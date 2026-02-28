<?php

namespace Modules\Billing\Listeners;

use Modules\Billing\Events\SubscriptionCreated;
use Modules\Billing\Notifications\SubscriptionCreatedNotification;

class SendSubscriptionCreatedNotification
{
    public function handle(SubscriptionCreated $event): void
    {
        $user = $event->subscription->customer->user;

        $user->notify(new SubscriptionCreatedNotification($event->subscription));
    }
}
