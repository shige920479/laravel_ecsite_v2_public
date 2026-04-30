<?php
namespace App\Services\Owner\Csv;

use App\Models\Item;
use App\Models\StockHistory;
use Illuminate\Support\Facades\DB;

class StockCsvApplyService
{
    public function apply(array $rowsForProcessing, int $ownerId): int
    {
        $uniqueItemIds = array_unique(array_column($rowsForProcessing, 'item_id'));

        $existingIds = Item::whereIn('id', $uniqueItemIds)
            ->whereHas('shop', fn ($query) =>
                $query->where('owner_id', $ownerId)
            )
            ->pluck('id')
            ->all();

        $missingItemIds = array_diff($uniqueItemIds, $existingIds);

        if (! empty($missingItemIds)) {
            throw new \RuntimeException(
                '存在しない、または操作権限のない商品IDがあります: '
                . implode(',', $missingItemIds)
            );
        }

        DB::transaction(function () use ($rowsForProcessing) {
            
            foreach ($rowsForProcessing as $row) {

                $item = Item::whereKey($row['item_id'])
                    ->lockForUpdate()
                    ->first();

                if (! $item) {
                    throw new \RuntimeException("商品ID {$row['item_id']} が存在しません");
                }

                $newStock = $item->stock_current + $row['quantity'];
                if ($newStock < 0) {
                    throw new \RuntimeException(
                        "CSVの{$row['row']}行目：商品ID {$row['item_id']} の在庫が不足しています"
                    );
                }

                $item->update([
                    'stock_current' => $newStock,
                ]);

                StockHistory::create([
                    'item_id'    => $item->id,
                    'stock_diff' => $row['quantity'],
                    'stock_after' => $newStock,
                    'reason'     => $row['reason'],
                ]);
            }
        });

        return count($rowsForProcessing);
    }
}