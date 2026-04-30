<?php
namespace App\Services\Customer\Cart;

use App\Models\Cart;
use App\Models\CheckoutRequest;
use App\Services\Customer\Cart\DTO\CreateCartDto;

interface CartServiceInterface
{
    /** カートに登録（重複/販売中/在庫チェック） */
    public function store(CreateCartDto $dto): void;

    /** 数量更新（販売中/在庫チェック） */
    public function updateQuantity(Cart $cart, int $quantity): void;

    /** 決済完了後のカート削除（ChekoutRequestのcart_id) */
    public function deleteFromCheckout(CheckoutRequest $checkoutRequest): void;
}