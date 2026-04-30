<?php

namespace App\Http\Resources\Customer\Cart;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class CartResource extends JsonResource
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
            'item_price' => $this->item->priceTaxIn,
            'quantity' => $this->quantity,
            'main_image' => $this->item->mainImageUrl,
            'shop_name' => $this->item->shop->name,
            'is_selling' => $this->item->is_selling,
        ];
    }
}
