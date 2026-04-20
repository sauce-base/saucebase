<?php

use Illuminate\Support\Facades\Route;
use Modules\Blog\Http\Controllers\BlogController;

Route::get('/blog', [BlogController::class, 'index'])->name('blog.index');
Route::get('/blog/{category}/{slug}', [BlogController::class, 'show'])->name('blog.show.category');
Route::get('/blog/{slug}', [BlogController::class, 'show'])->name('blog.show');
