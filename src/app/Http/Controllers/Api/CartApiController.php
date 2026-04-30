<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCartRequest;
use App\Http\Requests\UpdateCartRequest;
use App\Http\Resources\Customer\Cart\CartResource;
use App\Http\Responses\ApiResponse;
use App\Models\Cart;
use App\Services\Customer\Cart\CartServiceInterface;
use App\Services\Customer\Cart\DTO\CreateCartDto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;

class CartApiController extends Controller
{
    use ApiResponse;

    public function __construct(private CartServiceInterface $cartService)
    {}

    public function index(Request $request)
    {
        $user = $request->user();
        $cartItems = $user->carts()
            ->with(['item.mainImage', 'item.shop'])
            ->latest()
            ->get();

        return $this->success(CartResource::collection($cartItems));
    }

    public function store(StoreCartRequest $request)
    {
        $dto = CreateCartDto::fromRequest($request->user(), $request->validated());
        
        $this->cartService->store($dto);

        return $this->success(message: 'この商品をカートに登録しました');
    }

    public function update(UpdateCartRequest $request, Cart $cart)
    {
        $quantity = (int)$request->input('quantity');
        $this->cartService->updateQuantity($cart, $quantity);

        return $this->success(message: "{$cart->item->name}の数量を変更しました");
    }

    public function destroy(Cart $cart)
    {
        Gate::authorize('delete', $cart);
        $itemName = $cart->item->name;
        $cart->delete();

        return $this->success(message: "{$itemName}を削除しました");
    }
}
