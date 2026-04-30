<?php

namespace Database\Seeders;

use App\Models\Item;
use App\Models\OrderItem;
use App\Models\Review;
use App\Models\User;
use App\Support\AppLog;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ReviewSeeder extends Seeder
{
    public function run(): void
    {
        try {
            DB::transaction(function () {
                $this->seedVerifiedReviews();
                $this->seedNotVerifiedReviews();
            });
            
        } catch (\Throwable $e) {
            AppLog::error('ReviewSeeder失敗', $e);
            throw $e;
        }
    }

    /** 
     * 購入者のレビュー登録
     * 購入者の1/2が投稿、評価5,4多め、購入から1週間以内に投稿
     */
    private function seedVerifiedReviews(): void
    {
        $users = User::all();

        $insertData = [];
        
        foreach ($users as $user) {

            $items = OrderItem::query()
                ->select('item_id', 'created_at', 'shipment_id')
                ->with('shipment.order')
                ->whereHas('shipment.order', fn ($query) =>
                    $query->where('user_id', $user->id)
                )
                ->get();

            $uniqueItems = $items->sortBy('created_at')->unique('item_id');
            $postingRate = rand(50, 80);

            foreach ($uniqueItems as $item) {
                if (! fake()->boolean($postingRate)) {
                    continue;
                }
                if ($item->created_at > now()->subDays(6)) {
                    continue;
                }

                $postDate = $item->created_at->copy()->addDays(rand(3, 5));

                $insertData[] = [
                    'user_id' => $user->id,
                    'item_id' => $item->item_id,
                    'order_id' => $item->shipment->order->id,
                    'star' => $this->weightedStar([40, 45, 30, 30, 30]),
                    'title' => fake()->realText(15),
                    'review' => fake()->realText(150),
                    'verified_purchase' => true,
                    'ip_address' => fake()->ipv4(),
                    'created_at' => $postDate,
                    'updated_at' => $postDate,
                ];
            }

            if (count($insertData) >= 500) {
                DB::table('reviews')->insert($insertData);
                $insertData = [];
            }
        }

        if (! empty($insertData)) {
            DB::table('reviews')->insert($insertData);
        }
    }
    
    /** 
     * 未購入者のレビュー登録
     * 購入者レビューに対して15～25％の投稿数
     */
    private function seedNotVerifiedReviews(): void
    {
        $itemIds = Item::pluck('id');
        $users = User::select('id')->get();

        $insertData = [];

        foreach ($itemIds as $itemId) {

            // 既存レビュー数（投稿数ベース）
            $reviewsCount = Review::where('item_id', $itemId)->count();

            if ($reviewsCount === 0) continue;

            $storeCount = (int) ceil($reviewsCount * (rand(15, 25) / 100));

            // 購入者IDを取得
            $purchasedUserIds = OrderItem::query()
                ->where('item_id', $itemId)
                ->with('shipment.order')
                ->whereHas('shipment.order')
                ->get()
                ->pluck('shipment.order.user_id')
                ->unique()
                ->toArray();

            $existingReviewUserIds = Review::query()
                ->where('item_id', $itemId)
                ->pluck('user_id')
                ->toArray();

            $usedUserIds = [];

            for ($i = 0; $i < $storeCount; $i++) {

                // 未購入者から選ぶ
                $reviewer = $this->selectUser(
                    $users,
                    $purchasedUserIds,
                    $existingReviewUserIds,
                    $usedUserIds
                );

                if (! $reviewer) continue;

                $postDate = fake()->dateTimeBetween('-45 days');

                $insertData[] = [
                    'user_id' => $reviewer->id,
                    'item_id' => $itemId,
                    'order_id' => null,
                    'star' => $this->weightedStar([10, 30, 40, 20, 10]),
                    'title' => fake()->realText(15),
                    'review' => fake()->realText(rand(50, 150)),
                    'verified_purchase' => false,
                    'ip_address' => fake()->ipv4(),
                    'created_at' => $postDate,
                    'updated_at' => $postDate,
                ];

                $usedUserIds[] = $reviewer->id;
            }

            if (count($insertData) >= 500) {
                DB::table('reviews')->insert($insertData);
                $insertData = [];
            }
        }

        if (! empty($insertData)) {
            DB::table('reviews')->insert($insertData);
        }
    }

    private function selectUser($users, $purchasedUserIds, $existingReviewUserIds, $usedUserIds)
    {
        $candidates = $users->reject(fn ($user) =>
            in_array($user->id, $purchasedUserIds) ||
            in_array($user->id, $existingReviewUserIds) ||
            in_array($user->id, $usedUserIds)
        );

        return $candidates->isNotEmpty()
            ? $candidates->random()
            : null;
    }

    private function weightedStar(array $weighted): int
    {
        $star = null;
        foreach($weighted as $i => $weight) {
            if (fake()->boolean($weight)) {
                $star = match ($i) {
                    0 => 5,
                    1 => 4,
                    2 => 3,
                    3 => 2,
                    4 => 1,
                    default => 3,
                };
                break;
            }
        }
        return $star ?? 3;
    }
}
