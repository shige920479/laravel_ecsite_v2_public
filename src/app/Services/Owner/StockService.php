<?php
namespace App\Services\Owner;

use App\Models\Item;
use App\Models\StockHistory;
use App\Support\AppLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;


class StockService
{
  /**
   * Items stock_currentのupdate / stock_historiesへの登録
   */
  public function storeStockAndHistory(array $validated, Item $item): void
  {
      try {
          DB::transaction(function () use ($validated, $item) {
              $item = Item::whereKey($item->id)
                      ->lockForUpdate()
                      ->firstOrFail();
              $quantity = (int)$validated['stock_diff'] * ($validated['up_down'] ? 1 : -1);
              
              $item->increment('stock_current', $quantity);
              $after = $item->fresh()->stock_current;
          
              StockHistory::create([
                  'item_id' => $item->id,
                  'stock_diff' => $quantity,
                  'stock_after' => $after,
                  'reason' => $validated['reason'],
              ]);
          });

      } catch (\Throwable $e) {
          AppLog::error("商品ID:{$item->id} の在庫登録に失敗: " ,$e);
          throw $e;
      }
  }

}