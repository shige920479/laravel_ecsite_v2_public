<?php
namespace App\Services\Customer\Review;

use App\Models\Item;
use App\Models\Review;
use App\Models\User;

interface ReviewServiceInterface
{
    public function withRevivews(Item $item, User $user): Item;
    public function buildItemSummary(Item $item): array;
    public function isDuplicated(int $userId, int $itemId): bool;
    public function storeReview(User $user, int $itemId, array $inputs): Review;
    public function updateReview(Review $review, array $data): Review;
}