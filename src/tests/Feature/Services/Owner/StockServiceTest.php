<?php

namespace Tests\Feature\Services\Owner;

use App\Models\Item;
use App\Services\Owner\StockService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\Request;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class StockServiceTest extends TestCase
{
    use RefreshDatabase;
    private StockService $stockService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->stockService = new StockService();
    }

    #[Test]
    public function storeStockAndHistory_入庫を登録する(): void
    {
        $item = Item::factory()->create(['stock_current' => 10]);

        $input = [
            'stock_diff' => 10,
            'up_down' => 1,
            'reason' => null,
        ];

        $this->stockService->storeStockAndHistory($input, $item);

        $this->assertDatabaseCount('items', 1)
        ->assertDatabaseHas('items', [
            'id' => $item->id,
            'stock_current' => 20
        ])
        ->assertDatabaseCount('stock_histories', 1)
        ->assertDatabaseHas('stock_histories', [
            'item_id' => $item->id,
            'stock_diff' => 10,
            'stock_after' => 20,
        ]);
    }
    #[Test]
    public function storeStockAndHistory_出庫を登録する(): void
    {
        $item = Item::factory()->create(['stock_current' => 10]);

        $input = [
            'stock_diff' => 3,
            'up_down' => 0, // 出庫
            'reason' => null,
        ];

        $this->stockService->storeStockAndHistory($input, $item);

        $this->assertDatabaseCount('items', 1)
        ->assertDatabaseHas('items', [
            'id' => $item->id,
            'stock_current' => 7
        ])
        ->assertDatabaseCount('stock_histories', 1)
        ->assertDatabaseHas('stock_histories', [
            'item_id' => $item->id,
            'stock_diff' => -3,
            'stock_after' => 7,
        ]);
    }
}
