<?php

namespace Tests\Feature\Controllers\Customer\Api;

use App\Models\Item;
use App\Models\Review;
use App\Models\Shop;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Testing\Fluent\AssertableJson;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class MyReviewApiControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Carbon::setTestNow(Carbon::parse('2026-01-01 10:00:00'));
    }

    #[Test]
    public function index_自分の投稿データを取得しレスポンスする(): void
    {
        [$user, $itemA, $itemB, $itemC, $reviewA, $reviewB, $reviewC] = $this->createReviews();

        $query = '?review_sort=desc&per_page=2&page=1';

        $response = $this->actingAs($user, 'web')->getJson("/api/mypage/reviews{$query}");

        $response->assertStatus(200)
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('data.0.id', $reviewB->id)
            ->assertJsonPath('data.1.id', $reviewA->id)
            ->assertJsonStructure([
                'success',
                'data' => [
                    '*' => [
                        'id',
                        'item_id',
                        'star',
                        'title',
                        'review',
                        'helpful_count',
                        'verified_purchase',
                        'updated_at',
                        'mainImage',
                        'item_name',
                        'shop_name',
                    ],
                ],
                'meta' => [
                    'current_page',
                    'last_page',
                    'total'
                ]
            ]);
    }

    #[Test]
    public function index_検索結果が0件でも正常なjsonを返す(): void
    {
        $user = User::factory()->create();
        $query = '?review_sort=desc&per_page=2&page=1';
        $response = $this->actingAs($user, 'web')->getJson("/api/mypage/reviews{$query}");

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonCount(0, 'data')
            ->assertJsonStructure([
                'meta' => [
                    'current_page',
                    'last_page',
                    'total'
                ]
            ])
            ;
    }

    private function createReviews(): array
    {
        $user = User::factory()->create();
        $shop = Shop::factory()->create();
        $itemA = Item::factory()->for($shop)->withMainImage()->create();
        $itemB = Item::factory()->for($shop)->withMainImage()->create();
        $itemC = Item::factory()->for($shop)->withMainImage()->create();

        $reviewA = Review::factory()->for($user)->for($itemA)->create(['updated_at' => now()->subDays(10)]);
        $reviewB = Review::factory()->for($user)->for($itemB)->create(['updated_at' => now()->subDays(5)]);
        $reviewC = Review::factory()->for($user)->for($itemC)->create(['updated_at' => now()->subDays(20)]);

        return [$user, $itemA, $itemB, $itemC, $reviewA, $reviewB, $reviewC];
    }





}
