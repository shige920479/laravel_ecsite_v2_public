<?php

namespace Database\Factories;

use App\Models\Item;
use App\Models\Order;
use App\Services\TaxCalculator;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\OrderItem>
 */
class OrderItemFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'quantity' => 1,
        ];
    }

    public function forItem(Item $item, int $quantity): self
    {
        $priceData = TaxCalculator::calculateItem($item->price_ex_tax, $quantity);

        return $this->state([
            'item_id' => $item->id,
            'item_name' => $item->name,
            'quantity' => $quantity,
            'price_ex_tax' => $item->price_ex_tax,
            'tax_rate' => $priceData['tax_rate'],
            'price_tax' => $priceData['unit_tax_amount'],
            'price_in_tax' => $priceData['unit_in_tax'],
            'subtotal_ex_tax' => $priceData['subtotal_ex_tax'],
            'subtotal_tax' => $priceData['subtotal_tax'],
            'subtotal_in_tax' => $priceData['subtotal_in_tax'],
        ]);
    }
}
