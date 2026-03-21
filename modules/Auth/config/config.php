<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Magic Link Authentication
    |--------------------------------------------------------------------------
    |
    | When enabled, users can log in without a password by requesting a
    | one-time link sent to their email address.
    |
    */

    'magic_link' => [
        'enabled' => env('AUTH_MAGIC_LINK_ENABLED', true),
        'expiry' => env('AUTH_MAGIC_LINK_EXPIRY', 15), // minutes
    ],

];
