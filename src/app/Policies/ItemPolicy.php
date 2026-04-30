<?php

namespace App\Policies;

use App\Models\Item;
use App\Models\Owner;
use App\Models\User;
use Illuminate\Auth\Access\Response;
use Illuminate\Support\Facades\Log;

class ItemPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(Owner $owner): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(Owner $owner, Item $item): bool
    {
        return $owner->shop->id === $item->shop_id;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(Owner $owner): bool
    {
        return $owner->shop != null;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(Owner $owner, Item $item): bool
    {
        return $owner->shop->id === $item->shop_id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(Owner $owner, Item $item): bool
    {
        return $owner->shop->id === $item->shop_id;
    }

    /**
     * Determine whether the user can restore the model.
     */
    // public function restore(Owner $owner, Item $item): bool
    // {
    //     return false;
    // }

    /**
     * Determine whether the user can permanently delete the model.
     */
    // public function forceDelete(Owner $owner, Item $item): bool
    // {
    //     return false;
    // }
}
