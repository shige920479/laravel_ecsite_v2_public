<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @mixin IdeHelperItemImage
 */
class ItemImage extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'item_id', 'filename', 'sort_order'
    ];

    /** relation */
    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }
}
