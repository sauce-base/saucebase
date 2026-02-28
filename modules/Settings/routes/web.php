<?php

use Illuminate\Support\Facades\Route;
use Modules\Settings\Http\Controllers\PasswordController;
use Modules\Settings\Http\Controllers\ProfileController;
use Modules\Settings\Http\Controllers\SettingsController;

Route::group(['middleware' => [
    'auth',
    'verified',
    'role:admin|user',
]], function () {
    Route::prefix('settings')->group(function () {
        Route::get('/', [SettingsController::class, 'index'])
            ->name('settings.index');

        Route::get('profile', [ProfileController::class, 'show'])
            ->name('settings.profile');

        Route::get('profile/edit', [ProfileController::class, 'edit'])
            ->name('settings.profile.edit');

        Route::patch('profile/info', [ProfileController::class, 'updateInfo'])
            ->name('settings.profile.update-info');

        Route::post('profile/avatar', [ProfileController::class, 'updateAvatar'])
            ->name('settings.profile.update-avatar');

        Route::delete('profile/avatar', [ProfileController::class, 'deleteAvatar'])
            ->name('settings.profile.delete-avatar');

        Route::get('profile/password', [PasswordController::class, 'edit'])
            ->name('settings.profile.password.edit');

        Route::put('profile/password', [PasswordController::class, 'update'])
            ->name('settings.profile.password.update');
    });
});
