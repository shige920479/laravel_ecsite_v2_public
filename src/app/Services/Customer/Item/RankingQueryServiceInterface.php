<?php
namespace App\Services\Customer\Item;

use App\Services\Customer\Item\DTO\RankingQuery;
use Illuminate\Support\Collection;

interface RankingQueryServiceInterface
{
    public function getRankedItems(RankingQuery $rankingQuery): Collection;
}