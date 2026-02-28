<?php

namespace Modules\Billing\Listeners;

use Modules\Billing\Events\SubscriptionResumed;
use Modules\Billing\Notifications\SubscriptionResumedNotification;

class SendSubscriptionResumedNotification
{
    public function handle(SubscriptionResumed $event): void
    {
        $user = $event->subscription->customer->user;

        $user->notify(new SubscriptionResumedNotification($event->subscription));
    }
}
