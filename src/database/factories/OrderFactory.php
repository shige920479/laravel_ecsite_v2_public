<?php

namespace Database\Factories;

use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Models\Item;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Order>
 */
class OrderFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $orderedAt = fake()->dateTimeBetween('-90 days', 'now');

        return [
            'user_id' => User::factory(),
            'total_ex_tax' => 0,
            'total_tax' => 0,
            'total_in_tax' => 0,
            'payment_method' => PaymentMethod::CARD,
            'ordered_at' => $orderedAt,
            'payment_status' => OrderStatus::PAID,
        ];
    }
}
