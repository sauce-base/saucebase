<?php

namespace Modules\Billing\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Modules\Billing\Models\Subscription;

class SubscriptionCancelled
{
    use Dispatchable;

    public function __construct(
        public Subscription $subscription,
    ) {}
}
