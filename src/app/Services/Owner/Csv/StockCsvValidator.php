<?php
namespace App\Services\Owner\Csv;

class StockCsvValidator
{
    private const EXPECT_HEADER = ['item_id', 'quantity', 'reason'];

    public function validate(iterable $rows): array
    {
        $rowsForProcessing = [];
        $headerChecked = false;

        foreach ($rows as $rowNumber => $row) {

            if (! $headerChecked) {
                $row[0] = preg_replace('/^\xEF\xBB\xBF/', '', $row[0]);

                if ($row !== self::EXPECT_HEADER) {
                    throw new \RuntimeException('CSVヘッダーが正しくありません。');
                } 
                $headerChecked = true;
                continue;
            }

            if (count(array_filter($row, fn ($val) => $val !== null && $val !== '')) === 0) {
                continue;
            }

            if (count($row) !== count(self::EXPECT_HEADER)) {
                throw new \RuntimeException("CSVの{$rowNumber}行目：列数が正しくありません。");
            }

            [$itemId, $quantity, $reason] = $row;

            if (! ctype_digit($itemId)) {
                throw new \RuntimeException("CSVの{$rowNumber}行目：item_idが不正です。");
            }

            if (!is_numeric($quantity) || (int)$quantity === 0) {
                throw new \RuntimeException("CSVの{$rowNumber}行目：quantityが不正です。");
            }

            $rowsForProcessing[] = [
                'row' => $rowNumber,
                'item_id' => (int)$itemId,
                'quantity' => (int)$quantity,
                'reason' => trim((string)$reason)
            ];
        }

        return $rowsForProcessing;
    }
}