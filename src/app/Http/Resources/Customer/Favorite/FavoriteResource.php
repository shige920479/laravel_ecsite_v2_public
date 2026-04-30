<?php

namespace App\Http\Resources\Customer\Favorite;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class FavoriteResource extends JsonResource
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
            'name' => $this->name,
            'price' => $this->priceTaxIn,
            'main_image' => $this->mainImageUrl,
            'shop_name' => $this->shop->name,
            'is_selling' => $this->is_selling,
        ];
    }
}
