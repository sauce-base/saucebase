<?php

namespace Modules\Billing\Data;

use Spatie\LaravelData\Data;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript]
class PaymentMethodDetails extends Data
{
    public function __construct(
        public ?string $brand = null,
        public ?string $last4 = null,
        public ?int $expMonth = null,
        public ?int $expYear = null,
        public ?string $wallet = null,
        public ?string $funding = null,
        public ?string $email = null,
        public ?string $bankName = null,
        public ?string $country = null,
    ) {}
}
