<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ItemApiIndexRequest;
use App\Http\Resources\Customer\Item\ItemCollection;
use App\Http\Responses\ApiResponse;
use App\Models\Category;
use App\Models\ItemCategory;
use App\Models\SubCategory;
use App\Services\Customer\Item\DTO\ItemQuery;
use App\Services\Customer\Item\ItemQueryServiceInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ItemApiController extends Controller
{
    use ApiResponse;

    public function __construct(private ItemQueryServiceInterface $itemService)
    {}

    public function index(
        ItemApiIndexRequest $request,
        ?Category $category = null,
        ?SubCategory $subCategory = null,
        ?ItemCategory $itemCategory = null
    )
    {
        $itemQuery = ItemQuery::fromRequest($request->validated(), $category, $subCategory, $itemCategory);
        $items = $this->itemService->searchItems($itemQuery);
        $collection = new ItemCollection($items);

        return $this->success(
            data:$collection,
            meta:$collection->pagination()
        );
    }
}
