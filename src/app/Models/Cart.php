<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Cart extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'item_id', 'quantity',
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
}
