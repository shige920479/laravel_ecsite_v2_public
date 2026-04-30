<?php

namespace Database\Factories;

use App\Enums\CheckoutStatus;
use App\Models\CheckoutItem;
use App\Models\CheckoutRequest;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\CheckoutRequest>
 */
class CheckoutRequestFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'total_ex_tax' => 0,
            'total_tax' => 0,
            'total_in_tax' => 0,
            'status' => CheckoutStatus::PENDING,
            'expires_at' => now()->addMinutes(60),
        ];
    }
}
