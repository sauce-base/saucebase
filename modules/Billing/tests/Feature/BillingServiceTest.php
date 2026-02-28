<?php

namespace Modules\Billing\Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Modules\Billing\Data\CheckoutResultData;
use Modules\Billing\Data\CustomerData;
use Modules\Billing\Data\PaymentMethodData;
use Modules\Billing\Data\PaymentMethodDetails;
use Modules\Billing\Data\WebhookData;
use Modules\Billing\Enums\CheckoutSessionStatus;
use Modules\Billing\Enums\Currency;
use Modules\Billing\Enums\PaymentMethodType;
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
use Modules\Billing\Models\PaymentMethod;
use Modules\Billing\Models\Price;
use Modules\Billing\Models\Subscription;
use Modules\Billing\Services\BillingService;
use Modules\Billing\Services\Gateways\StripeGateway;
use Modules\Billing\Services\PaymentGatewayManager;
use Tests\TestCase;

class BillingServiceTest extends TestCase
{
    use RefreshDatabase;

    private BillingService $billingService;

    /** @var StripeGateway&\PHPUnit\Framework\MockObject\MockObject */
    private StripeGateway $gateway;

    protected function setUp(): void
    {
        parent::setUp();

        $this->gateway = $this->createMock(StripeGateway::class);
        $this->gateway->method('resolvePaymentMethod')->willReturn(
            new PaymentMethodData(
                providerPaymentMethodId: 'pm_test_123',
                type: PaymentMethodType::Card,
                details: new PaymentMethodDetails(
                    brand: 'visa',
                    last4: '4242',
                    expMonth: 12,
                    expYear: 2030,
                ),
            ),
        );

        $manager = $this->createMock(PaymentGatewayManager::class);
        $manager->method('driver')->willReturn($this->gateway);
        $this->app->instance(PaymentGatewayManager::class, $manager);

        $this->billingService = $this->app->make(BillingService::class);
    }

    public function test_process_checkout_creates_customer(): void
    {
        $user = User::factory()->create();
        $price = Price::factory()->create();
        $session = CheckoutSession::create([
            'price_id' => $price->id,
            'status' => CheckoutSessionStatus::Pending,
            'expires_at' => now()->addHours(24),
        ]);

        $this->gateway->method('createCustomer')->willReturnCallback(
            fn (CustomerData $data) => Customer::create([
                'user_id' => $data->user->id,
                'provider_customer_id' => 'cus_guest_123',
                'email' => $data->email,
                'name' => $data->name,
                'phone' => $data->phone,
                'address' => $data->address?->toArray(),
            ]),
        );
        $this->gateway->method('createCheckoutSession')->willReturn(
            new CheckoutResultData(sessionId: 'cs_guest_123', url: 'https://stripe.com/checkout', provider: 'stripe'),
        );

        $billingDetails = [
            'name' => 'Billing Name',
            'email' => 'billing@example.com',
            'phone' => '+1234567890',
            'address' => [
                'street' => '123 Main St',
                'city' => 'Springfield',
                'state' => 'IL',
                'postal_code' => '62701',
                'country' => 'US',
            ],
        ];

        $result = $this->billingService->processCheckout($session, $user, 'https://example.com/success', 'https://example.com/cancel', $billingDetails);

        $this->assertEquals('cs_guest_123', $result->sessionId);
        $this->assertEquals('https://stripe.com/checkout', $result->url);

        $this->assertDatabaseHas('customers', [
            'user_id' => $user->id,
            'provider_customer_id' => 'cus_guest_123',
            'name' => 'Billing Name',
            'email' => 'billing@example.com',
            'phone' => '+1234567890',
        ]);

        $session->refresh();
        $this->assertNotNull($session->customer_id);
        $this->assertEquals('cs_guest_123', $session->provider_session_id);
    }

    public function test_checkout_session_generates_uuid_automatically(): void
    {
        $price = Price::factory()->create();
        $session = CheckoutSession::create([
            'price_id' => $price->id,
            'status' => CheckoutSessionStatus::Pending,
        ]);

        $this->assertTrue(strlen($session->uuid) === 36);
    }

    public function test_checkout_session_uses_uuid_as_route_key(): void
    {
        $session = new CheckoutSession;
        $this->assertEquals('uuid', $session->getRouteKeyName());
    }

    public function test_cancel_delegates_to_gateway(): void
    {
        $subscription = Subscription::factory()->create();

        $this->gateway->expects($this->once())
            ->method('cancelSubscription')
            ->with($subscription, false);

        $this->billingService->cancel($subscription);
    }

    public function test_cancel_immediately_delegates_to_gateway(): void
    {
        $subscription = Subscription::factory()->create();

        $this->gateway->expects($this->once())
            ->method('cancelSubscription')
            ->with($subscription, true);

        $this->billingService->cancel($subscription, immediately: true);
    }

