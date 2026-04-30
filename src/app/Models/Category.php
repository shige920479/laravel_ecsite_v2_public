<?php

namespace App\Models;

use Attribute;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @mixin IdeHelperCategory
 */
class Category extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'slug'
    ];

    public function scopeWithTree(Builder $query): Builder
    {
        return $query->with(['subCategories.itemCategories']);
    }

    /** relation */
    public function subCategories(): HasMany
    {
        return $this->hasMany(SubCategory::class);
    }
}
