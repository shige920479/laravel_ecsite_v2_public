<?php

namespace App\Http\Resources\Customer\Item;

use App\Http\Resources\Customer\Item\ItemResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class ItemCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @return array<int|string, mixed>
     */
    public function toArray(Request $request)
    {
        return ItemResource::collection($this->collection);
    }

    public function pagination(): array
    {
        return [
            'current_page' => $this->currentPage(),
            'last_page' => $this->lastPage(),
            'total' => $this->total(),
        ];
    }
}
