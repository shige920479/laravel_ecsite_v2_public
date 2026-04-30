<?php

namespace Tests\Feature\Controllers\Customer\Queries;

use App\Models\Category;
use App\Models\Item;
use App\Models\ItemCategory;
use App\Services\Customer\Item\DTO\ItemQuery;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ItemQueryTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function apply_商品名検索ができる(): void
    {
        Item::factory()->create(['name' => 'mug']);
        Item::factory()->create(['name' => 'bottle']);

        $query = new ItemQuery(
            itemSearch: 'mug',
            itemSort: null,
            category: null,
            subCategory: null,
            itemCategory: null,
            perPage: 8
        );

        $items = $query->apply(Item::query())->get();

        $this->assertCount(1, $items);
        $this->assertSame('mug', $items->first()->name);
    }

    #[Test]
    public function apply_指定条件で並べ替えができる(): void
    {
        Item::factory()->create(['price_ex_tax' => 1000]);
        Item::factory()->create(['price_ex_tax' => 1200]);
        Item::factory()->create(['price_ex_tax' => 1300]);

        $query = new ItemQuery(
            itemSearch: null,
            itemSort: 'price_desc',
            category: null,
            subCategory: null,
            itemCategory: null,
            perPage: 8
        );

        $items = $query->apply(Item::query())->get();
        $sorted = $items->pluck('price_ex_tax')->values()->toArray();

        $this->assertCount(3, $items);
        $this->assertSame([1300, 1200, 1000], $sorted);
    }

    #[Test]
    public function apply_新着順で並べ替えができる(): void
    {
        $item1 = Item::factory()->create(['created_at' => '2026-01-01 10:00:00']);
        $item2 = Item::factory()->create(['created_at' => '2026-01-05 10:00:00']);
        $item3 = Item::factory()->create(['created_at' => '2026-01-03 10:00:00']);

        $query = new ItemQuery(
            itemSearch: null,
            itemSort: 'date_desc',
            category: null,
            subCategory: null,
            itemCategory: null,
            perPage: 8
        );

        $items = $query->apply(Item::query())->get();
        $sorted = $items->pluck('id')->values()->toArray();

        $this->assertCount(3, $items);
        $this->assertSame([$item2->id, $item3->id, $item1->id], $sorted);
    }
    #[Test]
    public function apply_アイテムカテゴリーで絞り込みができる(): void
    {
        $itemCate1 = ItemCategory::factory()->create();
        $itemCate2 = ItemCategory::factory()->create();
        $itemCate3 = ItemCategory::factory()->create();
        
        $item1 = Item::factory()->for($itemCate1)->create();
        $item2 = Item::factory()->for($itemCate2)->create();
        $item3 = Item::factory()->for($itemCate3)->create();

        $query = new ItemQuery(
            itemSearch: null,
            itemSort: null,
            category: $itemCate2->subCategory->category->slug,
            subCategory: $itemCate2->subCategory->slug,
            itemCategory: $itemCate2->slug,
            perPage: 8
        );

        $items = $query->apply(Item::query())->get();

        $this->assertCount(1, $items);
        $this->assertSame($item2->id, $items->first()->id);
    }

    #[Test]
    public function apply_カテゴリーで絞り込みができる(): void
    {
        $cate1 = Category::factory()->withTree()->create();
        $cate2 = Category::factory()->withTree()->create();
        
        $item1 = Item::factory()->for($cate1->subCategories->first()->itemCategories->first())->create();
        $item2 = Item::factory()->for($cate2->subCategories->first()->itemCategories->first())->create();

        $query = new ItemQuery(
            itemSearch: null,
            itemSort: null,
            category: $cate2->slug,
            subCategory: null,
            itemCategory: null,
            perPage: 8
        );

        $items = $query->apply(Item::query())->get();

        $this->assertCount(1, $items);
        $this->assertSame($item2->id, $items->first()->id);
    }

    #[Test]
    public function apply_検索とカテゴリー絞り込みを同時に適用できる(): void
    {
        $cate = Category::factory()->withTree()->create();

        Item::factory()
            ->for($cate->subCategories->first()->itemCategories->first())
            ->create(['name' => 'mug']);

        Item::factory()
            ->for($cate->subCategories->first()->itemCategories->first())
            ->create(['name' => 'bottle']);

        $query = new ItemQuery(
            itemSearch: 'mug',
            itemSort: null,
            category: $cate->slug,
            subCategory: null,
            itemCategory: null,
            perPage: 8
        );

        $items = $query->apply(Item::query())->get();

        $this->assertCount(1, $items);
        $this->assertSame('mug', $items->first()->name);
    }
}
