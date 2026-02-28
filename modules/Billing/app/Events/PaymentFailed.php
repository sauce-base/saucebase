<?php

namespace Modules\Billing\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Modules\Billing\Models\Payment;

class PaymentFailed
{
    use Dispatchable;

    public function __construct(
        public Payment $payment,
    ) {}
}
