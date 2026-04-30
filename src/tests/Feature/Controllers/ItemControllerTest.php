<?php

namespace Tests\Feature\Controllers;

use App\Models\Item;
use App\Models\ItemCategory;
use App\Models\Owner;
use App\Models\Shop;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Arr;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ItemControllerTest extends TestCase
{
    use RefreshDatabase;
    
    private ItemCategory $itemCategory;
    private Owner $owner;
    private Shop $shop;
    private const KEYS = ['shop_id', 'item_category_id', 'name', 'information', 'price_ex_tax', 'is_selling'];

    protected function setUp(): void
    {
        parent::setUp();
        $this->owner = Owner::factory()->create();
        $this->shop = Shop::factory()->for($this->owner)->create();
    }

    #[Test]
    public function index_商品データを取得し画面に表示(): void
    {
        $dummyItems = Item::factory()
            ->for($this->shop)
            ->withImages()
            ->count(2)
            ->create();
        
        $res = $this->actingAs($this->owner, 'web_owner')->get(route('owner.item.index'));

        $res->assertOk()
            ->assertViewIs('owner.items.index')
            ->assertViewHas('items', function ($items) use ($dummyItems) {
                $item = $items->first();
                return $items->count() === 2
                    && $item->relationLoaded('mainImage')
                    && $item->relationLoaded('itemCategory')
                    && $item->itemCategory?->relationLoaded('subCategory')
                    && array_key_exists('sales', $item->getAttributes())
                    && array_key_exists('avg_star', $item->getAttributes())
                    && array_key_exists('view_counts', $item->getAttributes());
            });
    }
    #[Test]
    public function index_商品データ未登録であればメッセージを表示(): void
    {
        $res = $this->actingAs($this->owner, 'web_owner')->get(route('owner.item.index'));

        $res->assertOk()
            ->assertViewIs('owner.items.index')
            ->assertViewHas('items', fn ($items) => $items->isEmpty())
            ->assertSee('商品がみつかりません');
    }

    #[Test]
    public function create_商品登録画面を表示する(): void
    {
        $res =$this->actingAs($this->owner, 'web_owner')->get(route('owner.item.create'));

        $res->assertOk()
            ->assertViewIs('owner.items.create')
            ->assertViewHas(['shop', 'categories'])
            ->assertViewHas('is_selling', fn ($is_selling) => $is_selling === 1)
            ->assertViewHas('input', fn ($input) => empty($input))
            ;
    }
    #[Test]
    public function create_確認画面から戻ってきた場合はセッション情報を表示(): void
    {
        session([
            'item' => [
                'shop_id' => $this->shop->id,
                'name' => 'test-item',
                'item_category_id' => 1,
                'information' => 'test-info',
                'price_ex_tax' => 1000,
                'is_selling' => 0,
            ]
        ]);

        $session = session('item');

        $res =$this->actingAs($this->owner, 'web_owner')->get(route('owner.item.create'));

        $res->assertOk()
            ->assertViewIs('owner.items.create')
            ->assertViewHas('is_selling', fn ($is_selling) => $is_selling === 0)
            ->assertViewHas('input');
    }

    #[Test]
    public function confirm_入力情報をセッションに保持し確認画面を表示する(): void
    {
        $req = [
            'shop_id' => $this->shop->id,
            'name' => 'test-item',
            'item_category_id' => $itemCategoryId = ItemCategory::factory()->create()->id,
            'information' => 'test-info',
            'price_ex_tax' => 1000,
            'is_selling' => 1,
        ];

        $res = $this->actingAs($this->owner, 'web_owner')->post(route('owner.item.confirm'), $req);

        $res->assertOk()
            ->assertViewIs('owner.items.confirm')
            ->assertSessionHas('item')
            ->assertSessionHas('item.is_selling', 1)
            ->assertViewHas(['shopName', 'categoryName']);
    }

    #[Test]
    public function confirm_バリーデーションエラーで入力画面へ戻る(): void
    {
        $req = [
            'shop_id' => $this->shop->id,
            'item_category_id' => $itemCategoryId = ItemCategory::factory()->create()->id,
            'is_selling' => 1,
        ];

        $res = $this->actingAs($this->owner, 'web_owner')
            ->from(route('owner.item.create'))
            ->post(route('owner.item.confirm'), $req);

        $res->assertRedirect(route('owner.item.create'))
            ->assertSessionHasErrors([
                'name', 'information', 'price_ex_tax'
            ]);
    }

    #[Test]
    public function confirm_他人のショップには登録できない(): void
    {
        $other = Owner::factory()->create();
        $othersShop = Shop::factory()->for($other)->create();
        $req = [
            'shop_id' => $othersShop->id,
            'name' => 'test-item',
            'item_category_id' => ItemCategory::factory()->create()->id,
            'information' => 'test-info',
            'price_ex_tax' => 1000,
            'is_selling' => 1,
        ];

        $res = $this->actingAs($this->owner, 'web_owner')->post(route('owner.item.confirm'), $req);

        $res->assertForbidden();
    }

    #[Test]
    public function store_セッション情報を取得しDBへ登録(): void
    {
        session([
            'item' => [
                'shop_id' => $this->shop->id,
                'name' => 'test-item',
                'item_category_id' => $itemCategoryId = ItemCategory::factory()->create()->id,
                'information' => 'test-info',
                'price_ex_tax' => 1000,
                'is_selling' => 0,
            ]
        ]);

        $res = $this->actingAs($this->owner, 'web_owner')->post(route('owner.item.store'));
        $item = Item::latest('id')->first();

        $res->assertRedirect(route('owner.item.image.create', ['item' => $item]))
            ->assertSessionHasAll(['status', 'message'])
            ->assertSessionMissing('item')
            ;
        
        $this->assertDatabaseCount('items', 1)
            ->assertDatabaseHas('items', [
                'shop_id' => $this->shop->id,
                'name' => 'test-item',
            ]);
    }
    #[Test]
    public function store_セッション情報が空であれば入力画面へ戻る(): void
    {
        $res = $this->actingAs($this->owner, 'web_owner')->post(route('owner.item.store'));

        $res->assertRedirect(route('owner.item.create'))
            ->assertSessionHas(['status', 'message']);
    }

    #[Test]
    public function edit_商品情報の編集画面を表示する(): void
    {
        $editItem = Item::factory()->for($this->shop)->create();

        $res = $this->actingAs($this->owner, 'web_owner')
            ->get(route('owner.item.edit', ['item' => $editItem]));

        $res->assertOk()
            ->assertViewIs('owner.items.edit')
            ->assertViewHas(['item', 'input', 'categories'])
            ->assertViewHas('item', fn ($item) =>
                $item->id === $editItem->id
            );
    }
    #[Test]
    public function edit_セッション情報があれば同情報を優先表示(): void
    {
        $editItem = Item::factory()->for($this->shop)->create();
        
        $edit = $editItem->toArray();
        $edit['name'] = 'session_check_name'; // name を変更と仮定
        session(['update_item' => $edit]);

        $res = $this->actingAs($this->owner, 'web_owner')
            ->get(route('owner.item.edit', ['item' => $editItem]));

        $res->assertViewIs('owner.items.edit')
            ->assertSee('session_check_name')
            ->assertSessionMissing('update_item');
    }
    #[Test]
    public function updateConfirm_セッションの登録情報を保持し確認画面を表示する(): void
    {
        $editItem = Item::factory()->for($this->shop)->create();
        $updateItem = $editItem->toArray();
        $updateItem['name'] = 'update_name'; // 名前だけ変更
        $req = Arr::only($updateItem, self::KEYS);

        $res = $this->actingAs($this->owner, 'web_owner')
            ->post(route('owner.item.updateConfirm', ['item' => $editItem]), $req);

        $res->assertOk()
            ->assertViewIs('owner.items.confirm')
            ->assertViewHas(['item', 'shopName','categoryName'])
            ->assertSessionHas('update_item');
    }
    #[Test]
    public function updateConfirm_変更内容がなければ編集画面に戻る(): void
    {
        $editItem = Item::factory()->for($this->shop)->create();
        $updateItem = $editItem->toArray();
        $req = Arr::only($updateItem, self::KEYS);

        $res = $this->actingAs($this->owner, 'web_owner')
            ->from(route('owner.item.edit', ['item' => $editItem]))
            ->post(route('owner.item.updateConfirm', ['item' => $editItem]), $req);

        $res->assertRedirect(route('owner.item.edit', ['item' => $editItem]))
            ->assertSessionHas(['status' => 'alert']);
    }
    #[Test]
    public function update_更新内容をDBに登録し画像編集画面へリダイレクトする(): void
    {
        $editItem = Item::factory()->for($this->shop)->create();
        $updateItem = $editItem->toArray();
        $updateItem['name'] = 'update_name'; // 名前だけ変更
        $req = Arr::only($updateItem, self::KEYS);
        session(['update_item' => $req]);

        $res = $this->actingAs($this->owner, "web_owner")
            ->put(route('owner.item.update', ['item' => $editItem]));
        
        $res->assertRedirect(route('owner.item.index'))
            ->assertSessionHas(['status' => 'info'])
            ->assertSessionMissing('update_item');

        $this->assertDatabaseCount('items', 1)
            ->assertDatabaseHas('items', [
                'name' => 'update_name'
            ]);
    }
    #[Test]
    public function update_セッションに情報が無ければ編集画面に戻る(): void
    {
        $editItem = Item::factory()->for($this->shop)->create();

        $res = $this->actingAs($this->owner, "web_owner")
            ->put(route('owner.item.update', ['item' => $editItem]));
        
        $res->assertRedirect(route('owner.item.edit', ['item' => $editItem]))
            ->assertSessionHas(['status' => 'alert']);
    }
    #[Test]
    public function update_他人の商品は変更できない(): void
    {
        $other = Owner::factory()->withShop()->create();
        $editItem = Item::factory()->for($other->shop)->create();

        $res = $this->actingAs($this->owner, "web_owner")
            ->put(route('owner.item.update', ['item' => $editItem]));
        
        $res->assertStatus(403);
    }

    #[Test]
    public function toggleIsSelling_販売ステータスを切替えられる(): void
    {
        $item = Item::factory()->for($this->shop)->create(['is_selling' => true]);

        $res = $this->actingAs($this->owner, 'web_owner')
            ->patch(route('owner.item.toggleIsSelling', ['item' => $item]));

        $res->assertRedirect(route('owner.item.index'));

        $this->assertDatabaseHas('items', [
            'id' => $item->id,
            'is_selling' => false,
        ]);

        $res = $this->actingAs($this->owner, 'web_owner')
            ->patch(route('owner.item.toggleIsSelling', ['item' => $item]));

        $this->assertDatabaseHas('items', [
            'id' => $item->id,
            'is_selling' => true,
        ]);
    }

    #[Test]
    public function destroy_商品をソフトデリートする(): void
    {
        $deleteItem = Item::factory()->for($this->shop)->create();

        $res = $this->actingAs($this->owner, "web_owner")
            ->delete(route('owner.item.destroy', ['item' => $deleteItem]));
        
        $res->assertRedirect(route('owner.item.index'));
        $this->assertDatabaseCount('items', 1)
            ->assertSoftDeleted('items', [
                'id' => $deleteItem->id
            ]);
    }
}
