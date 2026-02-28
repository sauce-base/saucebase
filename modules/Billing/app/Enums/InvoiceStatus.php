<?php

namespace Modules\Billing\Enums;

use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript]
enum InvoiceStatus: string
{
    case Draft = 'draft';
    case Posted = 'posted';
    case Paid = 'paid';
    case Unpaid = 'unpaid';
    case Voided = 'voided';
}
