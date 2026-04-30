<?php

namespace App\Http\Resources\Customer\Review;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class MyReviewResource extends JsonResource
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
            'item_id' => $this->item_id,
            'star' => $this->star,
            'title' => $this->title,
            'review' => $this->review,
            'helpful_count' => $this->helpful_count,
            'verified_purchase' => $this->verified_purchase,
            'updated_at' => $this->updated_at->format('Y-m-d h:i'),
            'mainImage' => $this->item->mainImageUrl,
            'item_name' => $this->item->name,
            'shop_name' => $this->item->shop->name ?? null,
        ];
    }
}
