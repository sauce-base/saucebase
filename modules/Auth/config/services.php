<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Socialite Providers
    |--------------------------------------------------------------------------
    | This file is for storing the credentials for social authentication
    |
    | @link https://laravel.com/docs/socialite Laravel Socialite Documentation
    |--------------------------------------------------------------------------
    */

    /**
     * Social services credentials and configuration
     */
    'google' => [
        'client_id' => env('GOOGLE_CLIENT_ID'),
        'client_secret' => env('GOOGLE_CLIENT_SECRET'),
        'redirect' => env('GOOGLE_CLIENT_REDIRECT_URI', '/auth/socialite/google/callback'),
    ],

    'github' => [
        'client_id' => env('GITHUB_CLIENT_ID'),
        'client_secret' => env('GITHUB_CLIENT_SECRET'),
        'redirect' => env('GITHUB_CLIENT_REDIRECT_URI', '/auth/socialite/github/callback'),
    ],

    'socialite_providers' => [
        [
            'name' => 'google',
            'label' => 'Google',
        ],
        [
            'name' => 'github',
            'label' => 'GitHub',
        ],
    ],
];
