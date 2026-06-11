<?php

use App\Http\Controllers\Auth\GoogleAuthController;
use Illuminate\Support\Facades\Route;

Route::controller(GoogleAuthController::class)->group(function () {
    Route::get('/auth/google/redirect', 'redirect')->name('auth.google.redirect');
    Route::get('/auth/google/callback', 'callback')->name('auth.google.callback');
});