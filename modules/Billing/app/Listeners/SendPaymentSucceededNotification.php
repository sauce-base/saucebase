<?php

namespace Modules\Billing\Listeners;

use Modules\Billing\Events\PaymentSucceeded;
use Modules\Billing\Notifications\PaymentSucceededNotification;

class SendPaymentSucceededNotification
{
    public function handle(PaymentSucceeded $event): void
    {
        $user = $event->payment->customer->user;

        $user->notify(new PaymentSucceededNotification($event->payment));
    }
}
