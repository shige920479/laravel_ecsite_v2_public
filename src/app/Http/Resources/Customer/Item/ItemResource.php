<?php

namespace App\Http\Resources\Customer\Item;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class ItemResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'shop_name' => $this->shop->name,
            'name' => $this->name,
            'price' => $this->price_ex_tax,
            'main_image' => $this->mainImageUrl,
            'avg_star' => round($this->avg_star ?? 0, 1),
            'reviews_count' => $this->reviews_count ?? 0,
            'is_selling' => $this->is_selling
        ];
    }
}
