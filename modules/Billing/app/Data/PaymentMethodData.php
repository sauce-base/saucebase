<?php

namespace Modules\Billing\Data;

use Modules\Billing\Enums\PaymentMethodType;
use Spatie\LaravelData\Data;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript]
class PaymentMethodData extends Data
{
    public function __construct(
        public string $providerPaymentMethodId,
        public PaymentMethodType $type,
        public PaymentMethodDetails $details = new PaymentMethodDetails,
    ) {}
}
