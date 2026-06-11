<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\AdminReviewIndexQueryRequest;
use App\Models\Review;
use App\Services\Admin\ReviewServiceInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class ReviewController extends Controller
{
    public function __construct(private ReviewServiceInterface $reviewService)
    {
    }

    public function index(AdminReviewIndexQueryRequest $request)
    {
        $requestQuery = $request->validated();
        $reviews = $this->reviewService->getReviews($requestQuery);

        return view('admin.reviews.index', [
            'reviews' => $reviews,
            'query' => $requestQuery]
        );
    }

    public function show(Review $review)
    {
        $review->load(['user', 'item']);

        return view('admin.reviews.show', ['review' => $review]);
    }

    public function destroy(Review $review)
    {
        Gate::authorize('review.delete');
        $review->delete();

        return to_route('admin.reviews')->with([
            'status' => 'info',
            'message' => 'レビューを1件削除しました'
        ]);
    }
}
