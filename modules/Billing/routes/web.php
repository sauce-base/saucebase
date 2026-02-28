<?php

use Illuminate\Support\Facades\Route;
use Modules\Billing\Http\Controllers\BillingPortalController;
use Modules\Billing\Http\Controllers\CheckoutController;
use Modules\Billing\Http\Controllers\SettingsBillingController;
use Modules\Billing\Http\Controllers\SubscriptionController;
use Modules\Billing\Http\Middleware\RedirectToRegister;

Route::post('/billing/checkout', [CheckoutController::class, 'create'])->middleware('throttle:10,1')->name('billing.checkout.create');

Route::middleware(RedirectToRegister::class)->group(function () {
    Route::get('/billing/checkout/{checkout_session}', [CheckoutController::class, 'show'])
        ->name('billing.checkout')
        ->missing(fn () => auth()->guest() ? redirect()->route('register') : abort(404));
    Route::post('/billing/checkout/{checkout_session}', [CheckoutController::class, 'store'])
        ->name('billing.checkout.store')
        ->missing(fn () => auth()->guest() ? redirect()->route('register') : abort(404));
});

Route::middleware('auth')->group(function () {
    Route::get('/billing/portal', BillingPortalController::class)->name('billing.portal');
    Route::post('/billing/subscription/cancel', [SubscriptionController::class, 'cancel'])->name('billing.subscription.cancel');
    Route::post('/billing/subscription/resume', [SubscriptionController::class, 'resume'])->name('billing.subscription.resume');

    Route::get('/settings/billing', [SettingsBillingController::class, 'show'])->name('settings.billing');
});
