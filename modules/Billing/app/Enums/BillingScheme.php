<?php

namespace Modules\Billing\Enums;

use Filament\Support\Contracts\HasLabel;

enum BillingScheme: string implements HasLabel
{
    case FlatRate = 'flat_rate';
    case PerUnit = 'per_unit';

    public function getLabel(): string
    {
        return match ($this) {
            self::FlatRate => __('Flat Rate'),
            self::PerUnit => __('Per Unit'),
        };
    }
}
