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
            self::Card => __('Card'),
            self::PayPal => __('PayPal'),
            self::SepaDebit => __('SEPA Debit'),
            self::UsBankAccount => __('US Bank Account'),
            self::BacsDebit => __('Bacs Debit'),
            self::Link => __('Link'),
            self::CashApp => __('Cash App'),
            self::ApplePay => __('Apple Pay'),
            self::GooglePay => __('Google Pay'),
            self::Bancontact => __('Bancontact'),
            self::Ideal => __('iDEAL'),
            self::Unknown => __('Unknown'),
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
