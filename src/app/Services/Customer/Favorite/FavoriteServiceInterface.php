<?php
namespace App\Services\Customer\Favorite;

use App\Models\Item;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

interface FavoriteServiceInterface
{
    public function get(User $user): Collection;
    public function add(Item $item, User $user): void;
    public function remove(Item $item, User $user): void;
}