<?php

namespace App\Http\Controllers\Api;

use App\Enums\CheckoutStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCheckoutRequest;
use App\Http\Responses\ApiResponse;
use App\Models\Cart;
use App\Services\Customer\Order\CheckoutServiceInterface;
use App\Services\Customer\Order\DTO\CheckoutItemDto;
use App\Services\Customer\Order\StripeServiceInterface;
use App\Support\AppLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class CheckoutApiController extends Controller
{
    use ApiResponse;

    public function __construct(
        private CheckoutServiceInterface $checkoutService,
        private StripeServiceInterface $stripeService,
        )
    {
    }

    public function store(StoreCheckoutRequest $request)
    {
        $cartIds = $request->input('ids');
        $user = $request->user();

        $this->checkoutService->isValidCartIds($user, $cartIds);

        $carts = Cart::query()
            ->where('user_id', $user->id)
            ->whereIn('id', $cartIds)
            ->with('item')
            ->get();

        $cartItems = $carts->map(fn ($cart) => CheckoutItemDto::getItemData($user, $cart));
        $checkoutItems = $this->checkoutService->reserveStockAndStoreSnap($cartItems);
        
        try {
            $url = $this->stripeService->createStripeSession($checkoutItems);

        } catch (\Throwable $e) {
            Log::channel('stripe')->error('Stripeセッション作成失敗', [$e]);
            try {
                $this->checkoutService->rollbackCheckout($checkoutItems, CheckoutStatus::FAILED, 'システムエラー');

            } catch (\Throwable $e2) {
                AppLog::error('Stripeセッション作成時のロールバック処理に失敗', $e2);
            }
            return $this->error('決済画面の作成に失敗しました', status:500);
        }
        return $this->success(['checkout_url' => $url]);
    }
}