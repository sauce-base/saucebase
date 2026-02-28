<?php

namespace Modules\Billing\Listeners;

use Modules\Billing\Enums\SubscriptionStatus;
use Modules\Billing\Events\SubscriptionUpdated;
use Modules\Billing\Notifications\SubscriptionUpdatedNotification;

class SendSubscriptionUpdatedNotification
{
    public function handle(SubscriptionUpdated $event): void
    {
        $subscription = $event->subscription;

        $isCancellationPending = $subscription->cancelled_at && $subscription->status === SubscriptionStatus::Active;
        $isPastDue = $subscription->status === SubscriptionStatus::PastDue;

        if (! $isCancellationPending && ! $isPastDue) {
            return;
        }

        $subscription->customer->user->notify(new SubscriptionUpdatedNotification($subscription));
    }
}
