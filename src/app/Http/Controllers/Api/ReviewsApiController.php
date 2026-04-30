<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreReviewRequest;
use App\Http\Requests\UpdateReviewRequest;
use App\Http\Resources\Customer\Review\ReviewResource;
use App\Http\Responses\ApiResponse;
use App\Models\Item;
use App\Models\Review;
use App\Models\User;
use App\Services\Customer\Review\Exception\DuplicateReviewException;
use App\Services\Customer\Review\ReviewServiceInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class ReviewsApiController extends Controller
{
    use ApiResponse;

    public function __construct(private ReviewServiceInterface $reviewService)
    {
    }

    public function index(Request $request, Item $item)
    {
        $itemWithReviews = $this->reviewService->withRevivews($item, $request->user());

        return $this->success(
            data: ReviewResource::collection($itemWithReviews->reviews),
            meta: $this->reviewService->buildItemSummary($itemWithReviews),
        );
    }

    public function store(StoreReviewRequest $request, Item $item)
    {
        $user = $request->user();

        if ($this->reviewService->isDuplicated($user->id, $item->id)) {
            throw new DuplicateReviewException();
        }
        
        $newReview = $this->reviewService->storeReview($user, $item->id, $request->validated());

        return $this->success(
            data: new ReviewResource($newReview),
            message: 'この商品にレビューを投稿しました',
        );
    }

    public function update(UpdateReviewRequest $request, Review $review)
    {
        Gate::authorize('update', $review);

        $updatedReview = $this->reviewService->updateReview($review, $request->validated());

        return $this->success(
            data: new ReviewResource($updatedReview),
            message: 'レビューを変更しました');
    }

    public function toggleHelpful(Request $request, Review $review)
    {
        $user = $request->user();
        if ($review->user_id === $user->id) {
            return $this->error(
                message: '自分のレビューには評価できません',
                status: 422
            );
        }
        $result = $user->helpfulReviews()->toggle($review->id);

        $isHelpful = ! empty($result['attached']);

        return $this->success(
            data: ['is_helpful' => $isHelpful],
        );
    }

    public function destroy(Review $review)
    {
        Gate::authorize('delete', $review);
        $title = $review->title;
        $review->delete();

        return $this->success(message: "投稿（タイトル：{$title}）を削除しました");
    }
}
