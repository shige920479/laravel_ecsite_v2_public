<?php

namespace Tests\Unit\Services\Owner;

use App\Services\Owner\Csv\StockCsvValidator;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

// use PHPUnit\Framework\TestCase;


class StockCsvValidatorTest extends TestCase
{

    private StockCsvValidator $validator;

    protected function setUp(): void
    {
        parent::setUp();

        $this->validator = new StockCsvValidator();
    }

    #[Test]
    public function validate_正しくバリデーションし配列を返す(): void
    {
        $rows = [
            1 => ['item_id', 'quantity', 'reason'],
            2 => ['', '', ''], // 全て空なら処理を進める確認
            3 => ["1", "10", 'test-reason'],
        ];
        
        $result = $this->validator->validate($rows);

        $this->assertIsArray($result);
        $this->assertCount(1, $result);
        $this->assertSame([
            'row' => 3,
            'item_id' => 1,
            'quantity' => 10,
            'reason' => 'test-reason'
        ], $result[0]);
    }
    #[Test]
    public function validate_ヘッダーが不正で例外を投げる(): void
    {
        $rows = [
            1 => ['invalid', 'quantity', 'reason'],
            2 => ["1", "10", 'test-reason'],
        ];
        
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('CSVヘッダーが正しくありません。');

        $this->validator->validate($rows);
    }
    #[Test]
    public function validate_列数が不正で例外を投げる(): void
    {
        $rows = [
            1 => ['item_id', 'quantity', 'reason'],
            2 => ["1", '10'],
        ];
        
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('CSVの2行目：列数が正しくありません。');

        $this->validator->validate($rows);
    }
    #[Test]
    public function validate_item_idが不正で例外を投げる(): void
    {
        $rows = [
            1 => ['item_id', 'quantity', 'reason'],
            2 => [1, '10', 'test-reason'],
        ];
        
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage("CSVの2行目：item_idが不正です。");

        $this->validator->validate($rows);
    }
    #[Test]
    public function validate_数量が不正で例外を投げる(): void
    {
        $rows = [
            1 => ['item_id', 'quantity', 'reason'],
            2 => ["1", 'invalid', 'test-reason'],
        ];
        
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage("CSVの2行目：quantityが不正です。");

        $this->validator->validate($rows);
    }
}
