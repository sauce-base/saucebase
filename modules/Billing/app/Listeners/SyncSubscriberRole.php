<?php

namespace Modules\Billing\Listeners;

use App\Enums\Role;
use Modules\Billing\Enums\SubscriptionStatus;
use Modules\Billing\Events\SubscriptionCancelled;
use Modules\Billing\Events\SubscriptionCreated;
use Modules\Billing\Events\SubscriptionUpdated;
use Modules\Billing\Models\Subscription;

class SyncSubscriberRole
{
    public function handle(SubscriptionCreated|SubscriptionUpdated|SubscriptionCancelled $event): void
    {
        $subscription = $event->subscription;
        $user = $subscription->customer?->user;

        if (! $user) {
            return;
        }

        if (in_array($subscription->status, [SubscriptionStatus::Active, SubscriptionStatus::PastDue])) {
            $user->assignRole(Role::SUBSCRIBER);

            return;
        }

        // Only remove subscriber role if user has no other active/past_due subscriptions
        $hasOtherActiveSubscription = Subscription::where('customer_id', $subscription->customer_id)
            ->where('id', '!=', $subscription->id)
            ->whereIn('status', [SubscriptionStatus::Active, SubscriptionStatus::PastDue])
            ->exists();

        if (! $hasOtherActiveSubscription) {
            $user->removeRole(Role::SUBSCRIBER);
        }
    }
}
