<?php

namespace Tests\Feature\Services\Owner;

use App\Models\Item;
use App\Models\Owner;
use App\Services\Owner\Csv\StockCsvApplyService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class StockCsvApplyServiceTest extends TestCase
{
    use RefreshDatabase;

    private StockCsvApplyService $service;
    private Owner $owner;
    private Item $item1;
    private Item $item2;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = new StockCsvApplyService();
        $this->owner = Owner::factory()->withShop()->create();
        $this->item1 = Item::factory()->for($this->owner->shop)->create([
            'id' => 1,
            'stock_current' => 5
        ]);
        $this->item2 = Item::factory()->for($this->owner->shop)->create([
            'id' => 2,
            'stock_current' => 10
        ]);
    }

    #[Test]
    public function apply_検証済のCSVデータをDBに登録する(): void
    {
        $rows = [
            ['row' => 2, 'item_id' => 1, 'quantity' => 10, 'reason' => 'test-reason'],
            ['row' => 3, 'item_id' => 2, 'quantity' => 5, 'reason' => 'test-reason'],
        ];

        $result = $this->service->apply($rows, $this->owner->id);

        $this->assertEquals(2, $result);
        $this->assertDatabaseCount('stock_histories', 2)
            ->assertDatabaseHas('items', [
                'id' => 1,
                'stock_current' => 15 
            ])
            ->assertDatabaseHas('items', [
                'id' => 2,
                'stock_current' => 15 
            ])
            ->assertDatabaseHas('stock_histories', [
                'item_id' => 1,
                'stock_after' => 15
            ])
            ->assertDatabaseHas('stock_histories', [
                'item_id' => 2,
                'stock_after' => 15
            ]);
    }

    #[Test]
    public function apply_item_idが不正で例外を投げる(): void
    {
        $rows = [
            ['row' => 2, 'item_id' => 1, 'quantity' => 10, 'reason' => 'test-reason'],
            ['row' => 3, 'item_id' => 99, 'quantity' => 5, 'reason' => 'test-reason'],
            ['row' => 4, 'item_id' => 100, 'quantity' => 5, 'reason' => 'test-reason'],
        ];

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('存在しない、または操作権限のない商品IDがあります: 99,100');

        $this->service->apply($rows, $this->owner->id);
    }
    #[Test]
    public function apply_在庫が0を下回るため例外を投げる(): void
    {
        $rows = [
            ['row' => 2, 'item_id' => 1, 'quantity' => 10, 'reason' => 'test-reason'],
            ['row' => 3, 'item_id' => 2, 'quantity' => -11, 'reason' => 'test-reason'],
        ];

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage(
            "CSVの3行目：商品ID 2 の在庫が不足しています"
        );
        
        $this->service->apply($rows, $this->owner->id);
    }
    #[Test]
    public function apply_在庫が0を下回るため例外発生でロールバック(): void
    {
        $rows = [
            ['row' => 2, 'item_id' => 1, 'quantity' => 10, 'reason' => 'test-reason'],
            ['row' => 3, 'item_id' => 2, 'quantity' => -11, 'reason' => 'test-reason'],
        ];
        
        try {
            $this->service->apply($rows, $this->owner->id);

        } catch (\Throwable $e) {
            $this->assertDatabaseCount('stock_histories', 0)
                ->assertDatabaseHas('items', [
                    'id' => 1,
                    'stock_current' => 5
                ]);
        }
    }
}
