<?php
namespace App\Services\Customer\Order\DTO;

use App\Enums\ShippingStatus;
use App\Models\CheckoutItem;
use App\Models\Item;
use App\Models\Shop;
use App\Models\User;

class StoreShipmentCommand
{
    public function __construct(
        public int $orderId,
        public int $shopId,
        public string $shippingName,
        public string $shippingPostcode,
        public string $shippingAddress,
        public string $shippingPhone,
        public ShippingStatus $shippingStatus,
    )
    {
    }

    public static function createCommand(int $orderId, int $shopId, User $user)
    {
        return new self(
            orderId: $orderId,
            shopId: $shopId,
            shippingName: $user->name,
            shippingPostcode: $user->postcode,
            shippingAddress: $user->address,
            shippingPhone: $user->phone,
            shippingStatus: ShippingStatus::UNSHIPPED,
        );
    }
}