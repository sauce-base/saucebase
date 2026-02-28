<?php

namespace Modules\Billing\Enums;

use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript]
enum Currency: string
{
    case USD = 'USD';
    case EUR = 'EUR';
    case GBP = 'GBP';
    case BRL = 'BRL';

    /**
     * Get the default currency from configuration.
     */
    public static function default(): self
    {
        return self::from(config('billing.default_currency'));
    }

    public function formatAmount(int $amountInMinorUnits): string
    {
        $formatter = new \NumberFormatter(app()->getLocale(), \NumberFormatter::CURRENCY);

        return $formatter->formatCurrency($amountInMinorUnits / 100, $this->value);
    }
}
