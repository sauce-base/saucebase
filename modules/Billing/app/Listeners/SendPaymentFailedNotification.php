<?php

namespace Modules\Billing\Listeners;

use Modules\Billing\Events\PaymentFailed;
use Modules\Billing\Notifications\PaymentFailedNotification;

class SendPaymentFailedNotification
{
    public function handle(PaymentFailed $event): void
    {
        $user = $event->payment->customer->user;

        $user->notify(new PaymentFailedNotification($event->payment));
    }
}
