<?php
namespace App\Services\Customer\Review;

use App\Models\Item;
use App\Models\Order;
use App\Models\Review;
use App\Models\User;
use App\Services\Customer\Review\Exception\NotModifiedException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ReviewService implements ReviewServiceInterface
{
    public function withRevivews(Item $item, ?User $user): Item
    {
        $userId = $user?->id;
        
        $item->load([
            'mainImage',
            'shop',
            'reviews' => fn ($query) => 
                $query->with('user')
                    ->withCount('helpfulUsers as helpful_count')
                    ->when($userId, fn ($qy) =>
                        $qy->withExists(['helpfulUsers as is_helpful' => fn ($q) =>
                            $q->where('user_id', $userId)
                        ])
                    )
                    ->orderBy('updated_at', 'desc')
            ])
            ->loadAvg('reviews as avg_star', 'star')
            ->loadCount('reviews as reviews_count')
            ->loadCount([
                'reviews as fiveStar' => fn ($query) => $query->where('star', 5),
                'reviews as fourStar' => fn ($query) => $query->where('star', 4),
                'reviews as threeStar' => fn ($query) => $query->where('star', 3),
                'reviews as twoStar' => fn ($query) => $query->where('star', 2), 
                'reviews as oneStar' => fn ($query) => $query->where('star', 1)
            ]);

        return $item;
    }

    public function buildItemSummary(Item $item): array
    {
        $count = $item->reviews_count;
        return 
            [
                'rating_summary' => [
                    'avgStar' => round($item->avg_star, 1),
                    'count' => $count,
                    'distribution' => [
                        ['star' => 5, 'count' => $item->fiveStar, 'percent' => $this->calcPercent($item->fiveStar, $count) . '%'],
                        ['star' => 4, 'count' => $item->fourStar, 'percent' => $this->calcPercent($item->fourStar, $count) . '%'],
                        ['star' => 3, 'count' => $item->threeStar, 'percent' => $this->calcPercent($item->threeStar, $count) . '%'],
                        ['star' => 2, 'count' => $item->twoStar, 'percent' => $this->calcPercent($item->twoStar, $count) . '%'],
                        ['star' => 1, 'count' => $item->oneStar, 'percent' => $this->calcPercent($item->oneStar, $count) . '%'],
                    ]
                ],
                'item' => [
                    'id' => $item->id,
                    'image' => $item->mainImageUrl,
                    'name' => $item->name,
                    'price' => number_format($item->priceTaxIn),
                    'shop' => [
                        'name' => $item->shop->name,
                    ] 
                ],
                'user' => [
                    'id' => auth('web')->id(),
                ]
            ];
    }

    public function isDuplicated(int $userId, int $itemId): bool
    {
        return Review::query()
            ->where('user_id', $userId)
            ->where('item_id', $itemId)
            ->exists();
    }

    public function storeReview(User $user, int $itemId, array $data): Review
    {
        $order = Order::query()
            ->where('user_id', $user->id)
            ->whereHas('orderItems', fn ($query) =>
                $query->where('item_id', $itemId)
            )
            ->latest('ordered_at')
            ->first();

        $review = Review::create([
            'user_id' => $user->id,
            'item_id' => $itemId,
            'order_id' => $order?->id ?? null,
            'star' => $data['star'],
            'title' => $data['title'] ?? 'タイトル無し',
            'review' => $data['review'],
            'verified_purchase' => $order?->verified_purchase ?? 0,
            'ip_address' => request()->ip(),
        ]);

        return $review->load('user');
    }

    public function updateReview(Review $review, array $data): Review
    {
        $review->fill($data);
        
        if ($review->isClean()) {
            throw new NotModifiedException();
        }
        
        $review->save();

        return $review->load('user')->loadCount('helpfulUsers as helpful_count');
    }

    private function calcPercent(int $star, int $count): float
    {
        return
            $count > 0
             ? round((($star ?? 0) / $count) * 100, 0)
             : 0
             ;
    }
}