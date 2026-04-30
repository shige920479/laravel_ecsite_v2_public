<?php
namespace App\Services\Customer\Item;

use App\Models\Item;
use App\Services\Customer\Item\DTO\ItemQuery;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class ItemQueryService implements ItemQueryServiceInterface
{
    public function searchItems(ItemQuery $itemQuery): LengthAwarePaginator
    {
        return $itemQuery->apply(
                Item::query()
                    ->where('is_selling', true)
                    ->with(['mainImage', 'shop'])
                    ->withAvgStar()
                    ->withReviewsCount()
                )
                ->paginate($itemQuery->perPage)
                ->withQueryString();
    }
}