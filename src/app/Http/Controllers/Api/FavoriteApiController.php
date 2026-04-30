<?php

namespace App\Http\Controllers\Api;

use App\Actions\Favorite\MoveFavoriteToCartAction;
use App\Http\Controllers\Controller;
use App\Http\Resources\Customer\Favorite\FavoriteResource;
use App\Http\Responses\ApiResponse;
use App\Models\Item;
use App\Services\Customer\Favorite\FavoriteServiceInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;

class FavoriteApiController extends Controller
{
    use ApiResponse;

    public function __construct(private FavoriteServiceInterface $favoriteService)
    {
    }

    public function index(Request $request)
    {
        $favoriteItems = $this->favoriteService->get($request->user());

        return $this->success(FavoriteResource::collection($favoriteItems));
    }

    public function store(Request $request, Item $item)
    {
        $this->favoriteService->add($item, $request->user());

        return $this->success();
    }

    public function destroy(Request $request, Item $item)
    {
        $this->favoriteService->remove($item, $request->user());

        return $this->success();
    }

    public function moveToCart(Request $request, Item $item, MoveFavoriteToCartAction $action)
    {
        $action->moveToCart($item, $request->user());
        
        return $this->success(message: "商品:{$item->name}をカートへ移動しました");
    }
}
