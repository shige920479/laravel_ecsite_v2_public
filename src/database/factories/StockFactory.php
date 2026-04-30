<?php

namespace Database\Factories;

use App\Models\Item;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Stock>
 */
class StockFactory extends Factory
{
    public function definition(): array
    {
        return [
            'item_id' => Item::factory(),
            'stock_diff' => fake()->numberBetween(20, 50),
            'reason' => fake()->realText(20)
        ];
    }
}
