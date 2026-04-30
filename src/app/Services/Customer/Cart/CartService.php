<?php
namespace App\Services\Customer\Cart;

use App\Models\Cart;
use App\Models\CheckoutItem;
use App\Models\CheckoutRequest;
use App\Models\Item;
use App\Services\Customer\Cart\DTO\CreateCartDto;
use App\Services\Customer\Cart\Exceptions\DuplicateItemException;
use Illuminate\Support\Facades\Log;

class CartService implements CartServiceInterface
{
    /** カートに登録（重複/販売中/在庫チェック） */
    public function store(CreateCartDto $dto): void
    {
        $user = $dto->user;
        if ($user->carts()->where('item_id', $dto->itemId)->exists()) {
            throw new DuplicateItemException();
        }

        $item = Item::findOrFail($dto->itemId);
        $item->isPurchasable($dto->quantity);

        $user->carts()->create([
            'item_id' => $dto->itemId,
            'quantity' => $dto->quantity,
        ]);
    }

    /** 数量更新（販売中/在庫チェック） */
    public function updateQuantity(Cart $cart, int $quantity): void
    {
        $cart->item->isPurchasable($quantity);

        $cart->update(['quantity' => $quantity]);
    }
    
    /** 決済完了後のカート削除（ChekoutRequestのcart_id) */
    public function deleteFromCheckout(CheckoutRequest $checkoutRequest): void
    {
        $cartIds = CheckoutItem::where('checkout_request_id', $checkoutRequest->id)
            ->pluck('cart_id')
            ->values();

        Cart::whereIn('id', $cartIds)->delete();
    }
}