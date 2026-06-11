<?php

namespace App\Models;

use App\Helper\Helper;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Review extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id', 'item_id', 'order_id', 'star', 'title', 'review', 'verified_purchase', 'ip_address', 
    ];

    protected $casts = [
        'verified_purchase' => 'boolean',
    ];

    /** レビュー本文のワード検索（複数ワード可） */
    public function scopeSearchByReview(Builder $query, string $searchWord): Builder
    {
        $wordList = Helper::trimSearchWord($searchWord);

        $query->where(function ($query) use ($wordList) {
            foreach ($wordList as $word) {
                $query->orWhereLike('review', "%{$word}%");
            }
        });

        return $query;
    }
    
     /** relation */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
    /** relation */
    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }
    /** relation */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function helpfulUsers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'review_helpfuls')->withTimestamps();
    }
}
