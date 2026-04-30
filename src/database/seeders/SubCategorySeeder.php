<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SubCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('sub_categories')->insert([
            [
                'category_id' => 1,
                'name' => 'デザイン',
                'slug' => 'design',
            ],
            [
                'category_id' => 1,
                'name' => '素材',
                'slug' => 'material',
            ],
            [
                'category_id' => 1,
                'name' => 'サイズ',
                'slug' => 'size',
            ],
            [
                'category_id' => 2,
                'name' => '用途',
                'slug' => 'use',
            ],
            [
                'category_id' => 2,
                'name' => '素材',
                'slug' => 'material',
            ],
            [
                'category_id' => 2,
                'name' => 'カラー',
                'slug' => 'color',
            ],
        ]);
    }
}
