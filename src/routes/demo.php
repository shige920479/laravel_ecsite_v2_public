<?php

use App\Http\Controllers\Auth\DemoLoginController;
use Illuminate\Support\Facades\Route;

Route::prefix('/demo')
    ->controller(DemoLoginController::class)->group(function () {

    Route::post('/userLogin', 'userDemoLogin')->middleware('guest:web')->name('demo.userLogin');
    Route::post('/ownerLogin', 'ownerDemoLogin')->middleware('guest:web_owner')->name('demo.ownerLogin');
    Route::post('/adminLogin', 'adminDemoLogin')->middleware('guest:web_admin')->name('demo.adminLogin');

    });


