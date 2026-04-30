<?php

namespace App\Http\Resources\Customer\Order;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CheckoutConfirmResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'item_id' => $this->item->id,
            'item_name' => $this->item->name,
            'quantity' => $this->quantity,
            'subtotal' => $this->item->priceTaxIn * (int)$this->quantity,
        ];
    }
}
