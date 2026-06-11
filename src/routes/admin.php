<?php

use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\OwnersController;
use App\Http\Controllers\Admin\ReviewController;
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
    
        Route::get('/reviews', [ReviewController::class, 'index'])->name('reviews');
        Route::get('/reviews/{review}', [ReviewController::class, 'show'])->name('review.show');
        Route::delete('/reviews/{review}', [ReviewController::class, 'destroy'])->name('review.destroy');

        Route::get('/admins', [AdminController::class, 'index'])->name('admins.index');
        Route::get('/admins/create', [AdminController::class, 'create'])->name('admins.create');
        Route::post('/admins', [AdminController::class, 'store'])->name('admins.store');
        Route::get('/admins/{admin}/edit', [AdminController::class, 'edit'])->name('admins.edit');
        Route::patch('/admins/{admin}', [AdminController::class, 'update'])->name('admins.update');
        Route::delete('/admins/{admin}', [AdminController::class, 'destroy'])->name('admins.destroy');
        Route::get('/admins/trashed', [AdminController::class, 'trashed'])->name('admins.trashed');
        Route::patch('/admins/{admin}/restore', [AdminController::class, 'restore'])->name('admins.restore');
    });

});

Route::get('/admin/test', function () {
    return view('admin.super-admin.index');
});