    public function test_webhook_checkout_completed_creates_subscription(): void
    {
        Event::fake([CheckoutCompleted::class, SubscriptionCreated::class, PaymentSucceeded::class]);

        $session = CheckoutSession::factory()->create([
            'provider_session_id' => 'cs_test_789',
        ]);

        $webhook = new WebhookData(
            type: WebhookEventType::CheckoutCompleted,
            provider: 'stripe',
            providerEventId: 'evt_test_1',
            payload: [
                'id' => 'cs_test_789',
                'subscription' => 'sub_test_123',
                'currency' => 'eur',
                'amount_total' => 2900,
            ],
        );

        $this->gateway->method('verifyAndParseWebhook')->willReturn($webhook);

        $this->billingService->handleWebhook('stripe', request());

        $session->refresh();
        $this->assertEquals(CheckoutSessionStatus::Completed, $session->status);

        $subscription = Subscription::where('provider_subscription_id', 'sub_test_123')->first();
        $this->assertNotNull($subscription);
        $this->assertEquals($session->customer_id, $subscription->customer_id);
        $this->assertEquals(SubscriptionStatus::Active, $subscription->status);
        $this->assertNotNull($subscription->payment_method_id);
        $this->assertDatabaseHas('payment_methods', [
            'provider_payment_method_id' => 'pm_test_123',
        ]);

        // Checkout now also creates the initial payment for the subscription
        $payment = \Modules\Billing\Models\Payment::where('subscription_id', $subscription->id)->first();
        $this->assertNotNull($payment);
        $this->assertEquals($session->customer_id, $payment->customer_id);
        $this->assertEquals($session->price_id, $payment->price_id);
        $this->assertEquals(2900, $payment->amount);
        $this->assertEquals(PaymentStatus::Succeeded, $payment->status);

        Event::assertDispatched(CheckoutCompleted::class);
        Event::assertDispatched(SubscriptionCreated::class);
        Event::assertDispatched(PaymentSucceeded::class);
    }

    public function test_webhook_checkout_completed_syncs_period_dates_from_gateway(): void
    {
        Event::fake([CheckoutCompleted::class, SubscriptionCreated::class, PaymentSucceeded::class]);

        $periodStart = 1770827927;
        $periodEnd = 1773247127;

        $this->gateway->method('retrieveSubscription')->willReturn([
            'id' => 'sub_test_period',
            'current_period_start' => $periodStart,
            'current_period_end' => $periodEnd,
        ]);

        $session = CheckoutSession::factory()->create([
            'provider_session_id' => 'cs_test_period',
        ]);

        $webhook = new WebhookData(
            type: WebhookEventType::CheckoutCompleted,
            provider: 'stripe',
            providerEventId: 'evt_test_period',
            payload: [
                'id' => 'cs_test_period',
                'subscription' => 'sub_test_period',
                'currency' => 'eur',
                'amount_total' => 2900,
            ],
        );

        $this->gateway->method('verifyAndParseWebhook')->willReturn($webhook);

        $this->billingService->handleWebhook('stripe', request());

        $subscription = Subscription::where('provider_subscription_id', 'sub_test_period')->first();
        $this->assertNotNull($subscription);
        $this->assertNotNull($subscription->current_period_starts_at);
        $this->assertNotNull($subscription->current_period_ends_at);
        $this->assertEquals($periodStart, $subscription->current_period_starts_at->getTimestamp());
        $this->assertEquals($periodEnd, $subscription->current_period_ends_at->getTimestamp());

        Event::assertDispatched(SubscriptionCreated::class);
    }

    public function test_webhook_checkout_completed_creates_payment_for_one_time_purchase(): void
    {
        Event::fake([CheckoutCompleted::class, PaymentSucceeded::class]);

        $session = CheckoutSession::factory()->create([
            'provider_session_id' => 'cs_test_onetime',
        ]);

        $webhook = new WebhookData(
            type: WebhookEventType::CheckoutCompleted,
            provider: 'stripe',
            providerEventId: 'evt_test_onetime',
            payload: [
                'id' => 'cs_test_onetime',
                'payment_intent' => 'pi_test_onetime',
                'currency' => 'usd',
                'amount_total' => 29900,
            ],
        );

        $this->gateway->method('verifyAndParseWebhook')->willReturn($webhook);

        $this->billingService->handleWebhook('stripe', request());

        $session->refresh();
        $this->assertEquals(CheckoutSessionStatus::Completed, $session->status);

        $payment = \Modules\Billing\Models\Payment::where('provider_payment_id', 'pi_test_onetime')->first();
        $this->assertNotNull($payment);
        $this->assertEquals($session->customer_id, $payment->customer_id);
        $this->assertEquals(29900, $payment->amount);
        $this->assertEquals(PaymentStatus::Succeeded, $payment->status);
        $this->assertNotNull($payment->payment_method_id);
        $this->assertDatabaseHas('payment_methods', [
            'provider_payment_method_id' => 'pm_test_123',
        ]);
        $this->assertDatabaseCount('subscriptions', 0);

        Event::assertDispatched(CheckoutCompleted::class);
        Event::assertDispatched(PaymentSucceeded::class);
    }

    public function test_webhook_checkout_completed_creates_payment_for_subscription(): void
    {
        Event::fake([CheckoutCompleted::class, SubscriptionCreated::class, PaymentSucceeded::class]);

        $session = CheckoutSession::factory()->create([
            'provider_session_id' => 'cs_test_sub_pay',
        ]);

        $webhook = new WebhookData(
            type: WebhookEventType::CheckoutCompleted,
            provider: 'stripe',
            providerEventId: 'evt_test_sub_pay',
            payload: [
                'id' => 'cs_test_sub_pay',
                'subscription' => 'sub_test_sub_pay',
                'currency' => 'eur',
                'amount_total' => 2900,
            ],
        );

        $this->gateway->method('verifyAndParseWebhook')->willReturn($webhook);

        $this->billingService->handleWebhook('stripe', request());

        $subscription = Subscription::where('provider_subscription_id', 'sub_test_sub_pay')->first();
        $this->assertNotNull($subscription);

        $payment = \Modules\Billing\Models\Payment::where('subscription_id', $subscription->id)->first();
        $this->assertNotNull($payment);
        $this->assertEquals($session->customer_id, $payment->customer_id);
        $this->assertEquals($session->price_id, $payment->price_id);
        $this->assertEquals(2900, $payment->amount);
        $this->assertEquals(PaymentStatus::Succeeded, $payment->status);

        Event::assertDispatched(SubscriptionCreated::class);
        Event::assertDispatched(PaymentSucceeded::class);
    }

