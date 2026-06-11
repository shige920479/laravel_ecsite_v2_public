<?php
namespace App\Services\Admin;

use App\Models\Review;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class ReviewService implements ReviewServiceInterface
{
    public function getReviews(array $param): LengthAwarePaginator
    {
        $searchWord = $param['search_word'] ?? null;

        return Review::query()
            ->with(['user', 'item'])
            ->when($searchWord, fn ($query) =>
                $query->where(function ($query) use ($searchWord) {
                    $query->searchByReview($searchWord)
                        ->orWhereHas('user', fn($q) =>
                            $q->searchByName($searchWord)
                        )
                        ->orWhereHas('item', fn($q) =>
                            $q->searchByNameKeyword($searchWord)
                        );
                })
            )
            ->when($param['rating'] ?? null, fn ($query) =>
                $query->where('star', $param['rating'])
            )
            ->paginate(10)
            ->withQueryString()
            ;
    }
}