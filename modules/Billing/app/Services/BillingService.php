<?php

namespace Modules\Billing\Services;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Modules\Billing\Data\AddressData;
use Modules\Billing\Data\CheckoutData;
use Modules\Billing\Data\CheckoutResultData;
use Modules\Billing\Data\CustomerData;
use Modules\Billing\Data\WebhookData;
use Modules\Billing\Enums\CheckoutSessionStatus;
use Modules\Billing\Enums\Currency;
use Modules\Billing\Enums\InvoiceStatus;
use Modules\Billing\Enums\PaymentStatus;
use Modules\Billing\Enums\SubscriptionStatus;
use Modules\Billing\Enums\WebhookEventType;
use Modules\Billing\Events\CheckoutCompleted;
use Modules\Billing\Events\InvoicePaid;
use Modules\Billing\Events\PaymentFailed;
use Modules\Billing\Events\PaymentSucceeded;
use Modules\Billing\Events\SubscriptionCancelled;
use Modules\Billing\Events\SubscriptionCreated;
use Modules\Billing\Events\SubscriptionUpdated;
use Modules\Billing\Models\CheckoutSession;
use Modules\Billing\Models\Customer;
use Modules\Billing\Models\Invoice;
use Modules\Billing\Models\Payment;
use Modules\Billing\Models\PaymentMethod;
use Modules\Billing\Models\Subscription;
use Modules\Billing\Models\WebhookEvent;
use Modules\Billing\Services\Gateways\StripeGateway;

class BillingService
{
    public function __construct(
        private PaymentGatewayManager $manager,
    ) {}

    /**
     * @param  array<string, mixed>  $billingDetails
     */
    public function processCheckout(CheckoutSession $session, User $user, string $successUrl, string $cancelUrl, array $billingDetails = []): CheckoutResultData
    {
        $customer = $this->ensureCustomer($user, billingDetails: $billingDetails);
        $price = $session->price()->with('product')->firstOrFail();

        $data = new CheckoutData(
            customer: $customer,
            price: $price,
            successUrl: $successUrl,
            cancelUrl: $cancelUrl,
        );

        $result = $this->manager->driver()->createCheckoutSession($data);

        $session->update([
            'customer_id' => $customer->id,
            'provider_session_id' => $result->sessionId,
            'success_url' => $successUrl,
            'cancel_url' => $cancelUrl,
        ]);

        return $result;
    }

    public function handleWebhook(string $provider, Request $request): void
    {
        $gateway = $this->manager->driver($provider);
        $webhook = $gateway->verifyAndParseWebhook($request);

        $webhookType = $webhook->type !== null ? $webhook->type->value : 'unmapped';
        Log::info('Webhook received', ['provider' => $provider, 'type' => $webhookType]);

        if (WebhookEvent::where('provider_event_id', $webhook->providerEventId)->exists()) {
            Log::info('Webhook already processed, skipping', ['provider_event_id' => $webhook->providerEventId]);

            return;
        }

        WebhookEvent::create([
            'provider_event_id' => $webhook->providerEventId,
            'provider' => $provider,
            'type' => $webhookType,
            'processed_at' => now(),
        ]);

        match ($webhook->type) {
            WebhookEventType::CheckoutCompleted => $this->onCheckoutCompleted($webhook),
            WebhookEventType::SubscriptionUpdated => $this->onSubscriptionUpdated($webhook),
            WebhookEventType::SubscriptionDeleted => $this->onSubscriptionDeleted($webhook),
            WebhookEventType::PaymentSucceeded => $this->onPaymentSucceeded($webhook),
            WebhookEventType::PaymentFailed => $this->onPaymentFailed($webhook),
            WebhookEventType::InvoicePaid => $this->onInvoicePaid($webhook),
            default => null,
        };
    }

    public function cancel(Subscription $subscription, bool $immediately = false): ?\DateTimeInterface
    {
        return $this->manager->driver()->cancelSubscription($subscription, $immediately);
    }

    public function resume(Subscription $subscription): void
    {
        $this->manager->driver()->resumeSubscription($subscription);
    }