    public function test_webhook_payment_succeeded_skips_event_when_subscription_not_yet_created(): void
    {
        Event::fake([PaymentSucceeded::class]);

        $customer = Customer::factory()->create([
            'provider_customer_id' => 'cus_test_orphan',
        ]);

        // Invoice webhook arrives before checkout.session.completed — no Subscription exists yet
        $webhook = new WebhookData(
            type: WebhookEventType::PaymentSucceeded,
            provider: 'stripe',
            providerEventId: 'evt_test_orphan',
            payload: [
                'id' => 'in_test_orphan',
                'customer' => 'cus_test_orphan',
                'subscription' => 'sub_not_yet_created',
                'payment_intent' => 'pi_test_orphan',
                'currency' => 'eur',
                'amount_paid' => 2900,
            ],
        );

        $this->gateway->method('verifyAndParseWebhook')->willReturn($webhook);

        $this->billingService->handleWebhook('stripe', request());

        // Payment is saved for auditing
        $this->assertDatabaseHas('payments', [
            'customer_id' => $customer->id,
            'provider_payment_id' => 'pi_test_orphan',
            'subscription_id' => null,
        ]);

        // Event is NOT dispatched — will fire from onCheckoutCompleted later
        Event::assertNotDispatched(PaymentSucceeded::class);
    }

    public function test_checkout_completed_links_orphaned_payment_to_subscription(): void
    {
        Event::fake([CheckoutCompleted::class, SubscriptionCreated::class, PaymentSucceeded::class]);

        $session = CheckoutSession::factory()->create([
            'provider_session_id' => 'cs_test_orphan_link',
        ]);

        // Simulate an orphaned payment created by an earlier invoice webhook
        // (price_id is null because createPaymentFromWebhook sets price_id = $subscription?->price_id,
        // which resolves to null when the subscription doesn't exist yet)
        $orphanedPayment = \Modules\Billing\Models\Payment::create([
            'customer_id' => $session->customer_id,
            'subscription_id' => null,
            'price_id' => null,
            'provider_payment_id' => 'pi_orphan_link',
            'currency' => Currency::EUR,
            'amount' => 2900,
            'status' => PaymentStatus::Succeeded,
        ]);

        $webhook = new WebhookData(
            type: WebhookEventType::CheckoutCompleted,
            provider: 'stripe',
            providerEventId: 'evt_test_orphan_link',
            payload: [
                'id' => 'cs_test_orphan_link',
                'subscription' => 'sub_test_orphan_link',
                'currency' => 'eur',
                'amount_total' => 2900,
            ],
        );

        $this->gateway->method('verifyAndParseWebhook')->willReturn($webhook);

        $this->billingService->handleWebhook('stripe', request());

        $subscription = Subscription::where('provider_subscription_id', 'sub_test_orphan_link')->first();
        $this->assertNotNull($subscription);

        // Orphaned payment is linked to the subscription
        $orphanedPayment->refresh();
        $this->assertEquals($subscription->id, $orphanedPayment->subscription_id);
        $this->assertEquals($session->price_id, $orphanedPayment->price_id);

        // No duplicate payment created
        $this->assertDatabaseCount('payments', 1);

        Event::assertDispatched(PaymentSucceeded::class);
        Event::assertDispatched(SubscriptionCreated::class);
    }

    public function test_checkout_completed_does_not_link_one_time_payment_as_orphan(): void
    {
        Event::fake([CheckoutCompleted::class, SubscriptionCreated::class, PaymentSucceeded::class]);

        $session = CheckoutSession::factory()->create([
            'provider_session_id' => 'cs_test_no_steal',
        ]);

        // One-time payment created earlier (has price_id set — not an orphaned invoice payment)
        $oneTimePayment = \Modules\Billing\Models\Payment::create([
            'customer_id' => $session->customer_id,
            'subscription_id' => null,
            'price_id' => Price::factory()->create()->id,
            'provider_payment_id' => 'pi_one_time',
            'currency' => Currency::USD,
            'amount' => 9900,
            'status' => PaymentStatus::Succeeded,
        ]);

        $webhook = new WebhookData(
            type: WebhookEventType::CheckoutCompleted,
            provider: 'stripe',
            providerEventId: 'evt_test_no_steal',
            payload: [
                'id' => 'cs_test_no_steal',
                'subscription' => 'sub_test_no_steal',
                'currency' => 'eur',
                'amount_total' => 2900,
            ],
        );

        $this->gateway->method('verifyAndParseWebhook')->willReturn($webhook);

        $this->billingService->handleWebhook('stripe', request());

        $subscription = Subscription::where('provider_subscription_id', 'sub_test_no_steal')->first();
        $this->assertNotNull($subscription);

        // One-time payment is NOT touched
        $oneTimePayment->refresh();
        $this->assertNull($oneTimePayment->subscription_id);
        $this->assertEquals(9900, $oneTimePayment->amount);

        // A new subscription payment was created separately
        $subPayment = \Modules\Billing\Models\Payment::where('subscription_id', $subscription->id)->first();
        $this->assertNotNull($subPayment);
        $this->assertEquals(2900, $subPayment->amount);

        // Total: 2 payments (one-time + subscription)
        $this->assertDatabaseCount('payments', 2);
    }

