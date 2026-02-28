<?php

namespace Modules\Billing\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Modules\Billing\Models\Subscription;

class SubscriptionUpdated
{
    use Dispatchable;

    public function __construct(
        public Subscription $subscription,
    ) {}
}
