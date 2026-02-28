<?php

use Illuminate\Support\Facades\Route;
use Modules\Settings\Http\Controllers\SettingsController;

Route::middleware(['auth:sanctum'])->prefix('api/v1/settings')->group(function () {
    Route::apiResource('settings', SettingsController::class, ['as' => 'api']);
});
