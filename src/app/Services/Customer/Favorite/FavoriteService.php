<?php
namespace App\Services\Customer\Favorite;

use App\Exceptions\SalesSuspendedException;
use App\Models\Cart;
use App\Models\Item;
use App\Models\User;
use App\Services\Customer\Cart\Exceptions\DuplicateItemException;
use App\Services\Customer\Favorite\Exceptions\FavoriteNotFoundException;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class FavoriteService implements FavoriteServiceInterface
{
    public function get(User $user): Collection
    {
        return $user->favoriteItems()
            ->with(['mainImage', 'shop'])
            ->latest()
            ->get();
    }

    public function add(Item $item, User $user): void
    {
        if (! $item->is_selling) {
           throw new SalesSuspendedException();
        }

        $user->favoriteItems()->syncWithoutDetaching([$item->id]);
    }

    public function remove(Item $item, User $user): void
    {
        $user->favoriteItems()->detach($item->id);
    }
}