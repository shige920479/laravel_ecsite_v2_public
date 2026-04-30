<?php

namespace Tests\Feature\Controllers;

use App\Exceptions\ImageMoveException;
use App\Http\Controllers\Owner\ItemImageController;
use App\Models\Item;
use App\Models\ItemImage;
use App\Models\Owner;
use App\Services\Owner\ImageService;
use App\Services\Owner\ItemImageService;
use Exception;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Mockery;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ItemImageControllerTest extends TestCase
{

    use RefreshDatabase;
    private Owner $owner;

    protected function setUp(): void
    {
        parent::setUp();
        $this->owner = Owner::factory()->withShop()->create();
    }

    #[Test]
    public function create_画像登録用画面を表示できる(): void
    {
        $item = Item::factory()->for($this->owner->shop)->create();

        $res = $this->actingAs($this->owner, 'web_owner')
            ->get(route('owner.item.image.create', ['item' => $item]));

        $res->assertOk()
            ->assertViewIs('owner.images.create')
            ->assertViewHas('item');
    }
    #[Test]
    public function create_他人の商品の画像登録画面を表示できない(): void
    {
        $other = Owner::factory()->withShop()->create();
        $otherItem = Item::factory()->for($other->shop)->create();

        $res = $this->actingAs($this->owner, 'web_owner')
            ->get(route('owner.item.image.create', ['item' => $otherItem]));
        
        $res->assertForbidden();
    }

    #[Test]
    public function store_画像を登録し次画面へリダイレクトする(): void
    {
        $item = Item::factory()->for($this->owner->shop)->create();
        session([
            'tmp_item_image' => [
                1 => 'tmp/test.webp'
            ]
        ]);

        $mock = Mockery::mock(ItemImageService::class);
        $mock->shouldReceive('storeItemImages')->once();
        $this->app->instance(ItemImageService::class, $mock);

        $res = $this->actingAs($this->owner, 'web_owner')
            ->post(route('owner.item.image.store', ['item' => $item]));

        $res->assertRedirect(route('owner.item.stock.create', ['item' => $item]))
            ->assertSessionHas(['status', 'message']);
    }
    #[Test]
    public function store_システムエラーで登録画面に戻る(): void
    {
        $item = Item::factory()->for($this->owner->shop)->create();
        session([
            'tmp_item_image' => [
                1 => 'tmp/test.webp'
            ]
        ]);

        $mock = Mockery::mock(ItemImageService::class);
        $mock->shouldReceive('storeItemImages')->once()->andThrow(new \Exception('DB error'));
        $this->app->instance(ItemImageService::class, $mock);

        $res = $this->actingAs($this->owner, 'web_owner')
            ->from(route('owner.item.image.create', ['item' => $item]))
            ->post(route('owner.item.image.store', ['item' => $item]));

        $res->assertRedirect(route('owner.item.image.create', ['item' => $item]))
            ->assertSessionHas(['status', 'message']);
    }
    #[Test]
    public function store_他人の商品には画像登録できない(): void
    {
        $other = Owner::factory()->withShop()->create();
        $otherItem = Item::factory()->for($other->shop)->create();
        session([
            'tmp_item_image' => [
                1 => 'tmp/test.webp'
            ]
        ]);

        $res = $this->actingAs($this->owner, 'web_owner')
            ->post(route('owner.item.image.store', ['item' => $otherItem]));

        $res->assertForbidden();
    }
    #[Test]
    public function edit_画像更新用画面を表示する(): void
    {
        $item = Item::factory()->for($this->owner->shop)->create();
        ItemImage::factory()->for($item)->main()->create();
        ItemImage::factory()->for($item)->order(2)->create();
        ItemImage::factory()->for($item)->order(3)->create();

        $res = $this->actingAs($this->owner, 'web_owner')
            ->get(route('owner.item.image.edit', ['item' => $item]));

        $res->assertOk()
            ->assertViewIs('owner.images.edit')
            ->assertViewHas(['item', 'itemImages']);
    }

    #[Test]
    public function edit_他人の画像更新用画面は表示できない(): void
    {
        $other = Owner::factory()->withShop()->create();
        $item = Item::factory()->for($other->shop)->create();
        ItemImage::factory()->for($item)->main()->create();

        $res = $this->actingAs($this->owner, 'web_owner')
            ->get(route('owner.item.image.edit', ['item' => $item]));

        $res->assertForbidden();
    }

    #[Test]
    public function update_画像情報を更新して商品一覧表示の戻る(): void
    {
        $item = Item::factory()->for($this->owner->shop)->create();
        $itemImage = ItemImage::factory()->for($item)->main()->create();
        session(['tmp_item_image.2' => 'tmp/test2.webp']);
        $req = [
            'item_image_ids' => [null, null, $itemImage->id, null],
            'filenames' => [null, null, $itemImage->filename, null],
            'def_sort' => [3, 2, 1, 4],
            'sort_order' => [1, 2, 3, 4]
        ];

        $mock = Mockery::mock(ItemImageService::class);
        $mock->shouldReceive('storeOrCreateItemImage')->once()->andReturn(2);
        $this->app->instance(ItemImageService::class, $mock);

        $res = $this->actingAs($this->owner, 'web_owner')
            ->put(route('owner.item.image.update', ['item' => $item]), $req);

        $res->assertRedirect(route('owner.item.index'))
            ->assertSessionHas([
                'status' => 'info',
                'message' => "商品番号: {$item->id} 商品名: {$item->name} の画像登録を更新しました"
            ])
            ->assertSessionMissing('tmp_item_image');
    }

    #[Test]
    public function update_更新処理中にエラーが発生し編集画面に戻る(): void
    {
        $item = Item::factory()->for($this->owner->shop)->create();
        $itemImage = ItemImage::factory()->for($item)->main()->create();
        session(['tmp_item_image.2' => 'tmp/test2.webp']);
        $req = [
            'item_image_ids' => [null, null, $itemImage->id, null],
            'filenames' => [null, null, $itemImage->filename, null],
            'def_sort' => [3, 2, 1, 4],
            'sort_order' => [1, 2, 3, 4]
        ];

        $mock = Mockery::mock(ItemImageService::class);
        $mock->shouldReceive('storeOrCreateItemImage')->once()
            ->andThrow(new ImageMoveException('image-move-error'));
        $this->app->instance(ItemImageService::class, $mock);

        $res = $this->actingAs($this->owner, 'web_owner')
            ->from(route('owner.item.image.edit', ['item' => $item]))
            ->put(route('owner.item.image.update', ['item' => $item]), $req);

        $res->assertRedirect(route('owner.item.image.edit', ['item' => $item]))
            ->assertSessionHasErrors('images')
            ->assertSessionMissing('tmp_item_image');
    }
    #[Test]
    public function update_更新対象がなく編集画面に戻る(): void
    {
        $item = Item::factory()->for($this->owner->shop)->create();
        $itemImage = ItemImage::factory()->for($item)->main()->create();
        $req = [
            'item_image_ids' => [$itemImage->id, null, null, null],
            'filenames' => [$itemImage->filename, null, null, null],
            'def_sort' => [1, 2, 3, 4],
            'sort_order' => [1, 2, 3, 4],
            'has-deleted' => 0,
        ];

        $res = $this->actingAs($this->owner, 'web_owner')
            ->from(route('owner.item.image.edit', ['item' => $item]))
            ->put(route('owner.item.image.update', ['item' => $item]), $req);

        $res->assertRedirect(route('owner.item.image.edit', ['item' => $item]))
            ->assertSessionHasErrors('images')
            ->assertSessionMissing('tmp_item_images');
    }
    #[Test]
    public function update_画像ファイルがなく編集画面に戻る(): void
    {
        $item = Item::factory()->for($this->owner->shop)->create();
        $req = [
            'item_image_ids' => [null, null, null, null], // 画像を削除した前提
            'filenames' => [null, null, null, null], // 画像を削除した前提
            'def_sort' => [1, 2, 3, 4],
            'sort_order' => [1, 2, 3, 4],
            'has-deleted' => 1, // フラグを変更
        ];

        $res = $this->actingAs($this->owner, 'web_owner')
            ->from(route('owner.item.image.edit', ['item' => $item]))
            ->put(route('owner.item.image.update', ['item' => $item]), $req);

        $res->assertRedirect(route('owner.item.image.edit', ['item' => $item]))
            ->assertSessionHasErrors(['images' => '商品には必ず画像ファイルを最低1つ登録願います'])
            ->assertSessionMissing('tmp_item_images');
    }

    #[Test]
    public function destroy_画像を削除しレスポンスとメッセージを返す(): void
    {
        $item = Item::factory()->for($this->owner->shop)->create();
        $itemImage = ItemImage::factory()->for($item)->main()->create();

        $mock = Mockery::mock(ImageService::class);
        $mock->shouldReceive('deleteFile')->once();
        $this->app->instance(ImageService::class, $mock);

        $res = $this->actingAs($this->owner, "web_owner")
            ->deleteJson(route('owner.item.image.delete', ['itemImage' => $itemImage]));

        $res->assertStatus(200)
            ->assertjson(['message' => '登録画像を削除しました']);
        $this->assertSoftDeleted('item_images', [
            'id' => $itemImage->id
        ]);
    }
}
