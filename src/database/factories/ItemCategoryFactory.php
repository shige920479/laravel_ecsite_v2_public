<?php

namespace Database\Factories;

use App\Models\SubCategory;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ItemCategory>
 */
class ItemCategoryFactory extends Factory
{
    public function definition(): array
    {
        $name = fake()->unique()->word(2, true);

        return [
            'sub_category_id' => SubCategory::factory(),
            'name' => $name,
            'slug' => Str::slug($name)
        ];
    }
}
