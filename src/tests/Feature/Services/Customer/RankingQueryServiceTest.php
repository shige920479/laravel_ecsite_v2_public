<?php

namespace Tests\Feature\Services\Customer;

use App\Models\Item;
use App\Services\Customer\Item\DTO\RankingQuery;
use App\Services\Customer\Item\RankingQueryService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Cache;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class RankingQueryServiceTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function getRankedItems_ランキングは最大8件取得できる(): void
    {
        Cache::flush();

        $items = Item::factory()->withMainImage()->count(10)->create();

        $query = RankingQuery::fromRequest([]);

        $res = (new RankingQueryService())->getRankedItems($query);

        $this->assertCount(8, $res);
    }
}
