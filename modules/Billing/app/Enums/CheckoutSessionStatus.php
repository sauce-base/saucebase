<?php

namespace Modules\Billing\Enums;

use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript]
enum CheckoutSessionStatus: string
{
    case Pending = 'pending';
    case Completed = 'completed';
    case Abandoned = 'abandoned';
    case Expired = 'expired';
}
