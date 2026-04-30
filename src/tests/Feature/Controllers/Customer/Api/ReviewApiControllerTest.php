<?php

namespace Tests\Feature\Controllers\Customer\Api;

use App\Models\Item;
use App\Models\Review;
use App\Models\Shop;
use App\Models\User;
use App\Services\Customer\Review\Exception\DuplicateReviewException;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Testing\Fluent\AssertableJson;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ReviewApiControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Carbon::setTestNow(Carbon::parse('2026-01-01 10:00:00'));
    }

    #[Test]
    public function index_商品のレビュー情報と商品基本情報を取得しレスポンスする(): void
    {
        [$userA, $userB, $item, $otherItem, $reviewA, $reviewB, $otherReview] = $this->createReviews();

        $response = $this->getJson("/api/item/{$item->id}/reviews");

        $response->assertJson(fn (AssertableJson $json) =>
            $json->where('success', true)
                ->has('data', 2)
                ->whereType('data', 'array')
                ->has('data.0', fn($jn) =>
                    $jn->where('id', $reviewB->id)
                        ->etc()
                )
                ->has('data.1', fn($jn) =>
                    $jn->where('id', $reviewA->id)
                        ->etc()
                )
                ->has('meta', fn($jn) =>
                    $jn->where('rating_summary.avgStar', 4)
                        ->where('rating_summary.count', 2)
                        ->where("rating_summary.distribution.0.count", 1)
                        ->where("rating_summary.distribution.2.count", 1)
                        ->where('item.id', $item->id)
                        ->where('user.id', null)
                        ->etc()
                )->has('meta.rating_summary.distribution', 5)
                ->etc()
        );

        // ログイン時
        $response = $this->actingAs($userA, 'web')->getJson("/api/item/{$item->id}/reviews");

        $response->assertJsonPath('meta.user.id', $userA->id);
        
    }
    #[Test]
    public function store_新規レビューを登録する(): void
    {
        [$userA, $userB, $item, $otherItem, $reviewA, $reviewB, $otherReview] = $this->createReviews();

        $input = ['star' => 5, 'title' => 'test-title', 'review' => 'test-review'];
        $response = $this->actingAs($userB, 'web')
            ->postJson("/api/item/{$otherItem->id}/review", $input);

        $response->assertJson(fn (AssertableJson $json) =>
            $json->where('success', true)
                ->has('message')
                ->has('data', fn ($jn) =>
                    $jn->where('star', 5)
                        ->where('title', 'test-title')
                        ->etc()
                )
            ->etc()
        );

        $this->assertDatabaseHas('reviews', [
            'star' => 5,
            'title' => 'test-title',
            'review' => 'test-review'
        ]);
    }
    #[Test]
    public function store_同じ商品には複数投稿できずエラーレスポンスを返す(): void
    {
        [$userA, $userB, $item, $otherItem, $reviewA, $reviewB, $otherReview] = $this->createReviews();

        $input = ['star' => 5, 'title' => 'test-title', 'review' => 'test-review'];

        $response = $this->actingAs($userA, 'web')
            ->postJson("/api/item/{$item->id}/review", $input);
        
        $response->assertJson(fn (AssertableJson $json) =>
            $json->where('success', false)
                ->where('code', 'REVIEW_DUPLICATE_ITEM')
                ->where('message', 'この商品へは既にレビューを投稿済です')
                ->etc()
        );
    }
    #[Test]
    public function update_投稿を更新する(): void
    {
        [$userA, $userB, $item, $otherItem, $reviewA, $reviewB, $otherReview] = $this->createReviews();

        $input = ['star' => $reviewA->star, 'title' => 'test-title', 'review' => 'test-review'];

        $response = $this->actingAs($userA, 'web')
            ->patchJson("/api/reviews/{$reviewA->id}", $input);
        
        $response->assertJson(fn (AssertableJson $json) =>
            $json->where('success', true)
                ->has('data', fn ($jn) =>
                    $jn->where('id', $reviewA->id)
                        ->where('title', 'test-title')
                        ->where('review', 'test-review')
                        ->etc()
                )
                ->etc()
        );

        $this->assertDatabaseHas('reviews', [
            'id' => $reviewA->id,
            'title' => 'test-title',
            'review' => 'test-review'
        ]);
    }

    #[Test]
    public function update_他人の投稿は編集できない(): void
    {
        [$userA, $userB, $item, $otherItem, $reviewA, $reviewB, $otherReview] = $this->createReviews();

        $input = ['star' => 3, 'title' => 'test-title', 'review' => 'test-review'];

        $response = $this->actingAs($userA, 'web')->patchJson("/api/reviews/{$reviewB->id}", $input);

        $response->assertForbidden();
    }

    #[Test]
    public function destroy_投稿を削除する(): void
    {
        [$userA, $userB, $item, $otherItem, $reviewA, $reviewB, $otherReview] = $this->createReviews();

        $response = $this->actingAs($userA, 'web')->deleteJson("/api/reviews/{$reviewA->id}");

        $response->assertJson(fn (AssertableJson $json) =>
            $json->where('success', true)
                ->where('message', "投稿（タイトル：{$reviewA->title}）を削除しました")
                ->etc()
        );

        $this->assertDatabaseMissing('reviews', [
            'id' => $reviewA->id,
        ]);
    }

    #[Test]
    public function toggleHelpful_役に立ったを登録・削除する(): void
    {
        [$userA, $userB, $item, $otherItem, $reviewA, $reviewB, $otherReview] = $this->createReviews();

        $response = $this->actingAs($userA, 'web')->postJson("/api/reviews/{$reviewB->id}/toggle");

        $response->assertStatus(200)
            ->assertJsonPath('data.is_helpful', true);

        $response = $this->actingAs($userA, 'web')->postJson("/api/reviews/{$reviewB->id}/toggle");

        $response->assertStatus(200)
            ->assertJsonPath('data.is_helpful', false);
    }
    #[Test]
    public function toggleHelpful_役に立ったを自分の投稿には登録できない(): void
    {
        [$userA, $userB, $item, $otherItem, $reviewA, $reviewB, $otherReview] = $this->createReviews();

        $response = $this->actingAs($userA, 'web')->postJson("/api/reviews/{$reviewA->id}/toggle");

        $response->assertStatus(422);
        $response->assertJson(fn (AssertableJson $json) =>
            $json->where('success', false)
                ->where('message', '自分のレビューには評価できません')
                ->etc()
        );
    }

    #[Test]
    public function destroy_他人の投稿は削除できない(): void
    {
        [$userA, $userB, $item, $otherItem, $reviewA, $reviewB, $otherReview] = $this->createReviews();

        $input = ['star' => 3, 'title' => 'test-title', 'review' => 'test-review'];

        $response = $this->actingAs($userA, 'web')->deleteJson("/api/reviews/{$reviewB->id}");

        $response->assertForbidden();
    }

    private function createReviews(): array
    {
        $userA = User::factory()->create();
        $userB = User::factory()->create();
        $shop = Shop::factory()->create();
        $item = Item::factory()->for($shop)->withMainImage()->create();
        $otherItem = Item::factory()->for($shop)->withMainImage()->create();

        $reviewA = Review::factory()->for($userA)->for($item)->create([
            'star' => 3, 'updated_at' => now()->subDays(10)
        ]);
        $reviewB = Review::factory()->for($userB)->for($item)->create([
            'star' => 5,  'updated_at' => now()->subDays(5)
        ]);
        $otherReview = Review::factory()->for($userA)->for($otherItem)->create(['star' => 5]);

        return [$userA, $userB, $item, $otherItem, $reviewA, $reviewB, $otherReview];
    }
}