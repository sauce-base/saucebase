<?php

namespace Modules\Billing\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Billing\Contracts\PaymentGatewayInterface;
use Modules\Billing\Enums\SubscriptionStatus;
use Modules\Billing\Models\Customer;
use Modules\Billing\Models\Subscription;
use Modules\Billing\Services\PaymentGatewayManager;
use Tests\TestCase;

class SubscriptionCancelTest extends TestCase
{
    use RefreshDatabase;

    /** @var PaymentGatewayInterface&\PHPUnit\Framework\MockObject\MockObject */
    private PaymentGatewayInterface $gateway;

    protected function setUp(): void
    {
        parent::setUp();

        $this->gateway = $this->createMock(PaymentGatewayInterface::class);

        $manager = $this->createMock(PaymentGatewayManager::class);
        $manager->method('driver')->willReturn($this->gateway);
        $this->app->instance(PaymentGatewayManager::class, $manager);
    }

    public function test_cancel_subscription_requires_auth(): void
    {
        $response = $this->post(route('billing.subscription.cancel'));

        $response->assertRedirect(route('login'));
    }

    public function test_cancel_subscription_calls_billing_service(): void
    {
        $user = $this->createUser();
        $customer = Customer::factory()->create(['user_id' => $user->id]);
        Subscription::factory()->create([
            'customer_id' => $customer->id,
            'status' => SubscriptionStatus::Active,
        ]);

        $this->gateway->expects($this->once())
            ->method('cancelSubscription')
            ->with($this->anything(), false);

        $response = $this->actingAs($user)->post(route('billing.subscription.cancel'));

        $response->assertRedirect();
    }

    public function test_cancel_subscription_updates_local_state(): void
    {
        $user = $this->createUser();
        $customer = Customer::factory()->create(['user_id' => $user->id]);
        $subscription = Subscription::factory()->create([
            'customer_id' => $customer->id,
            'status' => SubscriptionStatus::Active,
            'current_period_ends_at' => now()->addMonth(),
        ]);

        $this->gateway->expects($this->once())
            ->method('cancelSubscription');

        $this->actingAs($user)->post(route('billing.subscription.cancel'));

        $subscription->refresh();

        $this->assertNotNull($subscription->cancelled_at);
        $this->assertEquals(
            $subscription->current_period_ends_at->toDateTimeString(),
            $subscription->ends_at->toDateTimeString(),
        );
        $this->assertEquals(SubscriptionStatus::Active, $subscription->status);
    }

    public function test_cancel_uses_gateway_period_end_when_local_is_null(): void
    {
        $user = $this->createUser();
        $customer = Customer::factory()->create(['user_id' => $user->id]);
        $subscription = Subscription::factory()->create([
            'customer_id' => $customer->id,
            'status' => SubscriptionStatus::Active,
            'current_period_ends_at' => null,
        ]);

        $periodEnd = now()->addMonth()->startOfDay();

        $this->gateway->method('cancelSubscription')->willReturn($periodEnd);

        $this->actingAs($user)->post(route('billing.subscription.cancel'));

        $subscription->refresh();

        $this->assertNotNull($subscription->ends_at);
        $this->assertEquals($periodEnd->toDateTimeString(), $subscription->ends_at->toDateTimeString());
        $this->assertEquals($periodEnd->toDateTimeString(), $subscription->current_period_ends_at->toDateTimeString());
    }

    public function test_cancel_returns_404_when_no_active_subscription(): void
    {
        $user = $this->createUser();
        Customer::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->post(route('billing.subscription.cancel'));

        $response->assertNotFound();
    }
}
