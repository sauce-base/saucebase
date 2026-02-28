<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Stripe Payment Gateway
    |--------------------------------------------------------------------------
    |
    | Configuration for Stripe payment processing.
    | These credentials should be stored securely in your .env file.
    |
    */
    'stripe' => [
        'secret_key' => env('STRIPE_SECRET_KEY'),
        'publishable_key' => env('STRIPE_PUBLISHABLE_KEY'),
        'webhook_secret' => env('STRIPE_WEBHOOK_SECRET'),
    ],

    // Additional gateways can be configured here in the future (e.g., Paddle, LemonSqueezy).
];
