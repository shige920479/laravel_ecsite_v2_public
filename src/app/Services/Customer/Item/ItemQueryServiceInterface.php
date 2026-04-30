<?php
namespace App\Services\Customer\Item;

use App\Services\Customer\Item\DTO\ItemQuery;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface ItemQueryServiceInterface
{
    public function searchItems(ItemQuery $query): LengthAwarePaginator;
}