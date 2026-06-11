<?php

namespace Tests\Feature\Services\Customer;

use App\Exceptions\NotModifiedException;
use App\Models\Item;
use App\Models\Review;
use App\Models\Shop;
use App\Models\User;
use App\Services\Customer\Review\ReviewService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ReviewServiceTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function isDuplicated_同一ユーザーが同一商品に投稿済みの場合trueを返す(): void
    {
       [$userA, $userB, $item, $otherItem, $reviewA, $reviewB,] = $this->createReviews();

        $result = (new ReviewService())->isDuplicated($userA->id, $item->id);

        $this->assertTrue($result);
    }
    #[Test]
    public function isDuplicated_同一ユーザーが同一商品に未投稿の場合falseを返す(): void
    {
       [$userA, $userB, $item, $otherItem, $reviewA, $reviewB,] = $this->createReviews();

        $result = (new ReviewService())->isDuplicated($userB->id, $otherItem->id);

        $this->assertFalse($result);
    }

    #[Test]
    public function updateReview_送信内容に変更がなければ例外を返す(): void
    {
        [$userA, $userB, $item, $otherItem, $reviewA, $reviewB,] = $this->createReviews();
        $data = [
            'star' => $reviewA->star,
            'title' => $reviewA->title,
            'review' => $reviewA->review,
        ];

        $this->expectException(NotModifiedException::class);
        $result = (new ReviewService())->updateReview($reviewA, $data);
    }

    #[Test]
    public function updateReview_送信内容に変更があれば更新する(): void
    {
        [$userA, $userB, $item, $otherItem, $reviewA, $reviewB,] = $this->createReviews();
        $oldTitle = $reviewA->title;
        $data = [
            'star' => $reviewA->star,
            'title' => $reviewA->title . '-update',
            'review' => $reviewA->review,
        ];

        $result = (new ReviewService())->updateReview($reviewA, $data);

        $this->assertSame($oldTitle . '-update', $result->title);
        $this->assertSame($userA->id, $result->user->id);

        $this->assertDatabaseHas('reviews', [
            'id' => $reviewA->id,
            'title' => $oldTitle . '-update'
        ]);
    }

    private function createReviews(): array
    {
        $userA = User::factory()->create();
        $userB = User::factory()->create();
        $shop = Shop::factory()->create();
        $item = Item::factory()->for($shop)->withMainImage()->create();
        $otherItem = Item::factory()->for($shop)->withMainImage()->create();

        $reviewA = Review::factory()->for($userA)->for($item)->create(['star' => 3]);
        $reviewB = Review::factory()->for($userB)->for($item)->create(['star' => 5]);

        return [$userA, $userB, $item, $otherItem, $reviewA, $reviewB,];
    }

    
}
