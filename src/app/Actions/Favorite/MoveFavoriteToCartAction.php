<?php
namespace App\Actions\Favorite;

use App\Models\Item;
use App\Models\User;
use App\Services\Customer\Cart\CartServiceInterface;
use App\Services\Customer\Cart\DTO\CreateCartDto;
use App\Services\Customer\Favorite\FavoriteServiceInterface;
use Illuminate\Support\Facades\DB;

class MoveFavoriteToCartAction
{
    public function __construct(
        private CartServiceInterface $cartService,
        private FavoriteServiceInterface $favoriteService,
    ){}

    public function moveToCart(Item $item, User $user)
    {
        DB::transaction(function () use ($item, $user) {
            
            $this->favoriteService->remove($item, $user);

            $this->cartService->store(new CreateCartDto($user, $item->id, 1));
        });
    }

}