<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @mixin IdeHelperSubCategory
 */
class SubCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_id', 'name', 'slug'
    ];



    
    /** relation */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }
    /** relation */
    public function itemCategories(): HasMany
    {
        return $this->hasMany(ItemCategory::class);
    }
}
