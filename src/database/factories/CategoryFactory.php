<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\ItemCategory;
use App\Models\SubCategory;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Category>
 */
class CategoryFactory extends Factory
{
    public function definition(): array
    {
        $name = fake()->unique()->word(2, true);

        return [
            'name' => $name,
            'slug' => Str::slug($name)
        ];
    }

    public function withTree(int $subCount = 1, int $itemCatPerSub = 2): self
    {
        return $this->has(
            SubCategory::factory()
                ->count($subCount)
                ->has(ItemCategory::factory()->count($itemCatPerSub), 'itemCategories')
            , 'subCategories'
        );

    }


}
