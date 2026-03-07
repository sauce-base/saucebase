<?php

use Illuminate\Support\Facades\Route;
use Modules\Announcements\Http\Controllers\DismissAnnouncementController;

Route::post('/announcements/{announcement}/dismiss', DismissAnnouncementController::class)
    ->name('announcements.dismiss');
