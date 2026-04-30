<?php

namespace App\Http\Resources\Customer\Item;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ItemShowResource extends JsonResource
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
            'shop_name' => $this->shop->name,
            'name' => $this->name,
            'information' => $this->information,
            'price' => number_format($this->priceTaxIn),
            'avg_star' => round($this->avg_star ?? 0, 1),
            'reviews_count' => $this->reviews_count ?? 0,
            'stock_current' => $this->stock_current,
            'is_selling' => $this->is_selling,
            'category' => $this->itemCategory->subCategory->category->name,
            'sub_category' => $this->itemCategory->subCategory->name,
            'item_category' => $this->itemCategory->name,
            'images' => $this->imageUrls,
        ];
    }
}
