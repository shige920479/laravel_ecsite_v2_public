<?php

namespace Tests\Unit\Services\Customer\DTO;

use App\Services\Customer\Item\DTO\RankingQuery;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tests\TestCase as TestsTestCase;

class RankingQueryTest extends TestsTestCase
{
    
    #[Test]
    public function fromRequest_初回ロード時用に変換する(): void
    {
        $dto = RankingQuery::fromRequest([
            'rank_type' => null,
            'period' => null,
            'slug' => null,
        ]);

        $this->assertSame('views', $dto->type);
        $this->assertSame(config('constants.ranking.period.weekly'), $dto->period);
        $this->assertSame(null, $dto->categorySlug);
    }

    #[Test]
    public function fromRequest_リクエストに応じて変換する(): void
    {
        $dto = RankingQuery::fromRequest([
            'rank_type' => 'sales',
            'period' => config('constants.ranking.period.monthly'),
            'slug' => 'mug',
        ]);

        $this->assertSame('sales', $dto->type);
        $this->assertSame(config('constants.ranking.period.monthly'), $dto->period);
        $this->assertSame('mug', $dto->categorySlug);

    }

    #[Test]
    public function fromRequest_不正な値が来てもデフォルトを表示(): void
    {
        $dto = RankingQuery::fromRequest([
            'rank_type' => 'invalid',
            'period' => 999,
        ]);

        $this->assertSame('views', $dto->type);
        $this->assertSame(config('constants.ranking.period.weekly'), $dto->period);
    }
}
