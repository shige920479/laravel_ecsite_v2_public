<?php

namespace Database\Factories;

use App\Models\Item;
use App\Models\StockHistory;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\StockHistory>
 */
class StockHistoryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'item_id' => Item::factory(),
            'stock_diff' => fake()->numberBetween(1, 20),
            'stock_after' => 0,
            'reason' => fake()->randomElement([
                '入荷', '調整'
            ])
        ];
    }

    public function configure()
    {
        return $this->afterCreating(function (StockHistory $history) {

            $item = $history->item()->lockForUpdate()->first();
            $item->increment('stock_current', $history->stock_diff);

            $history->updateQuietly([
                'stock_after' => $item->stock_current
            ]);
        });
    }

    public function order(int $quantity = 1): self
    {
        return $this->state([
            'stock_diff' => -$quantity,
            'reason' => "出荷"
        ]);
    }
}
