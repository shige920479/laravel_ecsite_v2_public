<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\MyReviewIndexRequest;
use App\Http\Resources\Customer\Review\MyReviewCollection;
use App\Http\Responses\ApiResponse;
use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MyReviewApiController extends Controller
{
    use ApiResponse;

    public function index(MyReviewIndexRequest $request)
    {
        $user = Auth::user();
        $sort = $request->validated('review_sort') ?? 'desc';

        $reviews = Review::query()
            ->where('user_id', $user->id)
            ->with(['item.mainImage', 'item.shop', 'order'])
            ->withCount('helpfulUsers as helpful_count')
            ->orderBy('updated_at', $sort)
            ->paginate($request->validated('per_page'))
            ->withQueryString();

        $collection = new MyReviewCollection($reviews);

        return $this->success(
            data: $collection,
            meta: $collection->pagination(),
        );
    }
}
