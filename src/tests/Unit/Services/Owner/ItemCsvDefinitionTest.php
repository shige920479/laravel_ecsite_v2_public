<?php

namespace Tests\Unit\Services\Owner;

use App\Models\Item;
use App\Models\ItemCategory;
use App\Services\Owner\Csv\ItemCsvDefinition;
use Carbon\Carbon;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

// use PHPUnit\Framework\TestCase;

class ItemCsvDefinitionTest extends TestCase
{
    private ItemCsvDefinition $definition;

    protected function setUp(): void
    {
        parent::setUp();
        Carbon::setTestNow(Carbon::parse('2026-01-01 10:00:00'));
        $this->definition = new ItemCsvDefinition();
    }

    #[Test]
    public function headers_正しくヘッダーを返す(): void
    {
        $header = ['商品番号', '商品名', 'カテゴリー', '商品単価',
            '在庫数量', '販売数量', 'レーティング', '閲覧数', '登録日'];

        $result = $this->definition->headers();

        $this->assertSame($header, $result);
    }
    #[Test]
    public function mapRows_モデルから必要な値を取得し配列で返す(): void
    {
        $item = Item::factory()->make([
            'id' => 1,
            'shop_id' => null,
            'item_category_id' => null,
            'name' => 'test-name',
            'price_ex_tax' => 1000,
        ]);

        $itemCategory = ItemCategory::factory()->make([
            'id' => 1,
            'sub_category_id' => 2,
            'name' => 'cate-1',
        ]);
        $item->setRelation('itemCategory', $itemCategory);
        
        $item->stock_current = 100;
        $item->sales = 50;
        $item->avg_star = 4;
        $item->view_counts = 100;
        $item->created_at = '2026-01-01 10:00:00';
        
        $result = $this->definition->mapRows($item);

        $this->assertSame([
            1, 'test-name', 'cate-1', 1000, 100, 50, 4, 100, '2026-01-01 10:00'
        ], $result);
    }
    #[Test]
    public function filename_正しくファイル名を変えす(): void
    {
        $result = $this->definition->filename();
        
        $this->assertSame('items_20260101.csv', $result);
    }
}
