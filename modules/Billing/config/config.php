<?php

return [
    'name' => 'Billing',

    /*
    |--------------------------------------------------------------------------
    | Default Currency
    |--------------------------------------------------------------------------
    |
    | The default currency for billing operations.
    | Uses ISO 4217 currency codes (e.g., EUR, BRL, USD,...).
    |
    */
    'default_currency' => env('BILLING_DEFAULT_CURRENCY', 'EUR'),

    /*
    |--------------------------------------------------------------------------
    | Default Payment Gateway
    |--------------------------------------------------------------------------
    |
    | Select which payment gateway to use by default.
    | Available options: 'stripe', 'paddle', 'lemonsqueezy'
    |
    */
    'default_gateway' => env('BILLING_GATEWAY', 'stripe'),

    /*
    |--------------------------------------------------------------------------
    | Payment Gateway Logging
    |--------------------------------------------------------------------------
    |
    | Enable or disable logging of payment gateway operations.
    | Useful for debugging and monitoring payment flows.
    |
    */
    'logging' => [
        'enabled' => env('BILLING_LOGGING_ENABLED', true),
        'channel' => env('BILLING_LOG_CHANNEL', 'stack'),
    ],

    'checkout' => [
        // Minutes before a pending session with no action is marked Abandoned. Default: 60 (1 hour).
        'abandon_after_minutes' => env('BILLING_CHECKOUT_ABANDON_MINUTES', 60),

        // Minutes before a pending session is fully Expired. Default: 1440 (24h) — matches Stripe's TTL.
        'expire_after_minutes' => env('BILLING_CHECKOUT_EXPIRE_MINUTES', 1440),
    ],
];