    public function test_webhook_payment_succeeded_merges_into_checkout_created_payment(): void
    {
        Event::fake([CheckoutCompleted::class, SubscriptionCreated::class, PaymentSucceeded::class]);

        $session = CheckoutSession::factory()->create([
            'provider_session_id' => 'cs_test_merge',
        ]);

        // Step 1: Process checkout.session.completed (creates subscription + payment)
        $checkoutWebhook = new WebhookData(
            type: WebhookEventType::CheckoutCompleted,
            provider: 'stripe',
            providerEventId: 'evt_test_merge_checkout',
            payload: [
                'id' => 'cs_test_merge',
                'subscription' => 'sub_test_merge',
                'currency' => 'eur',
                'amount_total' => 2900,
            ],
        );

        // Step 2: invoice.payment_succeeded (should merge, not create duplicate)
        $invoiceWebhook = new WebhookData(
            type: WebhookEventType::PaymentSucceeded,
            provider: 'stripe',
            providerEventId: 'evt_test_merge_invoice',
            payload: [
                'id' => 'in_test_merge',
                'customer' => $session->customer->provider_customer_id,
                'subscription' => 'sub_test_merge',
                'default_payment_method' => 'pm_test_merge',
                'payment_intent' => 'pi_test_merge',
                'currency' => 'eur',
                'amount_paid' => 2900,
            ],
        );

        $this->gateway->method('verifyAndParseWebhook')
            ->willReturnOnConsecutiveCalls($checkoutWebhook, $invoiceWebhook);

        $this->billingService->handleWebhook('stripe', request());

        $subscription = Subscription::where('provider_subscription_id', 'sub_test_merge')->first();
        $this->assertNotNull($subscription);
        $this->assertDatabaseCount('payments', 1);

        // Payment created by checkout has no provider_payment_id
        $payment = \Modules\Billing\Models\Payment::where('subscription_id', $subscription->id)->first();
        $this->assertNull($payment->provider_payment_id);

        // Process invoice webhook
        $this->billingService->handleWebhook('stripe', request());

        // Still only 1 payment — not duplicated
        $this->assertDatabaseCount('payments', 1);

        // provider_payment_id is now filled in from the invoice webhook
        $payment->refresh();
        $this->assertEquals('pi_test_merge', $payment->provider_payment_id);

        // PaymentSucceeded dispatched only once (from checkout, not from invoice merge)
        Event::assertDispatchedTimes(PaymentSucceeded::class, 1);
    }

    public function test_webhook_subscription_updated_updates_status(): void
    {
        Event::fake([SubscriptionUpdated::class]);

        $subscription = Subscription::factory()->create([
            'provider_subscription_id' => 'sub_test_update',
            'status' => SubscriptionStatus::Active,
        ]);

        $webhook = new WebhookData(
            type: WebhookEventType::SubscriptionUpdated,
            provider: 'stripe',
            providerEventId: 'evt_test_2',
            payload: [
                'id' => 'sub_test_update',
                'status' => 'past_due',
                'default_payment_method' => 'pm_test_sub_update',
                'current_period_start' => 1700000000,
                'current_period_end' => 1702592000,
            ],
        );

        $this->gateway->method('verifyAndParseWebhook')->willReturn($webhook);

        $this->billingService->handleWebhook('stripe', request());

        $subscription->refresh();
        $this->assertEquals(SubscriptionStatus::PastDue, $subscription->status);
        $this->assertNotNull($subscription->payment_method_id);
        $this->assertDatabaseHas('payment_methods', [
            'provider_payment_method_id' => 'pm_test_123',
        ]);

        Event::assertDispatched(SubscriptionUpdated::class);
    }

    public function test_webhook_subscription_updated_handles_cancel_at_scheduled_cancellation(): void
    {
        Event::fake([SubscriptionUpdated::class]);

        $subscription = Subscription::factory()->create([
            'provider_subscription_id' => 'sub_test_cancel_at',
            'status' => SubscriptionStatus::Active,
        ]);

        $cancelAt = now()->addYear()->getTimestamp();

        $webhook = new WebhookData(
            type: WebhookEventType::SubscriptionUpdated,
            provider: 'stripe',
            providerEventId: 'evt_test_cancel_at',
            payload: [
                'id' => 'sub_test_cancel_at',
                'status' => 'active',
                'cancel_at_period_end' => false,
                'cancel_at' => $cancelAt,
            ],
        );

        $this->gateway->method('verifyAndParseWebhook')->willReturn($webhook);

        $this->billingService->handleWebhook('stripe', request());

        $subscription->refresh();
        $this->assertEquals(SubscriptionStatus::Active, $subscription->status);
        $this->assertNotNull($subscription->cancelled_at);
        $this->assertNotNull($subscription->ends_at);
        $this->assertEquals($cancelAt, $subscription->ends_at->getTimestamp());

        Event::assertDispatched(SubscriptionUpdated::class);
    }

    public function test_webhook_subscription_updated_cancel_at_period_end_falls_back_to_cancel_at(): void
    {
        Event::fake([SubscriptionUpdated::class]);

        $subscription = Subscription::factory()->create([
            'provider_subscription_id' => 'sub_test_cancel_fallback',
            'status' => SubscriptionStatus::Active,
        ]);

        $cancelAt = now()->addMonth()->getTimestamp();

        $webhook = new WebhookData(
            type: WebhookEventType::SubscriptionUpdated,
            provider: 'stripe',
            providerEventId: 'evt_test_cancel_fallback',
            payload: [
                'id' => 'sub_test_cancel_fallback',
                'status' => 'active',
                'cancel_at_period_end' => true,
                'cancel_at' => $cancelAt,
            ],
        );

        $this->gateway->method('verifyAndParseWebhook')->willReturn($webhook);

        $this->billingService->handleWebhook('stripe', request());

        $subscription->refresh();
        $this->assertEquals(SubscriptionStatus::Active, $subscription->status);
        $this->assertNotNull($subscription->cancelled_at);
        $this->assertNotNull($subscription->ends_at);
        $this->assertEquals($cancelAt, $subscription->ends_at->getTimestamp());

        Event::assertDispatched(SubscriptionUpdated::class);
    }

