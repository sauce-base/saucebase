<?php

namespace Modules\Billing\Data;

use App\Models\User;
use Spatie\LaravelData\Data;

class CustomerData extends Data
{
    public function __construct(
        public User $user,
        public string $name,
        public string $email,
        public ?string $phone = null,
        public ?AddressData $address = null,
    ) {}
}
