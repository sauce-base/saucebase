<?php

/*
    |--------------------------------------------------------------------------
    | Social Authentication
    |--------------------------------------------------------------------------
    |
    | The following language lines are used for social authentication features,
    | including connecting and disconnecting social accounts, handling errors,
    | and providing feedback messages for various social login scenarios.
    |
    */

return [
    'connect_with' => 'Connect with :Provider',
    'disconnect' => 'Disconnect',
    'not_connected' => 'The provider :Provider is not connected',
    'connected' => 'Connected',
    'account_connected' => ':Provider account connected successfully',
    'account_disconnected' => ':Provider account disconnected successfully',
    'error' => 'An error occurred while processing your social account',
    'invalid_user' => 'Invalid social account data received',
    'invalid_provider' => 'The selected social provider is invalid or not supported',
    'cannot_disconnect_only_method' => 'Cannot disconnect your only login method. Set a password first or connect another provider and try again',
    'missing_social_accounts_relation' => 'The User model is missing the socialAccounts relationship required for social authentication',
    'account_already_linked' => 'This :provider account is already linked to another user',
    'unsupported_provider' => 'The social provider :provider is not supported',
];
