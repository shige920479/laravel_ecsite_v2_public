<?php

namespace Database\Factories;

use App\Enums\ShippingStatus;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Shipment>
 */
class ShipmentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'shipping_status' => ShippingStatus::UNSHIPPED
        ];
    }

    public function forUser(User $user): self
    {
        return $this->state([
            'shipping_name' => $user->name,
            'shipping_postcode' => $user->postcode,
            'shipping_address' => $user->address,
            'shipping_phone' => $user->phone,
        ]);
    }
}
