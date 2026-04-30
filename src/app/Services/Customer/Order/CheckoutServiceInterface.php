<?php
namespace App\Services\Customer\Order;

use App\Enums\CheckoutStatus;
use App\Models\CheckoutRequest;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Collection as SupportCollection;

interface CheckoutServiceInterface
{
    public function isOrderable(User $user, Collection $carts): array;
    public function isValidCartIds(User $user, array $ids): void;
    public function reserveStockAndStoreSnap(SupportCollection $cartItems): SupportCollection;
    public function rollbackCheckout(SupportCollection $checkoutItems, CheckoutStatus $status, string $reason): void;
    public function updateStatus(CheckoutRequest $checkoutRequest, CheckoutStatus $status): void;
}