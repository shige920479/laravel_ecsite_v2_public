<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @mixin IdeHelperItemCategory
 */
class ItemCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'sub_category_id', 'name', 'slug'
    ];


    /** relation */
    public function subCategory(): BelongsTo
    {
        return $this->belongsTo(SubCategory::class);
    }
    /** relation */
    public function items(): HasMany
    {
        return $this->hasMany(Item::class);
    }
}
