<?php
namespace App\Services\Customer\Item;

use App\Models\Item;
use App\Services\Customer\Item\DTO\RankingQuery;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class RankingQueryService implements RankingQueryServiceInterface
{
    public function getRankedItems(RankingQuery $rankingQuery): Collection
    {
        $key = "item_ranking:{$rankingQuery->type}:category={$rankingQuery->categorySlug}:period={$rankingQuery->period}";

        return Cache::remember($key, 3600, function () use ($rankingQuery) {
            return $this->buildRanking($rankingQuery);
        });
    }

    private function buildRanking(RankingQuery $rankingQuery): Collection
    {
        return $rankingQuery->apply(
            Item::query()
                ->where('is_selling', true)
                ->with(['mainImage', 'shop'])
                ->withAvgStar()
                ->withViewCounts($rankingQuery->period)
                ->withSales($rankingQuery->period)
                ->withReviewsCount()
        )
        ->take(8)
        ->get();
    }
}