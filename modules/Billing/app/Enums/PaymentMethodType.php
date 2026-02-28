<?php

namespace Modules\Billing\Enums;

use Filament\Support\Contracts\HasLabel;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript]
enum PaymentMethodType: string implements HasLabel
{
    case Card = 'card';
    case PayPal = 'paypal';
    case SepaDebit = 'sepa_debit';
    case UsBankAccount = 'us_bank_account';
    case BacsDebit = 'bacs_debit';
    case Link = 'link';
    case CashApp = 'cashapp';
    case ApplePay = 'apple_pay';
    case GooglePay = 'google_pay';
    case Bancontact = 'bancontact';
    case Ideal = 'ideal';
    case Unknown = 'unknown';

    public function getLabel(): string
    {
        return match ($this) {
            self::Card => 'Card',
            self::PayPal => 'PayPal',
            self::SepaDebit => 'SEPA Debit',
            self::UsBankAccount => 'US Bank Account',
            self::BacsDebit => 'Bacs Debit',
            self::Link => 'Link',
            self::CashApp => 'Cash App',
            self::ApplePay => 'Apple Pay',
            self::GooglePay => 'Google Pay',
            self::Bancontact => 'Bancontact',
            self::Ideal => 'iDEAL',
            self::Unknown => 'Unknown',
        };
    }

    public function category(): string
    {
        return match ($this) {
            self::Card, self::ApplePay, self::GooglePay => 'card',
            self::SepaDebit, self::UsBankAccount, self::BacsDebit,
            self::Bancontact, self::Ideal => 'bank',
            self::PayPal, self::Link, self::CashApp => 'wallet',
            self::Unknown => 'unknown',
        };
    }
}
