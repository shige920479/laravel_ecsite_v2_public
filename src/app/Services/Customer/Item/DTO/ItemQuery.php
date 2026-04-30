<?php
namespace App\Services\Customer\Item\DTO;

use App\Models\Category;
use App\Models\ItemCategory;
use App\Models\SubCategory;
use Illuminate\Database\Eloquent\Builder;

class ItemQuery
{
    public function __construct(
        public readonly ?string $itemSearch,
        public readonly ?string $itemSort,
        public readonly ?string $category,
        public readonly ?string $subCategory,
        public readonly ?string $itemCategory,
        public readonly ?int $perPage,
    )
    {
    }

    public static function fromRequest(
        array $data,
        ?Category $category,
        ?SubCategory $subCategory,
        ?ItemCategory $itemCategory
    ): self
    {
        return new self(
            itemSearch: $data['item_search'] ?? null,
            itemSort: $data['item_sort'] ?? null,
            category: $category?->slug,
            subCategory: $subCategory?->slug,
            itemCategory: $itemCategory?->slug,
            perPage: (int)($data['per_page'] ?? 8)
        );
    }

    public function apply(Builder $query): Builder
    {
        return $query
            ->when($this->itemSearch,
                fn ($q) => $q->searchByNameOrShop($this->itemSearch))
            ->when($this->category,
                fn ($q) => $q->filterCategorySlug($this->category))
            ->when($this->subCategory,
                fn ($q) => $q->filterSubCategorySlug($this->subCategory))
            ->when($this->itemCategory,
                fn ($q) => $q->filterItemCategorySlug($this->itemCategory))
            ->when($this->itemSort,
                fn ($q) => $q->sortItemList($this->itemSort));
    }
}