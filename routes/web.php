<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\IndexController;
use App\Http\Controllers\LocalizationController;
use App\Http\Controllers\PrivacyController;
use App\Http\Controllers\TermsController;
use Illuminate\Support\Facades\Route;

Route::get('/', IndexController::class)->name('index');

Route::get('/privacy', PrivacyController::class)->name('privacy');
Route::get('/terms', TermsController::class)->name('terms');

Route::post('/locale/{locale}', LocalizationController::class)->name('locale');

Route::middleware(['auth', 'verified', 'role:admin|user'])->group(function () {
    Route::get('/dashboard', DashboardController::class)->name('dashboard');
});
