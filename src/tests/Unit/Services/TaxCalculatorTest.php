<?php

namespace Tests\Unit\Services;

use App\Services\TaxCalculator;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

// use PHPUnit\Framework\TestCase;

class TaxCalculatorTest extends TestCase
{
    #[Test]
    public function calculateItem_消費税を正しく計算する(): void
    {
        $priceExTax = 1000;
        $quantity = 10;

        $result = TaxCalculator::calculateItem($priceExTax, $quantity);

        $this->assertSame([
            'tax_rate' => 0.1,
            'unit_tax_amount' => 100,
            'unit_in_tax' => 1100,
            'subtotal_ex_tax' => 10000,
            'subtotal_tax' => 1000,
            'subtotal_in_tax' => 11000,
        ], $result);
    }
    #[Test]
    public function calculateItem_消費税を指定し正しく計算する(): void
    {
        $priceExTax = 1000;
        $quantity = 10;
        $taxRate = 0.08;

        $result = TaxCalculator::calculateItem($priceExTax, $quantity, $taxRate);

        $this->assertSame([
            'tax_rate' => 0.08,
            'unit_tax_amount' => 80,
            'unit_in_tax' => 1080,
            'subtotal_ex_tax' => 10000,
            'subtotal_tax' => 800,
            'subtotal_in_tax' => 10800,
        ], $result);
    }
}
