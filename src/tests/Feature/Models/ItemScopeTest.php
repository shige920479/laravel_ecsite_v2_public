<?php

namespace Tests\Feature\Models;

use App\Models\Category;
use App\Models\Item;
use App\Models\ItemCategory;
use App\Models\ItemView;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Review;
use App\Models\Shop;
use App\Models\SubCategory;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\ExpectationFailedException;
use Tests\TestCase;

class ItemScopeTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Carbon::setTestNow(Carbon::parse('2026-01-01 10:00:00'));
    }

    #[Test]
    public function scopeSearchItemName_検索ワードから該当商品を取得する(): void
    {
        $itemName = ['test-1', 'test-2', 'dummy-1', 'dummy-2'];
        foreach ($itemName as $name) {
            Item::factory()->create(['name' => $name]);
        }

        $items = Item::query()->searchItemName('test-1')->get();
        $this->assertCount(1, $items);
        $items = Item::query()->searchItemName('test 2')->get();
        $this->assertCount(1, $items);
        $items = Item::query()->searchItemName('dummy 2')->get();
        $this->assertCount(1, $items);
        $items = Item::query()->searchItemName('dummy')->get();
        $this->assertCount(2, $items);
        $items = Item::query()->searchItemName('-2')->get();
        $this->assertCount(2, $items);
        $items = Item::query()->searchItemName('test dummy')->get();
        $this->assertEmpty($items);
    }

    #[Test]
    public function scopeSortItemList_価格の高い順に並べ替える(): void
    {
        $itemHigh = Item::factory()->create(['price_ex_tax' => 1500]);
        $itemLow = Item::factory()->create(['price_ex_tax' => 500]);
        $itemMid = Item::factory()->create(['price_ex_tax' => 1000]);

        $items = Item::query()->sortItemList('price_desc')->get();
        $ids = [$itemHigh->id, $itemMid->id, $itemLow->id];

        $this->assertCount(3, $items);
        $this->assertSame($ids, $items->pluck('id')->toArray());
    }
    #[Test]
    public function scopeSortBy_価格の安い順に並べ替える(): void
    {
        $itemHigh = Item::factory()->create(['price_ex_tax' => 1500]);
        $itemLow = Item::factory()->create(['price_ex_tax' => 500]);
        $itemMid = Item::factory()->create(['price_ex_tax' => 1000]);

        $items = Item::query()->sortItemList('price_asc')->get();
        $expectedIds = [$itemLow->id, $itemMid->id, $itemHigh->id];

        $this->assertCount(3, $items);
        $this->assertSame($expectedIds, $items->pluck('id')->toArray());

    }
    #[Test]
    public function scopeSortBy_登録日の新しい順に並べ替える(): void
    {
        $today = Item::factory()->create(['created_at' => now()]);
        $before5 = Item::factory()->create(['created_at' => now()->subDays(5)]);
        $before3 = Item::factory()->create(['created_at' => now()->subDays(3)]);

        $items = Item::query()->sortItemList('date_desc')->get();
        $expectedIds = [$today->id, $before3->id, $before5->id];

        $this->assertCount(3, $items);
        $this->assertSame($expectedIds, $items->pluck('id')->toArray());
    }

    #[Test]
    public function scopeSortBy_shop番号順に並べ替える(): void
    {
        $shop1 = Shop::factory()->create();
        $shop2 = Shop::factory()->create();
        $shop3 = Shop::factory()->create();
        Item::factory()->for($shop1)->create();
        Item::factory()->for($shop2)->create();
        Item::factory()->for($shop3)->create();

        $items = Item::query()->sortItemList('shop_asc')->get();
        $expectedIds = collect([$shop1->id, $shop2->id, $shop3->id])->sortBy('asc')->toArray();
        
        $this->assertCount(3, $items);
        $this->assertSame($expectedIds, $items->pluck('id')->toArray());
    }

    #[Test]
    public function scopeSearchByNameOrShop_商品名かショップ名で検索し正しい結果を返す():void
    {
        $shopNames = ['shop-dummy-1', 'shop-dummy-2', 'shop-test-3'];
        $itemNames = ['item-dummy-1', 'item-dummy-2', 'item-test-3'];
        foreach ($shopNames as $name) {
            $shop = Shop::factory()->create(['name' => $name]);
            Item::factory()->for($shop)->create(['name' => 'products']);
        }
        $shop = Shop::factory()->create(['name' => 'abcstore']);
        foreach ($itemNames as $name) {
            Item::factory()->for($shop)->create(['name' => $name]);
        }

        $items = Item::query()->searchByNameOrShop('dummy')->get();
        $this->assertCount(4, $items);
        $items = Item::query()->searchByNameOrShop('test')->get();
        $this->assertCount(2, $items);
        $items = Item::query()->searchByNameOrShop('2')->get();
        $this->assertCount(2, $items);
        $items = Item::query()->searchByNameOrShop(' dummy  2 ')->get();
        $this->assertCount(2, $items);
    }
    #[Test]
    public function scopeFilterCategorySlug_カテゴリーで絞り込み(): void
    {
        $this->createDataForCategoryFilter();

        $items = Item::query()->filterCategorySlug('parent1')->get();
        $this->assertCount(3, $items);
        $items = Item::query()->filterCategorySlug('parent2')->get();
        $this->assertCount(2, $items);
    }
    #[Test]
    public function scopeFilterSubCategorySlug_サブカテゴリーで絞り込み(): void
    {
        $this->createDataForCategoryFilter();
        
        $items = Item::query()->filterSubCategorySlug('sub1')->get();
        $this->assertCount(3, $items);

        $items = Item::query()->filterSubCategorySlug('sub2')->get();
        $this->assertCount(2, $items);
    }
    #[Test]
    public function scopeFilterItemCategorySlug_アイテムカテゴリーで絞り込み(): void
    {
        $this->createDataForCategoryFilter();
        
        $items = Item::query()->filterItemCategorySlug('itemcat1')->get();
        $this->assertCount(3, $items);

        $items = Item::query()->filterItemCategorySlug('itemcat2')->get();
        $this->assertCount(2, $items);
    }

    #[Test]
    public function scopeWithAvgStar_商品毎のレーティング平均を取得する() : void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $item = Item::factory()->create();
        Review::factory()->for($user1)->for($item)->create(['star' => 4]);
        Review::factory()->for($user2)->for($item)->create(['star' => 2]);

        $item = Item::query()->withAvgStar()->find($item->id);

        $this->assertEquals(3, $item->avg_star);
    }
    #[Test]
    public function scopeWithAvgStar_評価が無ければnullが入る() : void
    {
        $item = Item::factory()->create();
        $item = Item::query()->withAvgStar()->find($item->id);

        $this->assertNull($item->avg_star);
    }

    /**
     * オーダー関連のテーブル定義が変えたので修正が必要、ファクトリーの修正後に対応
     */
    // #[Test]
    // public function scopeWithSales_指定した期間の販売数の合計を取得する(): void
    // {
    //     $item = Item::factory()->create();
    //     $order = Order::factory()->create(); // 本来は3つ必要もテストなので1つに設定

    //     OrderItem::factory()->forItem($item)->for($order)->create([
    //         'quantity' => 5, 'created_at' => '2025-12-01 10:00:00'
    //     ]);
    //     OrderItem::factory()->forItem($item)->for($order)->create([
    //         'quantity' => 10, 'created_at' => '2025-12-15 10:00:00'
    //     ]);
    //     OrderItem::factory()->forItem($item)->for($order)->create([
    //         'quantity' => 15, 'created_at' => '2025-12-30 10:00:00'
    //     ]);

    //     $items = Item::query()->withSales(20)->find($item->id);

    //     $this->assertEquals(25, $items->sales);
    // }
    #[Test]
    public function scopeWithSales_指定した期間の販売数がゼロであればnullが入る(): void
    {
        $item = Item::factory()->create();
        $items = Item::query()->withSales(20)->find($item->id);

        $this->assertNull($items->sales);
    }
    #[Test]
    public function scopeWithViewCounts_指定した期間の閲覧数を取得する(): void
    {
        $item = Item::factory()->create();
        ItemView::factory()->for($item)->create(['created_at' => '2025-12-01 10:00:00']);
        ItemView::factory()->for($item)->create(['created_at' => '2025-12-15 10:00:00']);
        ItemView::factory()->for($item)->create(['created_at' => '2025-12-30 10:00:00']);

        $item = Item::query()->withViewCounts(20)->find($item->id);

        $this->assertEquals(2, $item->view_counts);
    }

    /** カテゴリーフィルターテスト用データ生成  */
    private function createDataForCategoryFilter(): void
    {
        $cate1 = Category::factory()->create(['slug' => 'parent1']);
        $cate2 = Category::factory()->create(['slug' => 'parent2']);
        $subCate1 = SubCategory::factory()->for($cate1)->create(['slug' => 'sub1']);
        $subCate2 = SubCategory::factory()->for($cate2)->create(['slug' => 'sub2']);
        $itemCate1 = ItemCategory::factory()->for($subCate1)->create(['slug' => 'itemcat1']);
        $itemCate2 = ItemCategory::factory()->for($subCate2)->create(['slug' => 'itemcat2']);

        Item::factory()->for($itemCate1)->count(3)->create();
        Item::factory()->for($itemCate2)->count(2)->create();
    }
}
