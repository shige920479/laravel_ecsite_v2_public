<?php

namespace Database\Seeders;

use App\Models\ItemCategory;
use App\Models\Shop;
use App\Support\AppLog;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ItemSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $shopIds = Shop::pluck('id');
        $itemCategoryIds = ItemCategory::pluck('id');
        $itemCount = 120;

        $insertData = [];
        for ($i = 1; $i <= $itemCount; $i++) {

            $is_selling = fake()->boolean(90);
            $createdAt = $is_selling
                ? fake()->dateTimeBetween('-270 days', '-180 days')
                : fake()->dateTimeBetween('-365 days', '-270 days');

            $insertData[] = [
                'shop_id' => $shopIds->random(),
                'item_category_id' => $itemCategoryIds->random(),
                'name' => "ダミー商品{$i}",
                'information' => "これはダミー商品{$i}の商品情報です。これはダミー商品{$i}の商品情報です。\nこれはダミー商品{$i}の商品情報です。",
                'price_ex_tax' => round(fake()->numberBetween(700, 4000), -2),
                'stock_current' => 0,
                'is_selling' => $is_selling,
                'created_at' => $createdAt,
                'updated_at' => $createdAt
            ];
        }

        try {
            DB::table('items')->insert($insertData);

        } catch (\Throwable $e) {
            AppLog::error('ItemSeederで失敗', $e);
            throw $e;
        } 
    }
}
