<?php

use App\Http\Controllers\Auth\OwnerAuthController;
use App\Http\Controllers\Owner\ItemController;
use App\Http\Controllers\Owner\ItemImageController;
use App\Http\Controllers\Owner\SessionClearController;
use App\Http\Controllers\Owner\ShopController;
use App\Http\Controllers\Owner\StockController;
use App\Http\Controllers\Owner\TmpImageController;
use Illuminate\Support\Facades\Route;

use Intervention\Image\Laravel\Facades\Image;

Route::prefix('/owner')->name('owner.')->group(function () {

  Route::middleware('guest:web_owner')->group(function () {
    Route::get('/login', [OwnerAuthController::class, 'loginForm'])->name('login');
    Route::post('/login', [OwnerAuthController::class, 'login'])->name('login.submit');
  });

  Route::middleware('auth:web_owner')->group(function () {
    
    Route::post('/logout', [OwnerAuthController::class, 'logout'])->name('logout');
    Route::post('/session-clear', [SessionClearController::class, 'clear'])->name('session.clear');

    // ショップ
    Route::resource('/shop', ShopController::class)->except(['show', 'destroy']);

    // 画像操作
    Route::post('/tmp/image', [TmpImageController::class, 'store'])->name('tmp.image.store');

    // 商品操作
    Route::resource('item', ItemController::class);
    Route::post('/item/confirm', [ItemController::class, 'confirm'])->name('item.confirm');
    Route::post('/item/{item}/updateConfirm', [ItemController::class, 'updateConfirm'])->name('item.updateConfirm');
    Route::get('/items/download', [ItemController::class, 'downloadCsvItem'])->name('items.csv');
    Route::patch('/items/{item}/selling-status', [ItemController::class, 'toggleIsSelling'])->name('item.toggleIsSelling');


    // 商品画像操作
    Route::get('/item/{item}/image', [ItemImageController::class, 'create'])->name('item.image.create');
    Route::post('/item/{item}/image', [ItemImageController::class, 'store'])->name('item.image.store');
    Route::get('/item/{item}/image/edit', [ItemImageController::class, 'edit'])->name('item.image.edit');
    Route::put('/item/{item}/image/update', [ItemImageController::class, 'update'])->name('item.image.update');
    Route::delete('/item-image/{itemImage}/delete', [ItemImageController::class, 'destroy'])->name('item.image.delete');

    // 画像登録 非同期
    Route::post('/tmp/image', [TmpImageController::class, 'store'])->name('tmp.image.store');
    Route::delete('/tmp/itemImage/{index}', [TmpImageController::class, 'deleteTmpImage'])->name('tmp.itemImage.delete');

    // 在庫
    Route::get('/item/{item}/stocks/index', [StockController::class, 'index'])->name('item.stocks.index');
    Route::get('/item/{item}/stock', [StockController::class, 'create'])->name('item.stock.create');
    Route::post('/item/{item}/stock', [StockController::class, 'store'])->name('item.stock.store');
    
    // 在庫CSV
    Route::get('item/{item}/stocks/csv', [StockController::class, 'downloadCsv'])->name('item.stocks.csv');
    Route::get('/stocks/upload', [StockController::class, 'showUploadForm'])->name('stocks.csv.create');
    Route::post('/stocks/upload', [StockController::class, 'storeFromCsv'])->name('stocks.csv.store');

  });


});

Route::get('/view-test', function () {
  return view('owner.stocks.upload');
});



Route::get('/test-image', function () {
  $image = Image::read(public_path('/images/mug1.jpg'))
            ->scale(width:300)
            ->toJpeg(80);
  
  return response($image)->header('Content-Type', 'image/jpeg');
});

