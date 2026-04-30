<?php

namespace Database\Factories;

use App\Models\Item;
use App\Models\ItemCategory;
use App\Models\ItemImage;
use App\Models\Shop;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Item>
 */
class ItemFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'shop_id' => Shop::factory(),
            'item_category_id' => ItemCategory::factory(),
            'name' => fake()->unique()->words(2,true),
            'information' => fake()->realText(30),
            'price_ex_tax' => fake()->numberBetween(1200, 5000),
            'stock_current' => fake()->numberBetween(0, 50),
            'is_selling' => 1,
        ];
    }

    public function withMainImage(): self
    {
        return $this->has(
            ItemImage::factory()->main(),
            'itemImages',
        );
    }

    public function withImages(int $count = 4): self
    {
        return $this->afterCreating(function (Item $item) use ($count) {
            ItemImage::factory()
                ->count($count)
                ->sequence(fn ($sequence) => [
                    'sort_order' => $sequence->index + 1,
                ])
                ->for($item)
                ->create();
        });
    }
}
