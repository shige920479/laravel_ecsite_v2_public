<?php
namespace App\Services\Customer\Order;

use App\Enums\CheckoutStatus;
use App\Exceptions\OverStockException;
use App\Exceptions\SalesSuspendedException;
use App\Models\Cart;
use App\Models\CheckoutItem;
use App\Models\CheckoutRequest;
use App\Models\Item;
use App\Models\StockHistory;
use App\Models\User;
use App\Services\Customer\Order\DTO\CheckoutItemDto;
use App\Services\Customer\Order\Exceptions\InValidCartsException;
use App\Services\Customer\Order\Exceptions\NotRegiteredAccountException;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Collection as SupportCollection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class CheckoutService implements CheckoutServiceInterface
{
    /** アカウント登録・販売中・在庫をチェック */
    public function isOrderable(User $user, Collection $carts): array
    {
        $errors = [];

        try {
            $user->ensureRegistered();

        } catch (NotRegiteredAccountException $e) {
            return ['account' => $e->getMessage()];
        }

        foreach ($carts as $cart) {
            try {
                $cart->item->isPurchasable($cart->quantity);

            } catch (OverStockException $e) {
                $errors[$cart->id]['quantity'] =
                    "在庫が不足しています（在庫: {$cart->item->stock_current}）";

            } catch (SalesSuspendedException $e) {
                $errors[$cart->id]['quantity'] =
                    "販売停止中のため注文できません";
            }
        }

        return $errors;
    }

    public function isValidCartIds(User $user, array $ids): void
    {
        $storedIds = Cart::query()
            ->where('user_id', $user->id)
            ->whereIn('id', $ids)
            ->get();
        
        $distinctCartIds = array_unique($ids);
        
        if ($storedIds->count() !== count($ids) || count($distinctCartIds) !== count($ids)) {
            throw new InValidCartsException();
        }
    }

    public function reserveStockAndStoreSnap(SupportCollection $cartItems): SupportCollection
    {
        $snapShots = DB::transaction(function () use ($cartItems) {

            // id取得のため、先に仮データを登録
            $checkoutRequest = $this->initCheckout($cartItems);

            $checkoutItems = [];
            $totalExTax = 0;
            $totalTax = 0;
            $totalInTax = 0;

            foreach ($cartItems as $cartItem) {

                $this->reserveStock($cartItem);
                
                $checkoutItem = $this->createCheckoutItem($cartItem, $checkoutRequest->id);
                $totalExTax += $checkoutItem->subtotal_ex_tax;
                $totalTax += $checkoutItem->subtotal_tax;
                $totalInTax += $checkoutItem->subtotal_in_tax;
                $checkoutItems[] = $checkoutItem;
            }

            $checkoutRequest->update([
                'total_ex_tax' => $totalExTax,
                'total_tax' => $totalTax, 
                'total_in_tax' => $totalInTax,
            ]);

            return collect($checkoutItems);
        });

        return $snapShots;
    }

    /**
     * チェックアウト処理のロールバック（トランザクション内包）
     * 在庫戻し処理＋在庫履歴登録＋checkoutRequestのステータス変更
     * 
     */
    public function rollbackCheckout(SupportCollection $checkoutItems, CheckoutStatus $status, string $reason): void
    {
        DB::transaction(function () use ($checkoutItems, $status, $reason) {
            
            $checkoutRequestId = $checkoutItems->first()->checkout_request_id;
            $checkoutRequest = CheckoutRequest::whereKey($checkoutRequestId)
                ->lockForUpdate()
                ->firstOrFail();

            foreach ($checkoutItems as $stored) {
                $this->releaseStock($stored, $reason);
            }

            $checkoutRequest->update(['status' => $status]);
        });
    }

    public function updateStatus(CheckoutRequest $checkoutRequest, CheckoutStatus $status): void
    {
        $checkoutRequest->update(['status' => $status]);
    }

    private function initCheckout(SupportCollection $cartItems)
    {
            $checkoutRequest = CheckoutRequest::create([
                'user_id' => $cartItems->first()->userId,
                'status' => CheckoutStatus::PENDING,
                'total_ex_tax' => 0,
                'total_tax' => 0,
                'total_in_tax' => 0,
                'expires_at' => now()->addMinutes(30),
            ]);

            return $checkoutRequest;
    }

    private function reserveStock(CheckoutItemDto $cartItem)
    {
        // 在庫確保
        $item = Item::whereKey($cartItem->itemId)->lockForUpdate()->first();
        if ($item->stock_current < $cartItem->quantity) {
            throw new OverStockException();
        }
        $item->decrement('stock_current', $cartItem->quantity);
        $item->refresh();

        // 在庫履歴登録
        StockHistory::create([
            'item_id' => $cartItem->itemId,
            'stock_diff' => -$cartItem->quantity,
            'stock_after' => $item->stock_current,
            'reason' => "注文時の在庫確保",
        ]);
    }

    private function releaseStock(CheckoutItem $stored, string $reason)
    {
        $item = Item::whereKey($stored->item_id)->lockForUpdate()->first();
        $item->increment('stock_current', $stored->quantity);
        $item->refresh();

        StockHistory::create([
            'item_id' => $stored->item_id,
            'stock_diff' => $stored->quantity,
            'stock_after' => $item->stock_current,
            'reason' => $reason,
        ]);
    }

    private function createCheckoutItem(CheckoutItemDto $cartItem, int $checkoutRequestId)
    {
        $checkoutItem = CheckoutItem::create([
            'checkout_request_id' => $checkoutRequestId,
            'cart_id' => $cartItem->cartId,
            'shop_id' => $cartItem->shopId,
            'item_id' => $cartItem->itemId,
            'item_name' => $cartItem->itemName,
            'quantity' => $cartItem->quantity,
            'price_ex_tax' => $cartItem->priceExTax,
            'tax_rate' => $cartItem->taxRate,
            'price_tax' => $cartItem->priceTax,
            'price_in_tax' => $cartItem->priceInTax,
            'subtotal_ex_tax' => $cartItem->subtotalExTax,
            'subtotal_tax' => $cartItem->subtotalTax,
            'subtotal_in_tax' => $cartItem->subtotalInTax,
        ]);

        return $checkoutItem;
    }












}