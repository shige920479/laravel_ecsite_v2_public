<?php
namespace App\Services;

use Illuminate\Contracts\Container\BindingResolutionException;

class TaxCalculator
{
    /**
     * 現在の税率を取得
     */
    public static function currentTaxRate(): float
    {
        return (float) config('constants.TAX_RATE');
    }

    /**
     * 明細単位の金額計算
     */
    public static function calculateItem(int $priceExTax, int $quantity, ?float $taxRate = null): array
    {
        $taxRate ??= self::currentTaxRate();

        // 単価税額（四捨五入）
        $unitTax = (int) round($priceExTax * $taxRate);
        // 税込単価
        $unitInTax = $priceExTax + $unitTax;

        $subtotalEx = $priceExTax * $quantity;
        $subtotalTax = $unitTax * $quantity;
        $subtotalIn = $subtotalEx + $subtotalTax;

        // 明細合計
        return [
            'tax_rate' => $taxRate,
            'unit_tax_amount' => $unitTax,
            'unit_in_tax' => $unitInTax,
            'subtotal_ex_tax' => $subtotalEx,
            'subtotal_tax' => $subtotalTax,
            'subtotal_in_tax' => $subtotalIn,
        ];
    }
}