    public function test_webhook_subscription_updated_throws_when_subscription_not_found(): void
    {
        $webhook = new WebhookData(
            type: WebhookEventType::SubscriptionUpdated,
            provider: 'stripe',
            providerEventId: 'evt_test_not_found',
            payload: [
                'id' => 'sub_nonexistent',
                'status' => 'active',
            ],
        );

        $this->gateway->method('verifyAndParseWebhook')->willReturn($webhook);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Subscription not found: sub_nonexistent');

        $this->billingService->handleWebhook('stripe', request());
    }

    public function test_webhook_subscription_deleted_throws_when_subscription_not_found(): void
    {
        $webhook = new WebhookData(
            type: WebhookEventType::SubscriptionDeleted,
            provider: 'stripe',
            providerEventId: 'evt_test_not_found',
            payload: [
                'id' => 'sub_nonexistent',
            ],
        );

        $this->gateway->method('verifyAndParseWebhook')->willReturn($webhook);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Subscription not found: sub_nonexistent');

        $this->billingService->handleWebhook('stripe', request());
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('stripeStatusMappingProvider')]
    public function test_webhook_subscription_updated_maps_stripe_statuses(string $stripeStatus, SubscriptionStatus $expectedStatus): void
    {
        Event::fake([SubscriptionUpdated::class]);

        $subscription = Subscription::factory()->create([
            'provider_subscription_id' => 'sub_test_status_map',
            'status' => SubscriptionStatus::Active,
        ]);

        $webhook = new WebhookData(
            type: WebhookEventType::SubscriptionUpdated,
            provider: 'stripe',
            providerEventId: 'evt_test_status',
            payload: [
                'id' => 'sub_test_status_map',
                'status' => $stripeStatus,
            ],
        );

        $this->gateway->method('verifyAndParseWebhook')->willReturn($webhook);

        $this->billingService->handleWebhook('stripe', request());

        $subscription->refresh();
        $this->assertEquals($expectedStatus, $subscription->status);

        Event::assertDispatched(SubscriptionUpdated::class);
    }

    /**
     * @return array<string, array{string, SubscriptionStatus}>
     */
    public static function stripeStatusMappingProvider(): array
    {
        return [
            'active' => ['active', SubscriptionStatus::Active],
            'trialing' => ['trialing', SubscriptionStatus::Active],
            'past_due' => ['past_due', SubscriptionStatus::PastDue],
            'unpaid' => ['unpaid', SubscriptionStatus::PastDue],
            'canceled' => ['canceled', SubscriptionStatus::Cancelled],
            'incomplete_expired' => ['incomplete_expired', SubscriptionStatus::Cancelled],
            'incomplete' => ['incomplete', SubscriptionStatus::Pending],
            'paused' => ['paused', SubscriptionStatus::Pending],
        ];
    }

    public function test_webhook_subscription_deleted_cancels_subscription(): void
    {
        Event::fake([SubscriptionCancelled::class]);

        $subscription = Subscription::factory()->create([
            'provider_subscription_id' => 'sub_test_delete',
            'status' => SubscriptionStatus::Active,
        ]);

        $webhook = new WebhookData(
            type: WebhookEventType::SubscriptionDeleted,
            provider: 'stripe',
            providerEventId: 'evt_test_3',
            payload: [
                'id' => 'sub_test_delete',
            ],
        );

        $this->gateway->method('verifyAndParseWebhook')->willReturn($webhook);

        $this->billingService->handleWebhook('stripe', request());

        $subscription->refresh();
        $this->assertEquals(SubscriptionStatus::Cancelled, $subscription->status);
        $this->assertNotNull($subscription->cancelled_at);

        Event::assertDispatched(SubscriptionCancelled::class);
    }

    public function test_webhook_payment_succeeded_creates_payment(): void
    {
        Event::fake([PaymentSucceeded::class]);

        $customer = Customer::factory()->create([
            'provider_customer_id' => 'cus_test_pay',
        ]);
        $subscription = Subscription::factory()->create([
            'customer_id' => $customer->id,
            'provider_subscription_id' => 'sub_test_pay',
        ]);

        $webhook = new WebhookData(
            type: WebhookEventType::PaymentSucceeded,
            provider: 'stripe',
            providerEventId: 'evt_test_4',
            payload: [
                'id' => 'in_test_123',
                'customer' => 'cus_test_pay',
                'subscription' => 'sub_test_pay',
                'default_payment_method' => 'pm_test_pay',
                'payment_intent' => 'pi_test_123',
                'currency' => 'usd',
                'amount_paid' => 2900,
            ],
        );

        $this->gateway->method('verifyAndParseWebhook')->willReturn($webhook);

        $this->billingService->handleWebhook('stripe', request());

        $this->assertDatabaseHas('payment_methods', [
            'provider_payment_method_id' => 'pm_test_123',
            'customer_id' => $customer->id,
        ]);
        $this->assertDatabaseHas('payments', [
            'customer_id' => $customer->id,
            'subscription_id' => $subscription->id,
            'provider_payment_id' => 'pi_test_123',
            'amount' => 2900,
            'status' => PaymentStatus::Succeeded->value,
        ]);

        $payment = \Modules\Billing\Models\Payment::where('provider_payment_id', 'pi_test_123')->first();
        $this->assertNotNull($payment->payment_method_id);

        Event::assertDispatched(PaymentSucceeded::class);
    }

