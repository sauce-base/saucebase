<?php

namespace Modules\Billing\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Modules\Billing\Models\CheckoutSession;

class CheckoutCompleted
{
    use Dispatchable;

    public function __construct(
        public CheckoutSession $checkoutSession,
    ) {}
}
