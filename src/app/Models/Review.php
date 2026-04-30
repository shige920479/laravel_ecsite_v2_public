<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Review extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'item_id', 'order_id', 'star', 'title', 'review', 'verified_purchase', 'ip_address', 
    ];

    protected $casts = [
        'verified_purchase' => 'boolean',
    ];
    
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