    public function test_webhook_payment_failed_marks_subscription_past_due(): void
    {
        Event::fake([PaymentFailed::class]);

        $customer = Customer::factory()->create([
            'provider_customer_id' => 'cus_test_fail',
        ]);
        $subscription = Subscription::factory()->create([
            'customer_id' => $customer->id,
            'provider_subscription_id' => 'sub_test_fail',
            'status' => SubscriptionStatus::Active,
        ]);

        $webhook = new WebhookData(
            type: WebhookEventType::PaymentFailed,
            provider: 'stripe',
            providerEventId: 'evt_test_5',
            payload: [
                'id' => 'in_test_456',
                'customer' => 'cus_test_fail',
                'subscription' => 'sub_test_fail',
                'default_payment_method' => 'pm_test_fail',
                'payment_intent' => 'pi_test_456',
                'currency' => 'usd',
                'amount_due' => 2900,
            ],
        );

        $this->gateway->method('verifyAndParseWebhook')->willReturn($webhook);

        $this->billingService->handleWebhook('stripe', request());

        $subscription->refresh();
        $this->assertEquals(SubscriptionStatus::PastDue, $subscription->status);

        $this->assertDatabaseHas('payment_methods', [
            'provider_payment_method_id' => 'pm_test_123',
            'customer_id' => $customer->id,
        ]);
        $this->assertDatabaseHas('payments', [
            'customer_id' => $customer->id,
            'status' => PaymentStatus::Failed->value,
        ]);

        $payment = \Modules\Billing\Models\Payment::where('provider_payment_id', 'pi_test_456')->first();
        $this->assertNotNull($payment->payment_method_id);

        Event::assertDispatched(PaymentFailed::class);
    }

    public function test_webhook_invoice_paid_creates_invoice(): void
    {
        Event::fake([InvoicePaid::class]);

        $customer = Customer::factory()->create([
            'provider_customer_id' => 'cus_test_inv',
        ]);

        $webhook = new WebhookData(
            type: WebhookEventType::InvoicePaid,
            provider: 'stripe',
            providerEventId: 'evt_test_6',
            payload: [
                'id' => 'in_test_invoice',
                'customer' => 'cus_test_inv',
                'number' => 'INV-001',
                'currency' => 'usd',
                'subtotal' => 2900,
                'tax' => 0,
                'total' => 2900,
                'hosted_invoice_url' => 'https://stripe.com/invoice/123',
                'invoice_pdf' => 'https://stripe.com/invoice/123/pdf',
            ],
        );

        $this->gateway->method('verifyAndParseWebhook')->willReturn($webhook);

        $this->billingService->handleWebhook('stripe', request());

        $this->assertDatabaseHas('invoices', [
            'customer_id' => $customer->id,
            'provider_invoice_id' => 'in_test_invoice',
            'number' => 'INV-001',
            'total' => 2900,
        ]);

        Event::assertDispatched(InvoicePaid::class);
    }

    public function test_webhook_invoice_paid_syncs_subscription_period_from_line_items(): void
    {
        Event::fake([InvoicePaid::class]);

        $customer = Customer::factory()->create([
            'provider_customer_id' => 'cus_test_inv_period',
        ]);

        $subscription = Subscription::factory()->create([
            'customer_id' => $customer->id,
            'provider_subscription_id' => 'sub_test_inv_period',
            'current_period_starts_at' => null,
            'current_period_ends_at' => null,
        ]);

        $periodStart = 1770827927;
        $periodEnd = 1773247127;

        $webhook = new WebhookData(
            type: WebhookEventType::InvoicePaid,
            provider: 'stripe',
            providerEventId: 'evt_test_inv_period',
            payload: [
                'id' => 'in_test_inv_period',
                'customer' => 'cus_test_inv_period',
                'subscription' => 'sub_test_inv_period',
                'number' => 'INV-002',
                'currency' => 'usd',
                'subtotal' => 2900,
                'tax' => 0,
                'total' => 2900,
                'hosted_invoice_url' => 'https://stripe.com/invoice/456',
                'invoice_pdf' => 'https://stripe.com/invoice/456/pdf',
                'lines' => [
                    'data' => [
                        [
                            'period' => [
                                'start' => $periodStart,
                                'end' => $periodEnd,
                            ],
                        ],
                    ],
                ],
            ],
        );

        $this->gateway->method('verifyAndParseWebhook')->willReturn($webhook);

        $this->billingService->handleWebhook('stripe', request());

        $subscription->refresh();
        $this->assertNotNull($subscription->current_period_starts_at);
        $this->assertNotNull($subscription->current_period_ends_at);
        $this->assertEquals($periodStart, $subscription->current_period_starts_at->getTimestamp());
        $this->assertEquals($periodEnd, $subscription->current_period_ends_at->getTimestamp());

        Event::assertDispatched(InvoicePaid::class);
    }

