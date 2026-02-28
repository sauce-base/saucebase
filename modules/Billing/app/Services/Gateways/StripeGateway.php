<?php

namespace Modules\Billing\Services\Gateways;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Modules\Billing\Contracts\PaymentGatewayInterface;
use Modules\Billing\Data\CheckoutData;
use Modules\Billing\Data\CheckoutResultData;
use Modules\Billing\Data\CustomerData;
use Modules\Billing\Data\PaymentMethodData;
use Modules\Billing\Data\PaymentMethodDetails;
use Modules\Billing\Data\WebhookData;
use Modules\Billing\Enums\PaymentMethodType;
use Modules\Billing\Enums\WebhookEventType;
use Modules\Billing\Models\Customer;
use Modules\Billing\Models\Subscription;
use Stripe\Exception\SignatureVerificationException;
use Stripe\StripeClient;
use Stripe\Webhook;
use Symfony\Component\HttpKernel\Exception\HttpException;

class StripeGateway implements PaymentGatewayInterface
{
    private const array EVENT_MAP = [
        'checkout.session.completed' => WebhookEventType::CheckoutCompleted,
        'customer.subscription.updated' => WebhookEventType::SubscriptionUpdated,
        'customer.subscription.deleted' => WebhookEventType::SubscriptionDeleted,
        'invoice.payment_succeeded' => WebhookEventType::PaymentSucceeded,
        'invoice.payment_failed' => WebhookEventType::PaymentFailed,
        'invoice.paid' => WebhookEventType::InvoicePaid,
    ];

    public function __construct(
        private StripeClient $stripe,
    ) {}

    public function createCustomer(CustomerData $data): Customer
    {
        $params = [
            'name' => $data->name,
            'email' => $data->email,
        ];

        if ($data->phone) {
            $params['phone'] = $data->phone;
        }

        if ($data->address) {
            $params['address'] = array_filter([
                'line1' => $data->address->line1,
                'line2' => $data->address->line2,
                'city' => $data->address->city,
                'state' => $data->address->state,
                'postal_code' => $data->address->postalCode,
                'country' => $data->address->country,
            ], fn ($v) => $v !== null);
        }

        $stripeCustomer = $this->stripe->customers->create($params);

        return Customer::create([
            'user_id' => $data->user->id,
            'provider_customer_id' => $stripeCustomer->id,
            'email' => $data->email,
            'name' => $data->name,
            'phone' => $data->phone,
            'address' => $data->address?->toArray(),
        ]);
    }

    public function createCheckoutSession(CheckoutData $data): CheckoutResultData
    {
        $isRecurring = $data->price->interval !== null;

        $params = [
            'customer' => $data->customer->provider_customer_id,
            'mode' => $isRecurring ? 'subscription' : 'payment',
            'line_items' => [
                [
                    'price' => $data->price->provider_price_id,
                    'quantity' => 1,
                ],
            ],
            'success_url' => $data->successUrl,
            'cancel_url' => $data->cancelUrl,
        ];

        if ($data->coupon) {
            $params['discounts'] = [['coupon' => $data->coupon]];
        }

        $session = $this->stripe->checkout->sessions->create($params);

        return new CheckoutResultData(
            sessionId: $session->id,
            url: $session->url,
            provider: 'stripe',
        );
    }

    public function cancelSubscription(Subscription $subscription, bool $immediately = false): ?\DateTimeInterface
    {
        if ($immediately) {
            $this->stripe->subscriptions->cancel($subscription->provider_subscription_id);

            return null;
        }

        $stripeSub = $this->stripe->subscriptions->update($subscription->provider_subscription_id, [
            'cancel_at_period_end' => true,
        ]);

        $endsAt = $stripeSub->current_period_end ?? $stripeSub->cancel_at;

        return $endsAt ? Carbon::createFromTimestamp($endsAt) : null;
    }

    public function resumeSubscription(Subscription $subscription): void
    {
        $this->stripe->subscriptions->update($subscription->provider_subscription_id, [
            'cancel_at_period_end' => false,
        ]);
    }

    public function getManagementUrl(Customer $customer): string
    {
        $session = $this->stripe->billingPortal->sessions->create([
            'customer' => $customer->provider_customer_id,
            'return_url' => route('settings.billing'),
        ]);

        return $session->url;
    }

    public function resolvePaymentMethod(string $providerId): ?PaymentMethodData
    {
        $pmId = match (true) {
            str_starts_with($providerId, 'pm_') => $providerId,
            str_starts_with($providerId, 'sub_') => $this->stripe->subscriptions->retrieve($providerId)->default_payment_method,
            str_starts_with($providerId, 'pi_') => $this->stripe->paymentIntents->retrieve($providerId)->payment_method,
            default => null,
        };

        if (! $pmId) {
            return null;
        }

        $pm = $this->stripe->paymentMethods->retrieve($pmId);
        $type = PaymentMethodType::tryFrom($pm->type) ?? PaymentMethodType::Unknown;

        $details = match ($type) {
            PaymentMethodType::Card => new PaymentMethodDetails(
                brand: $pm->card->display_brand ?? $pm->card?->brand,
                last4: $pm->card?->last4,
                expMonth: $pm->card?->exp_month,
                expYear: $pm->card?->exp_year,
                funding: $pm->card?->funding,
                wallet: $pm->card?->wallet?->type,
            ),
            PaymentMethodType::SepaDebit => new PaymentMethodDetails(
                last4: $pm->sepa_debit?->last4,
                country: $pm->sepa_debit?->country,
            ),
            PaymentMethodType::UsBankAccount => new PaymentMethodDetails(
                bankName: $pm->us_bank_account?->bank_name,
                last4: $pm->us_bank_account?->last4,
            ),
            PaymentMethodType::PayPal => new PaymentMethodDetails(
                email: $pm->paypal?->payer_email,
            ),
            PaymentMethodType::Link => new PaymentMethodDetails(
                email: $pm->link?->email,
            ),
            default => new PaymentMethodDetails,
        };

        return new PaymentMethodData(
            providerPaymentMethodId: $pm->id,
            type: $type,
            details: $details,
        );
    }

    public function retrieveCheckoutSession(string $sessionId): array
    {
        return $this->stripe->checkout->sessions->retrieve($sessionId)->toArray();
    }

    public function retrieveSubscription(string $subscriptionId): array
    {
        return $this->stripe->subscriptions->retrieve($subscriptionId)->toArray();
    }

    public function verifyAndParseWebhook(Request $request): WebhookData
    {
        $webhookSecret = config('services.stripe.webhook_secret');

        try {
            $event = Webhook::constructEvent(
                $request->getContent(),
                $request->header('Stripe-Signature', ''),
                $webhookSecret,
            );
        } catch (SignatureVerificationException $e) {
            throw new HttpException(400, 'Invalid webhook signature: '.$e->getMessage());
        }

        $normalizedType = self::EVENT_MAP[$event->type] ?? null;

        return new WebhookData(
            type: $normalizedType,
            provider: 'stripe',
            providerEventId: $event->id,
            payload: $event->data->object->toArray(),
        );
    }
}
