<?php

use Illuminate\Support\Facades\Route;
use Modules\Themes\Http\Controllers\ThemesController;

Route::post('/themes', [ThemesController::class, 'store'])
    ->middleware('throttle:10,1')
    ->name('themes.store');

Route::put('/themes/{name}', [ThemesController::class, 'update'])
    ->name('themes.update');

Route::delete('/themes/{name}', [ThemesController::class, 'destroy'])
    ->name('themes.destroy');
