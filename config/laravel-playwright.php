<?php

return [
    /**
     * The prefix for the Playwright endpoints.
     * For example, if you set this to 'playwright',
     * the endpoints will be available at /playwright/*.
     */
    'prefix' => env('PLAYWRIGHT_PREFIX', 'playwright'),

    /**
     * The environments in which the Playwright endpoints should be available.
     */
    'environments' => ['local', 'testing'],

    /**
     * The secret key used to authenticate requests to the Playwright endpoints.
     * You should set this to a random string in your .env file
     * to prevent unauthorized access to the endpoints.
     */
    'secret' => env('PLAYWRIGHT_SECRET'),
];