    public function fulfillCheckoutIfNeeded(string $providerSessionId): void
    {
        $session = CheckoutSession::where('provider_session_id', $providerSessionId)->first();

        if (! $session || $session->status === CheckoutSessionStatus::Completed) {
            return;
        }

        try {
            $gateway = $this->manager->driver();

            if (! $gateway instanceof StripeGateway) {
                Log::info('fulfillCheckoutIfNeeded skipped: gateway is not Stripe', [
                    'session_id' => $providerSessionId,
                ]);

                return;
            }

            $stripeSession = $gateway->retrieveCheckoutSession($providerSessionId);
        } catch (\Throwable $e) {
            Log::warning('Failed to retrieve checkout session for redirect fulfillment', [
                'session_id' => $providerSessionId,
                'error' => $e->getMessage(),
            ]);

            return;
        }

        if (($stripeSession['payment_status'] ?? null) !== 'paid') {
            return;
        }

        $this->onCheckoutCompleted(new WebhookData(
            type: WebhookEventType::CheckoutCompleted,
            provider: 'stripe',
            providerEventId: 'redirect_fulfill_'.$providerSessionId,
            payload: $stripeSession,
        ));
    }

    public function getManagementUrl(User $user): string
    {
        $customer = Customer::where('user_id', $user->id)->firstOrFail();

        return $this->manager->driver()->getManagementUrl($customer);
    }

    /**
     * @param  array<string, mixed>  $billingDetails
     */
    private function ensureCustomer(User $user, ?string $provider = null, array $billingDetails = []): Customer
    {
        $name = $billingDetails['name'] ?? $user->name;
        $email = $billingDetails['email'] ?? $user->email;
        $phone = $billingDetails['phone'] ?? null;
        /** @var array<string, string>|null $rawAddress */
        $rawAddress = $billingDetails['address'] ?? null;

        $addressData = $rawAddress && ! empty($rawAddress['country'])
            ? new AddressData(
                country: $rawAddress['country'],
                line1: $rawAddress['line1'] ?? $rawAddress['street'] ?? null,
                line2: $rawAddress['line2'] ?? null,
                city: $rawAddress['city'] ?? null,
                state: $rawAddress['state'] ?? null,
                postalCode: $rawAddress['postal_code'] ?? null,
            )
            : null;

        $customer = Customer::where('user_id', $user->id)->first();

        if ($customer) {
            $updates = array_filter([
                'name' => $name,
                'email' => $email,
                'phone' => $phone,
            ], fn ($v) => $v !== null);

            if ($addressData) {
                $updates['address'] = $addressData->toArray();
            }

            $customer->update($updates);

            return $customer;
        }

        $customerData = new CustomerData(
            user: $user,
            name: $name,
            email: $email,
            phone: $phone,
            address: $addressData,
        );

        return $this->manager->driver($provider)->createCustomer($customerData);
    }

