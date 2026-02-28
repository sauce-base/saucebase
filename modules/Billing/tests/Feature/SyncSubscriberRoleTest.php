<?php

namespace Modules\Billing\Tests\Feature;

use App\Enums\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Billing\Enums\SubscriptionStatus;
use Modules\Billing\Events\SubscriptionCancelled;
use Modules\Billing\Events\SubscriptionCreated;
use Modules\Billing\Events\SubscriptionUpdated;
use Modules\Billing\Models\Customer;
use Modules\Billing\Models\Subscription;
use Tests\TestCase;

class SyncSubscriberRoleTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private Customer $customer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = $this->createUser();
        $this->customer = Customer::factory()->create(['user_id' => $this->user->id]);
    }

    public function test_subscriber_role_assigned_on_subscription_created(): void
    {
        $subscription = Subscription::factory()->create([
            'customer_id' => $this->customer->id,
        ]);

        event(new SubscriptionCreated($subscription));

        $this->assertTrue($this->user->fresh()->hasRole(Role::SUBSCRIBER));
    }

    public function test_subscriber_role_kept_on_subscription_updated_with_past_due_status(): void
    {
        $subscription = Subscription::factory()->pastDue()->create([
            'customer_id' => $this->customer->id,
        ]);

        event(new SubscriptionUpdated($subscription));

        $this->assertTrue($this->user->fresh()->hasRole(Role::SUBSCRIBER));
    }

    public function test_subscriber_role_removed_on_subscription_cancelled(): void
    {
        $this->user->assignRole(Role::SUBSCRIBER);

        $subscription = Subscription::factory()->cancelled()->create([
            'customer_id' => $this->customer->id,
        ]);

        event(new SubscriptionCancelled($subscription));

        $this->assertFalse($this->user->fresh()->hasRole(Role::SUBSCRIBER));
    }

    public function test_subscriber_role_not_removed_when_another_active_subscription_exists(): void
    {
        $this->user->assignRole(Role::SUBSCRIBER);

        // Active subscription
        Subscription::factory()->create([
            'customer_id' => $this->customer->id,
        ]);

        // Cancelled subscription
        $cancelledSubscription = Subscription::factory()->cancelled()->create([
            'customer_id' => $this->customer->id,
        ]);

        event(new SubscriptionCancelled($cancelledSubscription));

        $this->assertTrue($this->user->fresh()->hasRole(Role::SUBSCRIBER));
    }

    public function test_subscriber_role_is_additive_user_keeps_user_role(): void
    {
        $subscription = Subscription::factory()->create([
            'customer_id' => $this->customer->id,
        ]);

        event(new SubscriptionCreated($subscription));

        $user = $this->user->fresh();
        $this->assertTrue($user->hasRole(Role::USER));
        $this->assertTrue($user->hasRole(Role::SUBSCRIBER));
    }

    public function test_subscriber_role_reassigned_on_resubscription(): void
    {
        $subscription = Subscription::factory()->create([
            'customer_id' => $this->customer->id,
            'status' => SubscriptionStatus::Active,
        ]);

        event(new SubscriptionUpdated($subscription));

        $this->assertTrue($this->user->fresh()->hasRole(Role::SUBSCRIBER));
    }

    public function test_subscriber_role_removed_on_pending_status(): void
    {
        $this->user->assignRole(Role::SUBSCRIBER);

        $subscription = Subscription::factory()->pending()->create([
            'customer_id' => $this->customer->id,
        ]);

        event(new SubscriptionUpdated($subscription));

        $this->assertFalse($this->user->fresh()->hasRole(Role::SUBSCRIBER));
    }
}
