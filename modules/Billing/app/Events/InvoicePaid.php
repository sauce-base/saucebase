<?php

namespace Modules\Billing\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Modules\Billing\Models\Invoice;

class InvoicePaid
{
    use Dispatchable;

    public function __construct(
        public Invoice $invoice,
    ) {}
}
