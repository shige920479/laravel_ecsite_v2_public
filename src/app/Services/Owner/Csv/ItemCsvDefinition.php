<?php
namespace App\Services\Owner\Csv;

use App\Models\Item;

class ItemCsvDefinition implements CsvDefinition
{
    /** ヘッダー情報 */
    public function headers(): array
    {
        return [
            '商品番号', '商品名', 'カテゴリー', '商品単価', '在庫数量', '販売数量', 'レーティング', '閲覧数', '登録日'
        ];
    }

    /**
     * 書込み情報（1行分） 
     * @param Item $item 
     */
    public function mapRows($item): array
    {
        return [
            $item->id ?? '',
            $item->name ?? '',
            $item->itemCategory?->name ?? '',
            $item->price_ex_tax,
            $item->stock_current ?? '',
            $item->sales ?? '',
            $item->avg_star ?? '',
            $item->view_counts ?? '',
            $item->created_at?->format('Y-m-d H:i') ?? ''
        ];
    }

    /** ファイル名 */
    public function filename(): string
    {
        $date = now()->format('Ymd');
        return "items_{$date}.csv";
    }
}