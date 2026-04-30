<?php
namespace App\Services\Customer\Order\DTO;

use App\Models\CheckoutItem;
use App\Models\CheckoutRequest;

class StoreOrderItemCommand
{
    public function __construct(
        public int $shipmentId,
        public int $itemId,
        public string $itemName,
        public int $quantity,
        public int $priceExTax,
        public float $taxRate,
        public int $priceTax,
        public int $priceInTax,
        public int $subtotalExTax,
        public int $subtotalTax,
        public int $subtotalInTax,
    )
    {
    }

    public static function createCommand(int $shipmentId, CheckoutItem $checkoutItem)
    {
        return new self(
            shipmentId: $shipmentId,
            itemId: $checkoutItem->item_id,
            itemName: $checkoutItem->item_name,
            quantity: $checkoutItem->quantity,
            priceExTax: $checkoutItem->price_ex_tax,
            taxRate: $checkoutItem->tax_rate,
            priceTax: $checkoutItem->price_tax,
            priceInTax: $checkoutItem->price_in_tax,
            subtotalExTax: $checkoutItem->subtotal_ex_tax,
            subtotalTax: $checkoutItem->subtotal_tax,
            subtotalInTax: $checkoutItem->subtotal_in_tax,
        );
    }
}