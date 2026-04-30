<?php

namespace Database\Factories;

use App\Models\Cart;
use App\Models\CheckoutRequest;
use App\Models\Item;
use App\Models\User;
use App\Services\TaxCalculator;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\CheckoutItem>
 */
class CheckoutItemFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return ['quantity' => 1];
    }

    public function forCheckoutRequest(CheckoutRequest $checkoutRequest)
    {
        return $this->state(fn () => [
            'checkout_request_id' => $checkoutRequest->id,
        ]);
    }

    public function forCart(Cart $cart)
    {
        if (! $cart->relationLoaded('item')) {
            $cart->load('item');
        }

        return $this->state(function () use ($cart) {
            $priceExTax = $cart->item->price_ex_tax;
            $quantity = $cart->quantity;
            $calc = TaxCalculator::calculateItem($priceExTax, $quantity);

            return [
                'cart_id' => $cart->id,
                'shop_id' => $cart->item->shop_id,
                'item_id' => $cart->item->id,
                'item_name' => $cart->item->name,
                'quantity' => $quantity,
                'price_ex_tax' => $priceExTax,
                'tax_rate' => $calc['tax_rate'],
                'price_tax' => $calc['unit_tax_amount'],
                'price_in_tax' => $calc['unit_in_tax'],
                'subtotal_ex_tax' => $calc['subtotal_ex_tax'],
                'subtotal_tax' => $calc['subtotal_tax'],
                'subtotal_in_tax'=> $calc['subtotal_in_tax'],
            ];
        });
    }
}
