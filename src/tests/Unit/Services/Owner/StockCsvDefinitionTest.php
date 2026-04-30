<?php

namespace Tests\Unit\Services\Owner;

use App\Models\Item;
use App\Models\StockHistory;
use App\Services\Owner\Csv\StockCsvDefinition;
use Carbon\Carbon;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

// use PHPUnit\Framework\TestCase;

class StockCsvDefinitionTest extends TestCase
{
    private StockCsvDefinition $definition;

    protected function setUp(): void
    {
        parent::setUp();
        Carbon::setTestNow(Carbon::parse('2026-01-01 10:00:00'));
        $this->definition = new StockCsvDefinition();
    }

    #[Test]
    public function headers_正しくヘッダーを返す(): void
    {
        $header = ['date(日時)', 'item_id(商品id)', 'stock_diff(入出庫）', 'reason(増減理由)'];

        $result = $this->definition->headers();

        $this->assertSame($header, $result);
    }
    #[Test]
    public function mapRows_モデルから必要な値を取得し配列で返す(): void
    {
        $stock = StockHistory::factory()->make([
            'item_id' => 1,
            'stock_diff' => 10,
            'reason' => '入荷',
            'created_at' => '2026-01-01 10:00:00'
        ]);

        $result = $this->definition->mapRows($stock);

        $this->assertSame([
            '2026-01-01 10:00:00', 1, 10, '入荷'
        ], $result);
    }
    #[Test]
    public function filename_正しくファイル名を変えす(): void
    {
        $result = $this->definition->filename();
        
        $this->assertSame('stock_histories_20260101.csv', $result);
    }
}
