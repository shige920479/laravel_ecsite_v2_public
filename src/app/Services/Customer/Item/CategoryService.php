<?php
namespace App\Services\Customer\Item;

use App\Models\Category;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class CategoryService implements CategoryServiceInterface
{
    private const CACHE_KEY = 'category_tree';
    private const CACHE_TTl = 60 * 60 * 3;

    public function getTree(): Collection
    {
        return Cache::remember(self::CACHE_KEY, self::CACHE_TTl, fn () => $this->buildTree());
    }

    public function clearCache(): void
    {
        Cache::forget(self::CACHE_KEY);
    }

    private function buildTree(): Collection
    {
        $categories = Category::with('subCategories.itemCategories')->get();
        
        return
        $categories->map(function ($parent) {
            return [
                'id'       => $parent->id,
                'name'     => $parent->name,
                'slug'     => $parent->slug,
                'children' => $parent->subCategories->map(function ($sub) {
                    return [
                        'id'       => $sub->id,
                        'name'     => $sub->name,
                        'slug'     => $sub->slug,
                        'children' => $sub->itemCategories->map(function ($itemCat) {
                            return [
                                'id'       => $itemCat->id,
                                'name'     => $itemCat->name,
                                'slug'     => $itemCat->slug,
                            ];
                        })
                    ];
                })
            ];
        });
    }
}