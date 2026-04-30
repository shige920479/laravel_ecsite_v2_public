<?php

use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\OwnersController;
use App\Http\Controllers\Auth\AdminAuthController;
use Illuminate\Support\Facades\Route;

Route::prefix('admin')->name('admin.')->group(function () {
    // 認証
    Route::get('/login', [AdminAuthController::class, 'loginForm'])
        ->middleware('guest:web_admin')
        ->name('login');

    Route::post('/login', [AdminAuthController::class, 'login'])
        ->middleware('guest:web_admin')
        ->name('login.submit');

    Route::post('/logout', [AdminAuthController::class, 'logout'])
        ->middleware('auth:web_admin')
        ->name('logout');

    // 管理者機能
    Route::middleware('auth:web_admin')->group(function() {

        Route::get('/home', fn() => view('admin.home'))->name('home');

        Route::resource('owners', OwnersController::class)->except('show');
        Route::get('/categories', [CategoryController::class, 'index'])->name('categories.index');
        Route::get('/category', [CategoryController::class, 'createCategory'])->name('category.create');
        Route::post('/category', [CategoryController::class, 'storeCategory'])->name('category.store');
        Route::get('/sub-category', [CategoryController::class, 'createSubCategory'])->name('subCategory.create');
        Route::post('/sub-category', [CategoryController::class, 'storeSubCategory'])->name('subCategory.store');
        Route::get('/item-category', [CategoryController::class, 'createItemCategory'])->name('itemCategory.create');
        Route::post('/item-category', [CategoryController::class, 'storeItemCategory'])->name('itemCategory.store');
        Route::get('/item-category/{itemCategory}/edit', [CategoryController::class, 'editItemCategory'])->name('itemCategory.edit');
        Route::put('/item-category/{itemCategory}', [CategoryController::class, 'updateItemCategory'])->name('itemCategory.update');
    });

});
