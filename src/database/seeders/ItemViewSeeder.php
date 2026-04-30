<?php

namespace Database\Seeders;

use App\Models\Item;
use App\Models\User;
use DateTime;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ItemViewSeeder extends Seeder
{
    public function run(): void
    {
        $userIds = User::query()->pluck('id');
        $userCount = $userIds->count();

        foreach (Item::withAvgStar()->cursor() as $item) {

            if (fake()->boolean(5)) {
                continue;
            }

            $avgStar = $item->avg_star ?? 0;

            $viewCount = match (true) {
                $avgStar >= 4 => rand(1500, 3000),
                $avgStar >= 3  => rand(300, 1000),
                default => rand(50, 200)
            };

            $insertData = [];
            for ($i = 0; $i < $viewCount; $i++) {
                
                $viewDate = $this->viewDate();
                $insertData[] = [
                    'item_id' => $item->id,
                    'user_id' => fake()->boolean(40) ? $userIds[random_int(0, $userCount - 1)] : null,
                    'ip' => fake()->ipv4(),
                    'created_at' => $viewDate,
                    'updated_at' => $viewDate,
                ];

                if (count($insertData) >= 1000) {
                    DB::table('item_views')->insert($insertData);
                    $insertData = [];
                }
            
            }
            
            if (! empty($insertData)) {
                DB::table('item_views')->insert($insertData);
            }
        }
    }

    private function viewDate(): DateTime
    {
        $rand = rand(1, 100);

        return match (true) {
            $rand <= 50 => fake()->dateTimeBetween('-30 days', now()),
            $rand <= 85 => fake()->dateTimeBetween('-60 days', now()),
            default => fake()->dateTimeBetween('-180 days', now())
        };
    }
}
