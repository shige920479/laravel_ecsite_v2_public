<?php
namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Http\Requests\ItemIndexRequest;
use App\Http\Requests\RankingIndexRequest;
use App\Http\Resources\Customer\Item\ItemShowResource;
use App\Models\Category;
use App\Models\Item;
use App\Models\ItemCategory;
use App\Models\SubCategory;
use App\Services\Customer\Item\CategoryServiceInterface;
use App\Services\Customer\Item\DTO\RankingQuery;
use App\Services\Customer\Item\RankingQueryServiceInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class GuestController extends Controller
{
    public function __construct(
        private CategoryServiceInterface $categoryService,
        private RankingQueryServiceInterface $rankingQueryService,
    ){}

    public function index(
        ItemIndexRequest $request,
        ?Category $category = null,
        ?SubCategory $subCategory = null,
        ?ItemCategory $itemCategory = null,
    )
    {
        $viewData = [
            ...$request->validated(),
            'category' => $category?->slug,
            'sub_category' => $subCategory?->slug,
            'item_category' => $itemCategory?->slug,
        ];
        
        $categoryTree = $this->categoryService->getTree();

        return view('user.items.index', [
            'viewData' => $viewData,
            'categories' => $categoryTree,
        ]);
    }

    public function show(Request $request, Item $item)
    {
        $item = Item::where('id', $item->id)
            ->with(['shop', 'itemImages', 'itemCategory.subCategory.category'])
            ->withAvgStar()
            ->withReviewsCount()
            ->first();

        $isFavorite = Auth::guard('web')->check()
            ? Auth::user()->favoriteItems()->where('item_id', $item->id)->exists()
            : false;

        return view('user.items.show', [
            'item' => ItemShowResource::make($item)->resolve(),
            'isFavorite' => $isFavorite,
            'prevUrl' => $request->input('from') ?? '/',
        ]);
    }

    public function ranking(RankingIndexRequest $request)
    {
        $items = $this->rankingQueryService->getRankedItems(RankingQuery::fromRequest($request->validated()));
        $categories = Category::all();
        
        return view('user.items.ranking', [
            'items' => $items,
            'categories' => $categories
        ]);
    }
}