    public function test_webhook_invoice_paid_resolves_subscription_from_parent_field(): void
    {
        Event::fake([InvoicePaid::class]);

        $customer = Customer::factory()->create([
            'provider_customer_id' => 'cus_test_inv_parent',
        ]);

        $subscription = Subscription::factory()->create([
            'customer_id' => $customer->id,
            'provider_subscription_id' => 'sub_test_inv_parent',
            'current_period_starts_at' => null,
            'current_period_ends_at' => null,
        ]);

        $periodStart = 1770827927;
        $periodEnd = 1773247127;

        $webhook = new WebhookData(
            type: WebhookEventType::InvoicePaid,
            provider: 'stripe',
            providerEventId: 'evt_test_inv_parent',
            payload: [
                'id' => 'in_test_inv_parent',
                'customer' => 'cus_test_inv_parent',
                'parent' => [
                    'subscription_details' => [
                        'subscription' => 'sub_test_inv_parent',
                    ],
                ],
                'number' => 'INV-003',
                'currency' => 'eur',
                'subtotal' => 2900,
                'tax' => 0,
                'total' => 2900,
                'hosted_invoice_url' => 'https://stripe.com/invoice/789',
                'invoice_pdf' => 'https://stripe.com/invoice/789/pdf',
                'lines' => [
                    'data' => [
                        [
                            'period' => [
                                'start' => $periodStart,
                                'end' => $periodEnd,
                            ],
                        ],
                    ],
                ],
            ],
        );

        $this->gateway->method('verifyAndParseWebhook')->willReturn($webhook);

        $this->billingService->handleWebhook('stripe', request());

        $this->assertDatabaseHas('invoices', [
            'customer_id' => $customer->id,
            'subscription_id' => $subscription->id,
            'provider_invoice_id' => 'in_test_inv_parent',
        ]);

        $subscription->refresh();
        $this->assertNotNull($subscription->current_period_ends_at);
        $this->assertEquals($periodEnd, $subscription->current_period_ends_at->getTimestamp());

        Event::assertDispatched(InvoicePaid::class);
    }

    public function test_webhook_payment_succeeded_ignores_unknown_customer(): void
    {
        Event::fake([PaymentSucceeded::class]);

        $webhook = new WebhookData(
            type: WebhookEventType::PaymentSucceeded,
            provider: 'stripe',
            providerEventId: 'evt_no_customer',
            payload: [
                'id' => 'in_unknown',
                'customer' => 'cus_nonexistent',
                'currency' => 'usd',
                'amount_paid' => 1000,
            ],
        );

        $this->gateway->method('verifyAndParseWebhook')->willReturn($webhook);

        $this->billingService->handleWebhook('stripe', request());

        $this->assertDatabaseCount('payments', 0);
        Event::assertNotDispatched(PaymentSucceeded::class);
    }

    public function test_webhook_payment_failed_ignores_unknown_customer(): void
    {
        Event::fake([PaymentFailed::class]);

        $webhook = new WebhookData(
            type: WebhookEventType::PaymentFailed,
            provider: 'stripe',
            providerEventId: 'evt_no_customer_fail',
            payload: [
                'id' => 'in_unknown_fail',
                'customer' => 'cus_nonexistent',
                'currency' => 'usd',
                'amount_due' => 1000,
            ],
        );

        $this->gateway->method('verifyAndParseWebhook')->willReturn($webhook);

        $this->billingService->handleWebhook('stripe', request());

        $this->assertDatabaseCount('payments', 0);
        Event::assertNotDispatched(PaymentFailed::class);
    }

    public function test_webhook_invoice_paid_ignores_unknown_customer(): void
    {
        Event::fake([InvoicePaid::class]);

        $webhook = new WebhookData(
            type: WebhookEventType::InvoicePaid,
            provider: 'stripe',
            providerEventId: 'evt_no_customer_inv',
            payload: [
                'id' => 'in_unknown_inv',
                'customer' => 'cus_nonexistent',
                'currency' => 'usd',
                'subtotal' => 1000,
                'tax' => 0,
                'total' => 1000,
            ],
        );

        $this->gateway->method('verifyAndParseWebhook')->willReturn($webhook);

        $this->billingService->handleWebhook('stripe', request());

        $this->assertDatabaseCount('invoices', 0);
        Event::assertNotDispatched(InvoicePaid::class);
    }

    public function test_webhook_checkout_completed_ignores_unknown_session(): void
    {
        Event::fake([CheckoutCompleted::class]);

        $webhook = new WebhookData(
            type: WebhookEventType::CheckoutCompleted,
            provider: 'stripe',
            providerEventId: 'evt_unknown_session',
            payload: [
                'id' => 'cs_nonexistent',
                'subscription' => 'sub_test',
            ],
        );

        $this->gateway->method('verifyAndParseWebhook')->willReturn($webhook);

        $this->billingService->handleWebhook('stripe', request());

        $this->assertDatabaseCount('subscriptions', 0);
        Event::assertNotDispatched(CheckoutCompleted::class);
    }

    public function test_webhook_unmapped_type_does_nothing(): void
    {
        $webhook = new WebhookData(
            type: null,
            provider: 'stripe',
            providerEventId: 'evt_unmapped',
            payload: ['id' => 'obj_123'],
        );

        $this->gateway->method('verifyAndParseWebhook')->willReturn($webhook);

        $this->billingService->handleWebhook('stripe', request());

        $this->assertDatabaseHas('webhook_events', [
            'provider_event_id' => 'evt_unmapped',
            'type' => 'unmapped',
        ]);
    }

