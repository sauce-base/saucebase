<?php

namespace Modules\Billing\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Modules\Billing\Data\PaymentMethodData;
use Modules\Billing\Data\PaymentMethodDetails;
use Modules\Billing\Data\WebhookData;
use Modules\Billing\Enums\CheckoutSessionStatus;
use Modules\Billing\Enums\PaymentMethodType;
use Modules\Billing\Enums\PaymentStatus;
use Modules\Billing\Enums\SubscriptionStatus;
use Modules\Billing\Enums\WebhookEventType;
use Modules\Billing\Events\CheckoutCompleted;
use Modules\Billing\Events\PaymentSucceeded;
use Modules\Billing\Events\SubscriptionCreated;
use Modules\Billing\Models\CheckoutSession;
use Modules\Billing\Models\Customer;
use Modules\Billing\Models\WebhookEvent;
use Modules\Billing\Services\BillingService;
use Modules\Billing\Services\Gateways\StripeGateway;
use Modules\Billing\Services\PaymentGatewayManager;
use Tests\TestCase;

class WebhookIdempotencyTest extends TestCase
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

    public function test_duplicate_webhook_event_is_skipped(): void
    {
        Event::fake([CheckoutCompleted::class, SubscriptionCreated::class, PaymentSucceeded::class]);

        $session = CheckoutSession::factory()->create([
            'provider_session_id' => 'cs_test_idempotent',
        ]);

        $webhook = new WebhookData(
            type: WebhookEventType::CheckoutCompleted,
            provider: 'stripe',
            providerEventId: 'evt_duplicate_test',
            payload: [
                'id' => 'cs_test_idempotent',
                'subscription' => 'sub_test_idempotent',
                'currency' => 'eur',
                'amount_total' => 2900,
            ],
        );

        $this->gateway->method('verifyAndParseWebhook')->willReturn($webhook);

        // First call processes the webhook
        $this->billingService->handleWebhook('stripe', request());

        $this->assertDatabaseCount('subscriptions', 1);
        $this->assertDatabaseCount('webhook_events', 1);

        Event::assertDispatched(SubscriptionCreated::class, 1);

        // Second call with same event ID is skipped
        $this->billingService->handleWebhook('stripe', request());

        $this->assertDatabaseCount('subscriptions', 1);
        $this->assertDatabaseCount('webhook_events', 1);

        // Event should still only be dispatched once
        Event::assertDispatched(SubscriptionCreated::class, 1);
    }

    public function test_webhook_event_is_recorded_on_processing(): void
    {
        Event::fake([PaymentSucceeded::class]);

        Customer::factory()->create(['provider_customer_id' => 'cus_test_record']);

        $webhook = new WebhookData(
            type: WebhookEventType::PaymentSucceeded,
            provider: 'stripe',
            providerEventId: 'evt_record_test',
            payload: [
                'id' => 'in_record',
                'customer' => 'cus_test_record',
                'payment_intent' => 'pi_record',
                'currency' => 'usd',
                'amount_paid' => 1000,
            ],
        );

        $this->gateway->method('verifyAndParseWebhook')->willReturn($webhook);

        $this->billingService->handleWebhook('stripe', request());

        $this->assertDatabaseHas('webhook_events', [
            'provider_event_id' => 'evt_record_test',
            'provider' => 'stripe',
            'type' => 'payment.succeeded',
        ]);

        $event = WebhookEvent::where('provider_event_id', 'evt_record_test')->first();
        $this->assertNotNull($event->processed_at);
    }

    public function test_different_event_ids_are_processed_separately(): void
    {
        Event::fake([PaymentSucceeded::class]);

        Customer::factory()->create(['provider_customer_id' => 'cus_test_separate']);

        $webhook1 = new WebhookData(
            type: WebhookEventType::PaymentSucceeded,
            provider: 'stripe',
            providerEventId: 'evt_separate_1',
            payload: [
                'id' => 'in_sep_1',
                'customer' => 'cus_test_separate',
                'payment_intent' => 'pi_sep_1',
                'currency' => 'usd',
                'amount_paid' => 1000,
            ],
        );

        $webhook2 = new WebhookData(
            type: WebhookEventType::PaymentSucceeded,
            provider: 'stripe',
            providerEventId: 'evt_separate_2',
            payload: [
                'id' => 'in_sep_2',
                'customer' => 'cus_test_separate',
                'payment_intent' => 'pi_sep_2',
                'currency' => 'usd',
                'amount_paid' => 2000,
            ],
        );

        $this->gateway->method('verifyAndParseWebhook')
            ->willReturnOnConsecutiveCalls($webhook1, $webhook2);

        $this->billingService->handleWebhook('stripe', request());
        $this->billingService->handleWebhook('stripe', request());

        $this->assertDatabaseCount('payments', 2);
        $this->assertDatabaseCount('webhook_events', 2);

        Event::assertDispatched(PaymentSucceeded::class, 2);
    }

    public function test_payment_succeeded_restores_past_due_subscription(): void
    {
        Event::fake([PaymentSucceeded::class]);

        $customer = Customer::factory()->create(['provider_customer_id' => 'cus_test_restore']);
        $subscription = \Modules\Billing\Models\Subscription::factory()->create([
            'customer_id' => $customer->id,
            'provider_subscription_id' => 'sub_test_restore',
            'status' => SubscriptionStatus::PastDue,
        ]);

        $webhook = new WebhookData(
            type: WebhookEventType::PaymentSucceeded,
            provider: 'stripe',
            providerEventId: 'evt_restore',
            payload: [
                'id' => 'in_restore',
                'customer' => 'cus_test_restore',
                'subscription' => 'sub_test_restore',
                'payment_intent' => 'pi_restore',
                'currency' => 'eur',
                'amount_paid' => 2900,
            ],
        );

        $this->gateway->method('verifyAndParseWebhook')->willReturn($webhook);

        $this->billingService->handleWebhook('stripe', request());

        $subscription->refresh();
        $this->assertEquals(SubscriptionStatus::Active, $subscription->status);

        $this->assertDatabaseHas('payments', [
            'provider_payment_id' => 'pi_restore',
            'status' => PaymentStatus::Succeeded->value,
        ]);

        Event::assertDispatched(PaymentSucceeded::class);
    }

    public function test_checkout_completed_uses_first_or_create_for_subscription(): void
    {
        Event::fake([CheckoutCompleted::class, SubscriptionCreated::class, PaymentSucceeded::class]);

        $session = CheckoutSession::factory()->create([
            'provider_session_id' => 'cs_test_foc',
        ]);

        // Pre-create a subscription with the same provider ID (simulating race)
        \Modules\Billing\Models\Subscription::create([
            'customer_id' => $session->customer_id,
            'price_id' => $session->price_id,
            'provider_subscription_id' => 'sub_test_foc',
            'status' => SubscriptionStatus::Active,
            'current_period_starts_at' => now(),
        ]);

        $webhook = new WebhookData(
            type: WebhookEventType::CheckoutCompleted,
            provider: 'stripe',
            providerEventId: 'evt_test_foc',
            payload: [
                'id' => 'cs_test_foc',
                'subscription' => 'sub_test_foc',
                'currency' => 'eur',
                'amount_total' => 2900,
            ],
        );

        $this->gateway->method('verifyAndParseWebhook')->willReturn($webhook);

        $this->billingService->handleWebhook('stripe', request());

        // No duplicate created — firstOrCreate reused the existing one
        $this->assertDatabaseCount('subscriptions', 1);

        $session->refresh();
        $this->assertEquals(CheckoutSessionStatus::Completed, $session->status);
    }
}
