<?php

namespace Modules\Billing\Data;

use Spatie\LaravelData\Data;

class AddressData extends Data
{
    public function __construct(
        public string $country,
        public ?string $line1 = null,
        public ?string $line2 = null,
        public ?string $city = null,
        public ?string $state = null,
        public ?string $postalCode = null,
    ) {}
}
