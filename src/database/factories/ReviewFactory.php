<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Review>
 */
class ReviewFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $star = fake()->biasedNumberBetween(1, 5, function ($x) {
            return pow($x, 3);
        });

        return [
            'order_id' => null,
            'star' => $star,
            'title' => fake()->realText(15),
            'review' => fake()->realText(150),
            'verified_purchase' => fake()->boolean(70),
            'ip_address' => fake()->ipv4(),
        ];
    }
}
