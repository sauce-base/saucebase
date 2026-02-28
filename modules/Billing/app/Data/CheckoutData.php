<?php

namespace Modules\Billing\Data;

use Modules\Billing\Models\Customer;
use Modules\Billing\Models\Price;
use Spatie\LaravelData\Data;

class CheckoutData extends Data
{
    public function __construct(
        public Customer $customer,
        public Price $price,
        public string $successUrl,
        public string $cancelUrl,
        public ?string $coupon = null,
    ) {}
}