    public function test_webhook_invoice_paid_is_idempotent(): void
    {
        Event::fake([InvoicePaid::class]);

        $customer = Customer::factory()->create(['provider_customer_id' => 'cus_idempotent']);

        $payload = [
            'id' => 'in_idempotent',
            'customer' => 'cus_idempotent',
            'number' => 'INV-IDEM',
            'currency' => 'usd',
            'subtotal' => 2900,
            'tax' => 0,
            'total' => 2900,
            'hosted_invoice_url' => 'https://stripe.com/invoice/idem',
            'invoice_pdf' => 'https://stripe.com/invoice/idem/pdf',
        ];

        $webhook = new WebhookData(
            type: WebhookEventType::InvoicePaid,
            provider: 'stripe',
            providerEventId: 'evt_idem_1',
            payload: $payload,
        );

        $this->gateway->method('verifyAndParseWebhook')->willReturn($webhook);

        $this->billingService->handleWebhook('stripe', request());
        $this->billingService->handleWebhook('stripe', request());

        $this->assertDatabaseCount('invoices', 1);
        $this->assertDatabaseHas('invoices', [
            'provider_invoice_id' => 'in_idempotent',
            'customer_id' => $customer->id,
        ]);
    }

    public function test_process_checkout_updates_existing_customer(): void
    {
        $user = User::factory()->create();
        $existingCustomer = Customer::create([
            'user_id' => $user->id,
            'provider_customer_id' => 'cus_existing',
            'name' => 'Old Name',
            'email' => 'old@example.com',
        ]);
        $price = Price::factory()->create();
        $session = CheckoutSession::create([
            'price_id' => $price->id,
            'status' => CheckoutSessionStatus::Pending,
            'expires_at' => now()->addHours(24),
        ]);

        $this->gateway->method('createCheckoutSession')->willReturn(
            new CheckoutResultData(sessionId: 'cs_update', url: 'https://stripe.com/checkout', provider: 'stripe'),
        );

        $this->billingService->processCheckout($session, $user, 'https://example.com/success', 'https://example.com/cancel', [
            'name' => 'New Name',
            'email' => 'new@example.com',
        ]);

        $existingCustomer->refresh();
        $this->assertEquals('New Name', $existingCustomer->name);
        $this->assertEquals('new@example.com', $existingCustomer->email);
        $this->assertDatabaseCount('customers', 1);
    }

    public function test_ensure_payment_method_reuses_existing_default(): void
    {
        Event::fake([PaymentSucceeded::class]);

        $customer = Customer::factory()->create(['provider_customer_id' => 'cus_pm_default']);

        PaymentMethod::create([
            'customer_id' => $customer->id,
            'provider_payment_method_id' => 'pm_test_123',
            'type' => PaymentMethodType::Card,
            'details' => ['brand' => 'visa', 'last4' => '4242', 'expMonth' => 12, 'expYear' => 2030],
            'is_default' => true,
        ]);

        $webhook = new WebhookData(
            type: WebhookEventType::PaymentSucceeded,
            provider: 'stripe',
            providerEventId: 'evt_pm_reuse',
            payload: [
                'id' => 'in_pm_reuse',
                'customer' => 'cus_pm_default',
                'default_payment_method' => 'pm_test_123',
                'payment_intent' => 'pi_pm_reuse',
                'currency' => 'usd',
                'amount_paid' => 500,
            ],
        );

        $this->gateway->method('verifyAndParseWebhook')->willReturn($webhook);

        $this->billingService->handleWebhook('stripe', request());

        $this->assertDatabaseCount('payment_methods', 1);

        $payment = \Modules\Billing\Models\Payment::where('provider_payment_id', 'pi_pm_reuse')->first();
        $this->assertNotNull($payment->payment_method_id);
    }

    public function test_ensure_payment_method_swaps_default(): void
    {
        Event::fake([PaymentSucceeded::class]);

        $customer = Customer::factory()->create(['provider_customer_id' => 'cus_pm_swap']);

        $oldPm = PaymentMethod::create([
            'customer_id' => $customer->id,
            'provider_payment_method_id' => 'pm_old_default',
            'type' => PaymentMethodType::Card,
            'details' => ['brand' => 'mastercard', 'last4' => '5555', 'expMonth' => 6, 'expYear' => 2028],
            'is_default' => true,
        ]);

        $webhook = new WebhookData(
            type: WebhookEventType::PaymentSucceeded,
            provider: 'stripe',
            providerEventId: 'evt_pm_swap',
            payload: [
                'id' => 'in_pm_swap',
                'customer' => 'cus_pm_swap',
                'default_payment_method' => 'pm_new',
                'payment_intent' => 'pi_pm_swap',
                'currency' => 'usd',
                'amount_paid' => 500,
            ],
        );

        $this->gateway->method('verifyAndParseWebhook')->willReturn($webhook);

        $this->billingService->handleWebhook('stripe', request());

        $oldPm->refresh();
        $this->assertFalse($oldPm->is_default);

        $newPm = PaymentMethod::where('provider_payment_method_id', 'pm_test_123')->first();
        $this->assertTrue($newPm->is_default);
        $this->assertDatabaseCount('payment_methods', 2);
    }

    public function test_webhook_payment_succeeded_handles_zero_amount(): void
    {
        Event::fake([PaymentSucceeded::class]);

        Customer::factory()->create(['provider_customer_id' => 'cus_zero']);

        $webhook = new WebhookData(
            type: WebhookEventType::PaymentSucceeded,
            provider: 'stripe',
            providerEventId: 'evt_zero',
            payload: [
                'id' => 'in_zero',
                'customer' => 'cus_zero',
                'payment_intent' => 'pi_zero',
                'currency' => 'usd',
                'amount_paid' => 0,
            ],
        );

        $this->gateway->method('verifyAndParseWebhook')->willReturn($webhook);

        $this->billingService->handleWebhook('stripe', request());

        $this->assertDatabaseHas('payments', [
            'provider_payment_id' => 'pi_zero',
            'amount' => 0,
            'status' => PaymentStatus::Succeeded->value,
        ]);

        Event::assertDispatched(PaymentSucceeded::class);
    }
}
