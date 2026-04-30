<?php

namespace Database\Factories;

use App\Models\Owner;
use App\Models\Shop;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Shop>
 */
class ShopFactory extends Factory
{
    public function definition(): array
    {
        return [
            'owner_id' => Owner::factory(),
            'name' => fake()->unique()->company(),
            'information' => fake()->sentence(),
            'filename' => fake()->unique()->filePath(),
            'is_selling' => true,
        ];
    }
}
