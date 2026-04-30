<?php

namespace App\Policies;

use App\Models\Shop;
use App\Models\Owner;
use Illuminate\Auth\Access\Response;

class ShopPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    // public function viewAny(Owner $owner): bool
    // {
    //     return true;
    // }

    /**
     * Determine whether the user can view the model.
     */
    // public function view(Owner $owner, Shop $shop): bool
    // {
    //     return $owner->id === $shop->owner->id;
    // }

    /**
     * Determine whether the user can create models.
     */
    public function create(Owner $owner): bool
    {
        return ! $owner->shop()->exists();
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(Owner $owner, Shop $shop): bool
    {
        return $owner->id === $shop->owner_id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    // public function delete(Owner $owner, Shop $shop): bool
    // {
    //     return false;
    // }

    /**
     * Determine whether the user can restore the model.
     */
    // public function restore(Owner $owner, Shop $shop): bool
    // {
    //     return false;
    // }

    /**
     * Determine whether the user can permanently delete the model.
     */
    // public function forceDelete(Owner $owner, Shop $shop): bool
    // {
    //     return false;
    // }
}
