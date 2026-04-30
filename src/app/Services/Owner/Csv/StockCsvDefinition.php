<?php
namespace App\Services\Owner\Csv;

use App\Models\StockHistory;

class StockCsvDefinition implements CsvDefinition
{
    /** ヘッダー情報 */
    public function headers(): array
    {
        return [
            'date(日時)', 'item_id(商品id)', 'stock_diff(入出庫）', 'reason(増減理由)'
        ];
    }

    /**
     *  書込み情報（1行分） 
     * @param StockHistory $stock 
     */
    public function mapRows($stock): array
    {
        return [
            $stock->created_at->format('Y-m-d H:i:s'),
            $stock->item_id,
            $stock->stock_diff,
            $stock->reason,
        ];
    }

    /** ファイル名 */
    public function filename(): string
    {
        $date = now()->format('Ymd');
        return "stock_histories_{$date}.csv";
    }
}