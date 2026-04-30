<?php

namespace App\Http\Resources\Customer\Review;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;

class ReviewResource extends JsonResource
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
            'userId' => $this->user_id,
            'star' => $this->star,
            'title' => $this->title,
            'nickname' => $this->user->nickname ?? '名無し',
            'review' => $this->review,
            'updated_at' => $this->updated_at->format('Y-m-d H:i') ,
            'helpful_count' => $this->helpful_count ?? 0,
            'is_helpful' => $this->is_helpful ?? false,
            'verified_purchase' => $this->verified_purchase,
        ];
    }

}
