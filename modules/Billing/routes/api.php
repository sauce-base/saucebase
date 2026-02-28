<?php

use Illuminate\Support\Facades\Route;
use Modules\Billing\Http\Controllers\WebhookController;

// Webhook routes without CSRF protection
Route::post('/billing/webhooks/{provider}', WebhookController::class)
    ->name('billing.webhooks')
    ->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class]);
