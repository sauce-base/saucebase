<?php

namespace Modules\Billing\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Billing\Contracts\PaymentGatewayInterface;
use Modules\Billing\Enums\SubscriptionStatus;
use Modules\Billing\Models\Customer;
use Modules\Billing\Models\Subscription;
use Modules\Billing\Services\PaymentGatewayManager;
use Tests\TestCase;

class SubscriptionResumeTest extends TestCase
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

    public function test_resume_subscription_requires_auth(): void
    {
        $response = $this->post(route('billing.subscription.resume'));

        $response->assertRedirect(route('login'));
    }

    public function test_resume_subscription_calls_billing_service(): void
    {
        $user = $this->createUser();
        $customer = Customer::factory()->create(['user_id' => $user->id]);
        Subscription::factory()->create([
            'customer_id' => $customer->id,
            'status' => SubscriptionStatus::Active,
            'cancelled_at' => now(),
            'ends_at' => now()->addMonth(),
        ]);

        $this->gateway->expects($this->once())
            ->method('resumeSubscription');

        $response = $this->actingAs($user)->post(route('billing.subscription.resume'));

        $response->assertRedirect();
    }

    public function test_resume_subscription_updates_local_state(): void
    {
        $user = $this->createUser();
        $customer = Customer::factory()->create(['user_id' => $user->id]);
        $subscription = Subscription::factory()->create([
            'customer_id' => $customer->id,
            'status' => SubscriptionStatus::Active,
            'cancelled_at' => now(),
            'ends_at' => now()->addMonth(),
        ]);

        $this->gateway->expects($this->once())
            ->method('resumeSubscription');

        $this->actingAs($user)->post(route('billing.subscription.resume'));

        $subscription->refresh();

        $this->assertNull($subscription->cancelled_at);
        $this->assertNull($subscription->ends_at);
        $this->assertEquals(SubscriptionStatus::Active, $subscription->status);
    }

    public function test_resume_returns_404_when_no_pending_cancellation(): void
    {
        $user = $this->createUser();
        $customer = Customer::factory()->create(['user_id' => $user->id]);
        Subscription::factory()->create([
            'customer_id' => $customer->id,
            'status' => SubscriptionStatus::Active,
            'cancelled_at' => null,
        ]);

        $response = $this->actingAs($user)->post(route('billing.subscription.resume'));

        $response->assertNotFound();
    }
}
