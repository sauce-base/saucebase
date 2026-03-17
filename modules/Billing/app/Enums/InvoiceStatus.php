<?php

namespace Modules\Billing\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript]
enum InvoiceStatus: string implements HasColor, HasLabel
{
    case Draft = 'draft';
    case Posted = 'posted';
    case Paid = 'paid';
    case Unpaid = 'unpaid';
    case Voided = 'voided';

    public function getLabel(): string
    {
        return match ($this) {
            self::Draft => __('Draft'),
            self::Posted => __('Posted'),
            self::Paid => __('Paid'),
            self::Unpaid => __('Unpaid'),
            self::Voided => __('Voided'),
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Draft => 'gray',
            self::Posted => 'info',
            self::Paid => 'success',
            self::Unpaid => 'danger',
            self::Voided => 'gray',
        };
    }
}
