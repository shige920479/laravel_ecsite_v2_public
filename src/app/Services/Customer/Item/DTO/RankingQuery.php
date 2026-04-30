<?php
namespace App\Services\Customer\Item\DTO;

use App\Models\Category;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Log;

class RankingQuery
{
    public function __construct(
        public readonly ?string $type,
        public readonly ?int $period,
        public readonly ?string $categorySlug,
    )
    {
    }

    public static function fromRequest(array $data): RankingQuery
    {
        $types = array_keys(config('constants.ranking.type'));
        $periods = array_values(config('constants.ranking.period'));

        $rankType = in_array($data['rank_type'] ?? null, $types)
            ? $data['rank_type']
            : 'views';

        $period = in_array($data['period'] ?? null, $periods)
            ? $data['period']
            : config('constants.ranking.period.weekly');

        return new self(
            type: $rankType,
            period: $period,
            categorySlug: $data['slug'] ?? null,
        );
    }

    public function apply(Builder $query)
    {
        $typeConfig = config('constants.ranking.type');
        $type = $typeConfig[$this->type] ?? $typeConfig['views'];

        return $query
            ->when($this->categorySlug, fn ($query) =>
                $query->filterCategorySlug($this->categorySlug)
            )
            ->when($this->type, fn ($query) =>
                $query->orderBy($type['column'], $type['direction'])
            );
            
    }

}