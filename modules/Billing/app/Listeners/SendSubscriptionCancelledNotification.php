<?php

namespace Modules\Billing\Listeners;

use Modules\Billing\Events\SubscriptionCancelled;
use Modules\Billing\Notifications\SubscriptionCancelledNotification;

class SendSubscriptionCancelledNotification
{
    public function handle(SubscriptionCancelled $event): void
    {
        $user = $event->subscription->customer->user;

        $user->notify(new SubscriptionCancelledNotification($event->subscription));
    }
}