    private function ensurePaymentMethod(Customer $customer, string $providerId, string $provider): ?PaymentMethod
    {
        $gateway = $this->manager->driver($provider);

        if (! $gateway instanceof StripeGateway) {
            return null;
        }

        $data = $gateway->resolvePaymentMethod($providerId);

        if (! $data) {
            return null;
        }

        return DB::transaction(function () use ($customer, $data) {
            $existing = PaymentMethod::where('provider_payment_method_id', $data->providerPaymentMethodId)->first();

            if ($existing) {
                if (! $existing->is_default) {
                    PaymentMethod::where('customer_id', $customer->id)
                        ->where('is_default', true)
                        ->lockForUpdate()
                        ->update(['is_default' => false]);
                    $existing->update(['is_default' => true]);
                }

                return $existing;
            }

            PaymentMethod::where('customer_id', $customer->id)
                ->where('is_default', true)
                ->lockForUpdate()
                ->update(['is_default' => false]);

            return PaymentMethod::create([
                'customer_id' => $customer->id,
                'provider_payment_method_id' => $data->providerPaymentMethodId,
                'type' => $data->type,
                'details' => $data->details->toArray(),
                'is_default' => true,
            ]);
        });
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function parseCurrency(array $payload): Currency
    {
        return Currency::tryFrom(strtoupper($payload['currency'] ?? '')) ?? Currency::default();
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function resolveSubscriptionId(array $payload): ?string
    {
        return $payload['subscription'] ?? $payload['parent']['subscription_details']['subscription'] ?? null;
    }

    private function createPaymentFromWebhook(WebhookData $webhook, PaymentStatus $status, string $amountKey): ?Payment
    {
        /** @var array<string, mixed> $payload */
        $payload = $webhook->payload;

        $customer = Customer::where('provider_customer_id', $payload['customer'] ?? null)->first();

        if (! $customer) {
            return null;
        }

        $subscriptionId = $this->resolveSubscriptionId($payload);
        $subscription = $subscriptionId
            ? Subscription::where('provider_subscription_id', $subscriptionId)->first()
            : null;

        $pm = ($payload['default_payment_method'] ?? null)
            ? $this->ensurePaymentMethod($customer, $payload['default_payment_method'], $webhook->provider)
            : null;

        $providerPaymentId = $payload['payment_intent'] ?? $payload['id'];

        // Merge into existing payment pre-created by checkout.session.completed
        if ($subscription) {
            $existing = Payment::where('subscription_id', $subscription->id)
                ->whereNull('provider_payment_id')
                ->where('created_at', '>=', now()->subMinutes(5))
                ->first();

            if ($existing) {
                $existing->update([
                    'provider_payment_id' => $providerPaymentId,
                    'payment_method_id' => $pm?->id ?? $existing->payment_method_id, // @phpstan-ignore nullsafe.neverNull
                ]);

                return $existing;
            }
        }

        return Payment::create([
            'customer_id' => $customer->id,
            'subscription_id' => $subscription?->id,
            'payment_method_id' => $pm?->id,
            'price_id' => $subscription?->price_id,
            'provider_payment_id' => $providerPaymentId,
            'currency' => $this->parseCurrency($payload),
            'amount' => $payload[$amountKey] ?? 0,
            'status' => $status,
        ]);
    }

    private function onCheckoutCompleted(WebhookData $webhook): void
    {
        /** @var array<string, mixed> $payload */
        $payload = $webhook->payload;

        $session = CheckoutSession::where('provider_session_id', $payload['id'])->first();

        if (! $session || $session->status === CheckoutSessionStatus::Completed) {
            return;
        }

        $customer = $session->customer;

        if (! $customer) {
            Log::warning('Checkout session missing customer', ['session_id' => $session->id]);

            return;
        }

        $subscriptionId = $payload['subscription'] ?? null;
        /** @var array{subscription: ?Subscription, payment: ?Payment} $result */
        $result = ['subscription' => null, 'payment' => null];
        $wasJustCreated = false;

        DB::transaction(function () use ($session, $customer, $payload, $webhook, $subscriptionId, &$result, &$wasJustCreated) {
            $session->update(['status' => CheckoutSessionStatus::Completed]);

            if ($subscriptionId) {
                $pm = $this->ensurePaymentMethod($customer, $subscriptionId, $webhook->provider);

                $subscription = Subscription::firstOrCreate(
                    ['provider_subscription_id' => $subscriptionId],
                    [
                        'customer_id' => $session->customer_id,
                        'price_id' => $session->price_id,
                        'payment_method_id' => $pm?->id,
                        'status' => SubscriptionStatus::Active,
                        'current_period_starts_at' => now(),
                    ],
                );

                $wasJustCreated = $subscription->wasRecentlyCreated;
                $result['subscription'] = $subscription;

                // Link any payment created by an earlier invoice webhook (race condition)
                $orphanedPayment = Payment::where('customer_id', $session->customer_id)
                    ->whereNull('subscription_id')
                    ->whereNull('price_id')
                    ->where('created_at', '>=', now()->subMinutes(5))
                    ->lockForUpdate()
                    ->first();

                if ($orphanedPayment) {
                    $orphanedPayment->update([
                        'subscription_id' => $subscription->id,
                        'price_id' => $session->price_id,
                    ]);
                    $result['payment'] = $orphanedPayment;
                } else {
                    $result['payment'] = Payment::create([
                        'customer_id' => $session->customer_id,
                        'subscription_id' => $subscription->id,
                        'price_id' => $session->price_id,
                        'payment_method_id' => $pm?->id,
                        'currency' => $this->parseCurrency($payload),
                        'amount' => $payload['amount_total'] ?? 0,
                        'status' => PaymentStatus::Succeeded,
                    ]);
                }
            } elseif ($payload['payment_intent'] ?? null) {
                $pm = $this->ensurePaymentMethod($customer, $payload['payment_intent'], $webhook->provider);

                $result['payment'] = Payment::create([
                    'customer_id' => $session->customer_id,
                    'price_id' => $session->price_id,
                    'payment_method_id' => $pm?->id,
                    'provider_payment_id' => $payload['payment_intent'],
                    'currency' => $this->parseCurrency($payload),
                    'amount' => $payload['amount_total'] ?? 0,
                    'status' => PaymentStatus::Succeeded,
                ]);
            }
        });

        // Sync period dates from gateway after transaction commits (avoids API call inside transaction)
        if ($result['subscription'] && $wasJustCreated && $subscriptionId) {
            $this->syncSubscriptionPeriod($result['subscription'], $subscriptionId, $webhook->provider);
        }

        // Fire events after transaction commits
        if ($result['payment']) {
            event(new PaymentSucceeded($result['payment']));
        }

        if ($result['subscription']) {
            event(new SubscriptionCreated($result['subscription']));
        }

        event(new CheckoutCompleted($session));
    }

    private function onSubscriptionUpdated(WebhookData $webhook): void
    {
        /** @var array<string, mixed> $payload */
        $payload = $webhook->payload;

        $subscription = Subscription::where('provider_subscription_id', $payload['id'])->first();

        if (! $subscription) {
            Log::warning('Subscription not found for update', ['provider_subscription_id' => $payload['id']]);

            throw new \RuntimeException("Subscription not found: {$payload['id']}");
        }

        $status = match ($payload['status'] ?? null) {
            'active', 'trialing' => SubscriptionStatus::Active,
            'past_due', 'unpaid' => SubscriptionStatus::PastDue,
            'canceled', 'incomplete_expired' => SubscriptionStatus::Cancelled,
            'incomplete', 'paused' => SubscriptionStatus::Pending,
            default => $subscription->status,
        };

        $updates = ['status' => $status];

        if ($payload['default_payment_method'] ?? null) {
            $pm = $this->ensurePaymentMethod($subscription->customer, $payload['default_payment_method'], $webhook->provider);
            $updates['payment_method_id'] = $pm?->id;
        }

        if (isset($payload['current_period_start'])) {
            $updates['current_period_starts_at'] = Carbon::createFromTimestamp($payload['current_period_start']);
        }

        if (isset($payload['current_period_end'])) {
            $updates['current_period_ends_at'] = Carbon::createFromTimestamp($payload['current_period_end']);
        }

        if (isset($payload['cancel_at_period_end']) && $payload['cancel_at_period_end']) {
            $updates['cancelled_at'] = now();
            $endsAt = $payload['current_period_end'] ?? $payload['cancel_at'] ?? null;
            $updates['ends_at'] = $endsAt ? Carbon::createFromTimestamp($endsAt) : null;
        } elseif (isset($payload['cancel_at']) && $payload['cancel_at']) {
            $updates['cancelled_at'] = now();
            $updates['ends_at'] = Carbon::createFromTimestamp($payload['cancel_at']);
        } elseif (isset($payload['cancel_at_period_end']) && ! $payload['cancel_at_period_end'] && empty($payload['cancel_at'])) {
            $updates['cancelled_at'] = null;
            $updates['ends_at'] = null;
        }

        $subscription->update($updates);

        Log::info('Subscription updated', ['subscription_id' => $subscription->id, 'status' => $status->value]);

        event(new SubscriptionUpdated($subscription));
    }

    private function onSubscriptionDeleted(WebhookData $webhook): void
    {
        /** @var array<string, mixed> $payload */
        $payload = $webhook->payload;

        $subscription = Subscription::where('provider_subscription_id', $payload['id'])->first();

        if (! $subscription) {
            Log::warning('Subscription not found for deletion', ['provider_subscription_id' => $payload['id']]);

            throw new \RuntimeException("Subscription not found: {$payload['id']}");
        }

        $subscription->update([
            'status' => SubscriptionStatus::Cancelled,
            'cancelled_at' => now(),
            'ends_at' => now(),
        ]);

        Log::info('Subscription cancelled', ['subscription_id' => $subscription->id]);

        event(new SubscriptionCancelled($subscription));
    }

    private function onPaymentSucceeded(WebhookData $webhook): void
    {
        $payment = $this->createPaymentFromWebhook($webhook, PaymentStatus::Succeeded, 'amount_paid');

        if (! $payment) {
            return;
        }

        // Payment was pre-created from checkout — event already dispatched there
        if (! $payment->wasRecentlyCreated) {
            return;
        }

        // Race condition: invoice arrived before checkout created the subscription.
        // Payment is saved for auditing. Event will fire from onCheckoutCompleted
        // after it links this orphaned payment to the subscription.
        if ($payment->subscription_id === null && $this->resolveSubscriptionId($webhook->payload) !== null) {
            return;
        }

        // Restore subscription status after successful payment recovery
        if ($payment->subscription_id) {
            Subscription::where('id', $payment->subscription_id)
                ->where('status', SubscriptionStatus::PastDue)
                ->update(['status' => SubscriptionStatus::Active]);
        }

        event(new PaymentSucceeded($payment));
    }

    private function onPaymentFailed(WebhookData $webhook): void
    {
        $payment = $this->createPaymentFromWebhook($webhook, PaymentStatus::Failed, 'amount_due');

        if (! $payment) {
            return;
        }

        if ($payment->subscription_id) {
            Subscription::where('id', $payment->subscription_id)->update(['status' => SubscriptionStatus::PastDue]);
        }

        event(new PaymentFailed($payment));
    }

    private function onInvoicePaid(WebhookData $webhook): void
    {
        /** @var array<string, mixed> $payload */
        $payload = $webhook->payload;

        $customer = Customer::where('provider_customer_id', $payload['customer'] ?? null)->first();

        if (! $customer) {
            return;
        }

        $subscriptionId = $this->resolveSubscriptionId($payload);
        $subscription = $subscriptionId
            ? Subscription::where('provider_subscription_id', $subscriptionId)->first()
            : null;

        $invoice = Invoice::updateOrCreate(
            ['provider_invoice_id' => $payload['id']],
            [
                'customer_id' => $customer->id,
                'subscription_id' => $subscription?->id,
                'number' => $payload['number'] ?? null,
                'currency' => $this->parseCurrency($payload),
                'subtotal' => $payload['subtotal'] ?? 0,
                'tax' => $payload['tax'] ?? 0,
                'total' => $payload['total'] ?? 0,
                'status' => InvoiceStatus::Paid,
                'paid_at' => now(),
                'hosted_invoice_url' => $payload['hosted_invoice_url'] ?? null,
                'pdf_url' => $payload['invoice_pdf'] ?? null,
            ],
        );

        if ($subscription) {
            $lineItem = $payload['lines']['data'][0] ?? null;
            if ($lineItem && isset($lineItem['period'])) {
                $subscription->update([
                    'current_period_starts_at' => Carbon::createFromTimestamp($lineItem['period']['start']),
                    'current_period_ends_at' => Carbon::createFromTimestamp($lineItem['period']['end']),
                ]);
            }
        }

        event(new InvoicePaid($invoice));
    }

    private function syncSubscriptionPeriod(Subscription $subscription, string $providerSubscriptionId, string $provider): void
    {
        try {
            $gateway = $this->manager->driver($provider);

            if (! $gateway instanceof StripeGateway) {
                return;
            }

            $stripeSubscription = $gateway->retrieveSubscription($providerSubscriptionId);

            $updates = [];
            if (isset($stripeSubscription['current_period_start'])) {
                $updates['current_period_starts_at'] = Carbon::createFromTimestamp($stripeSubscription['current_period_start']);
            }
            if (isset($stripeSubscription['current_period_end'])) {
                $updates['current_period_ends_at'] = Carbon::createFromTimestamp($stripeSubscription['current_period_end']);
            }

            if ($updates) {
                $subscription->update($updates);
            }
        } catch (\Throwable $e) {
            Log::warning('Failed to sync subscription period from gateway', [
                'subscription_id' => $subscription->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
