<?php

namespace Database\Seeders;

use App\Models\Item;
use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class StockHistorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $items = Item::query()->where('is_selling', true)->get();

        DB::transaction(function () use ($items) {

            $insertData = [];
            foreach ($items as $item) {
    
                $stockDiff = $this->generateStockData(rand(5, 7));
                $stockAfter = 0;
                $created = Carbon::parse(fake()->dateTimeBetween('-180 days', '-120 days'));
    
                foreach ($stockDiff as $i => $diff) {
                    $reason = $i === 0;
                    $insertData[] = [
                        'item_id' => $item->id,
                        'stock_diff' => $diff,
                        'stock_after' => $stockAfter += $diff,
                        'reason' => $reason ? '初期在庫' : ($diff > 0 ? '入荷' : '出荷'),
                        'created_at' => $created,
                        'updated_at' => $created,
                    ];
                    $created = $created->addDays(rand(10, 15));
                }

                if ($stockAfter > 15 && fake()->boolean(3)) {
                    $insertData[] = [
                        'item_id' => $item->id,
                        'stock_diff' => -$stockAfter,
                        'stock_after' => 0,
                        'reason' => '完売',
                        'created_at' => $created,
                        'updated_at' => $created,
                    ];
                    $stockAfter = 0;
                }
                    
                $item->update(['stock_current' => $stockAfter]);
            }
    
            DB::table('stock_histories')->insert($insertData);
        });
    }

    private function generateStockData(int $count): array
    {
        $minTotal = 20;
        $maxTotal = 50;
        $minPer = -10;
        $maxPer = 15;

        $stocks = [];
        $total = 0;

        for($i = 0; $i < $count - 1; $i++) {
            $stockDiff = rand($minPer, $maxPer);
            if(($total + $stockDiff) < 0) {
                $i--;
                continue;
            }
            $stocks[] = $stockDiff;
            $total += $stockDiff;
        }
        $last = rand(($minTotal - $total), ($maxTotal - $total));
        $stocks[] = $last;

        return $stocks;
    }
}
