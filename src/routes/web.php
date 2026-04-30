<?php

use App\Http\Controllers\Api\CartApiController;
use App\Http\Controllers\Api\CheckoutApiController;
use App\Http\Controllers\Api\FavoriteApiController;
use App\Http\Controllers\Api\ItemApiController;
use App\Http\Controllers\Api\MyReviewApiController;
use App\Http\Controllers\Api\ReviewsApiController;
use App\Http\Controllers\Api\WebhookController;
use App\Http\Controllers\Auth\UserAccountController;
use App\Http\Controllers\Customer\CartController;
use App\Http\Controllers\Customer\CheckoutPageController;
use App\Http\Controllers\Customer\FavoriteController;
use App\Http\Controllers\Customer\GuestController;
use App\Http\Controllers\Customer\MyOrdersController;
use App\Http\Controllers\Customer\MyReviewController;
use App\Http\Controllers\Customer\ReviewPageController;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

require __DIR__.'/admin.php';
require __DIR__.'/owner.php';
require __DIR__.'/demo.php';

if(! app()->isProduction()) {
    require __DIR__.'/dev/test.php';
}

// $guards = ['auth:web', 'verified']; メール認証の確認用
$guards = ['auth:web'];

Route::controller(GuestController::class)->group(function () {
    Route::get('/', 'index')->name('home.index');
    Route::get('/category/{category:slug}', 'index')->name('category.index');
    Route::get('/category/{category:slug}/{subCategory:slug}', 'index')->name('subCategory.index');
    Route::get('/category/{category:slug}/{subCategory:slug}/{itemCategory:slug}', 'index')->name('itemCategory.index');
    Route::get('/item/{item}', 'show')->name('item.show');
    Route::get('/items/ranking', 'ranking')->name('items.ranking');
});

// レビュー一覧
Route::get('/item/{item}/reviews', [ReviewPageController::class, 'index'])->name('item.reviews');

Route::middleware($guards)->group(function () {
    
    // カート一覧表示
    Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
    // お気に入り一覧表示
    Route::get('/favorite', [FavoriteController::class, 'index'])->name('favorite.index');
    // 注文確認・完了
    Route::post('/checkout/confirm', [CheckoutPageController::class, 'confirm'])->name('checkout.confirm');
    Route::get('/checkout/success', [CheckoutPageController::class, 'success'])->name('checkout.success');
    Route::get('/checkout/cancel', [CheckoutPageController::class, 'cancel'])->name('checkout.cancel');

    // 注文履歴
    Route::get('/mypage/orders', [MyOrdersController::class, 'index'])->name('mypage.orders.index');
    Route::get('/mypage/orders/{order}', [MyOrdersController::class, 'show'])->name('mypage.orders.show');
    
    // 投稿履歴
    Route::get('/mypage/reviews', [MyReviewController::class, 'index'])->name('mypage.reviews.index');

    // アカウント登録
    Route::get('/mypage/account', [UserAccountController::class, 'edit'])->name('mypage.account.edit');
    Route::patch('/mypage/account', [UserAccountController::class, 'update'])->name('mypage.account.update');

});

Route::get('/cancel-ui', function () {
    return view('user.checkout.cancel');
});

// Webhook受信用
Route::post('/webhook/stripe', [WebhookController::class, 'handle'])
    ->withoutMiddleware([VerifyCsrfToken::class])->name('webhook.stripe');

// 内部API用ルーティング
Route::prefix('/api')->group(function () use ($guards) {
    
    Route::get('/items/{category:slug?}/{subCategory:slug?}/{itemCategory:slug?}',
        [ItemApiController::class, 'index']
    );

    // レビュー
    Route::get('/item/{item}/reviews', [ReviewsApiController::class, 'index']);

    Route::middleware($guards)->group(function () {

        //　お気に入り
        Route::get('/favorite', [FavoriteApiController::class, 'index']);
        Route::post('/items/{item}/favorite', [FavoriteApiController::class, 'store']);
        Route::delete('/items/{item}/favorite', [FavoriteApiController::class, 'destroy']);
        Route::post('items/{item}/moveToCart', [FavoriteApiController::class, 'moveToCart']);

        // カート
        Route::get('/cart', [CartApiController::class, 'index']);
        Route::post('/cart', [CartApiController::class, 'store']);
        Route::patch('/cart/{cart}', [CartApiController::class, 'update']);
        Route::delete('/cart/{cart}', [CartApiController::class, 'destroy']);

        // オーダー
        Route::post('/checkout', [CheckoutApiController::class, 'store']);

        // レビュー投稿・編集
        Route::post('/item/{item}/review', [ReviewsApiController::class, 'store']);
        Route::patch('/reviews/{review}', [ReviewsApiController::class, 'update']);
        Route::delete('/reviews/{review}', [ReviewsApiController::class, 'destroy']);
        Route::post('/reviews/{review}/toggle', [ReviewsApiController::class, 'toggleHelpful']);

        // マイページ レビュー一覧
        Route::get('/mypage/reviews', [MyReviewApiController::class, 'index']);

    });


});


