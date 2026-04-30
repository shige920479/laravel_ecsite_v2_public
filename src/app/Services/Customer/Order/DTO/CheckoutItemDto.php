<?php
namespace App\Services\Customer\Order\DTO;

use App\Models\Cart;
use App\Models\Item;
use App\Models\User;
use App\Services\TaxCalculator;
use Illuminate\Database\Eloquent\Collection;


/**
 * 
 * @package App\Services\Customer\Order\DTO
 */
class CheckoutItemDto
{
    public function __construct(
        public int $userId,
        public int $cartId,
        public int $shopId,
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

    public static function getItemData(User $user, Cart $cart): self
    {
        $priceData = TaxCalculator::calculateItem($cart->item->price_ex_tax, $cart->quantity);
        
        return new self(
            userId: $user->id,
            cartId: $cart->id,
            shopId: $cart->item->shop_id,
            itemId: $cart->item->id,
            itemName: $cart->item->name,
            quantity: $cart->quantity,
            priceExTax: $cart->item->price_ex_tax,
            taxRate: $priceData['tax_rate'],
            priceTax: $priceData['unit_tax_amount'],
            priceInTax: $priceData['unit_in_tax'],
            subtotalExTax: $priceData['subtotal_ex_tax'],
            subtotalTax: $priceData['subtotal_tax'],
            subtotalInTax: $priceData['subtotal_in_tax'],
        );
    }
}