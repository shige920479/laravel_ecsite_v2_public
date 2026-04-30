<?php

namespace Database\Factories;

use App\Models\Item;
use Closure;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ItemImage>
 */
class ItemImageFactory extends Factory
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
            'filename' => fake()->unique()->filePath(),
            'sort_order' => 1,
        ];
    }

    public function main(): self
    {
        return $this->state(fn () => ['sort_order' => 1]);
    }

    public function order(int $n): self
    {
        return $this->state(fn () => ['sort_order' => $n]);
    }
}
