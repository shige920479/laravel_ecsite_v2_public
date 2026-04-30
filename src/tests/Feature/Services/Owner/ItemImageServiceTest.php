<?php

namespace Tests\Feature\Services\Owner;

use App\Exceptions\ImageMoveException;
use App\Models\Item;
use App\Models\ItemImage;
use App\Models\Owner;
use App\Services\Owner\ImagePathService;
use App\Services\Owner\ImageService;
use App\Services\Owner\ItemImageService;
use Exception;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;
use Mockery;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ItemImageServiceTest extends TestCase
{
    use RefreshDatabase;
    private ItemImageService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new ItemImageService(
            new ImageService(
                new ImageManager(new Driver()), new ImagePathService()
            )
        );
        Storage::fake('public');
    }

    #[Test]
    public function storeItemImages_商品画像をuploadsに移動しDB登録(): void
    {
        $id = 1;
        $owner = Owner::factory()->create(['id' => $id]);
        $item = Item::factory()->create();
        $file1 = "tmp/test1.webp";
        $file2 = "tmp/test2.webp";
        $file3 = "tmp/test3.webp";
        Storage::put($file1, 'dummy1');
        Storage::put($file2, 'dummy1');
        Storage::put($file3, 'dummy1');

        // 逆順にセッションへ保存
        session([
            'tmp_item_image.3' => $file3,
            'tmp_item_image.2' => $file2,
            'tmp_item_image.1' => $file1,
        ]);

        $this->service->storeItemImages($item, $owner->id);

        Storage::assertExists('uploads/item-images/test1.webp');
        Storage::assertExists('uploads/item-images/test2.webp');
        Storage::assertExists('uploads/item-images/test3.webp');
        $this->assertEmpty(Storage::files('tmp'));

        $this->assertDatabaseCount('item_images', 3);
        $this->assertDatabaseHas('item_images',[
            'filename' => 'uploads/item-images/test1.webp',
            'sort_order' => 1,
        ]);
    }

    #[Test]
    public function storeItemImages_アップロードに失敗して例外をスロー(): void
    {
        $owner = Owner::factory()->create();
        $item = Item::factory()->create();

        $file1 = "tmp/test1.webp";
        $file2 = "tmp/test2.webp";
        Storage::put($file1, 'dummy1');
        Storage::put($file2, 'dummy2');
        session([
            'tmp_item_image.1' => $file1,
            'tmp_item_image.2' => $file2,
        ]);

        $mock = Mockery::mock(ImageService::class);
        $mock->shouldReceive('moveToUploads')->once()->andReturn('uploads/item-images/test1.webp');
        $mock->shouldReceive('moveToUploads')->once()->andThrow(new \Exception('DB error'));
        
        $this->expectException(\Exception::class);

        $service = new ItemImageService($mock);
        $service->storeItemImages($item, $owner->id);
    }
    #[Test]
    public function storeItemImages_アップロードに失敗して例外をスローし移動済みのファイルを削除(): void
    {
        $owner = Owner::factory()->create();
        $item = Item::factory()->create();

        $file1 = "tmp/test1.webp";
        $file2 = "tmp/test2.webp";
        Storage::put($file1, 'dummy1');
        Storage::put($file2, 'dummy2');
        session([
            'tmp_item_image.1' => $file1,
            'tmp_item_image.2' => $file2,
        ]);

        $mock = Mockery::mock(ImageService::class);
        $mock->shouldReceive('moveToUploads')->once()->andReturn('uploads/item-images/test1.webp');
        $mock->shouldReceive('moveToUploads')->once()->andThrow(new \Exception('DB error'));
        $mock->shouldReceive('deleteFile')->once()->with(['uploads/item-images/test1.webp']);
        
        $service = new ItemImageService($mock);
        
        try {
            $service->storeItemImages($item, $owner->id);
            $this->fail('強制的に例外発生');
        } catch (\Exception $e) {
            $this->assertDatabaseCount('item_images', 0);
        }

    }

    #[Test]
    public function normalizeValited_配列入力されたデータをDB処理用に整形(): void
    {
        $itemId = 1;
        session(['tmp_item_image.1' => 'tmp1.webp']);
        session(['tmp_item_image.2' => 'tmp2.webp']);

        $array = [
            'item_image_ids' => [3, 2, 1, null],
            'filenames' => ['test3.webp', 'test2.webp', 'test1.webp', null],
            'def_sort' => [3, 2, 1, 4],
            'sort_order' => [1, 2, 3, 4]
        ];
        
        $mock = Mockery::mock(ImageService::class);
        $service = new class($mock) extends ItemImageService {
            public function callNormalizeValidated($array, $itemId) {
               return $this->normalizeValited($array, $itemId);
            }
        };

        $result = $service->callNormalizeValidated($array, $itemId);

        $excpeted = [
            [
                "id" => 3,
                "item_id" => 1,
                "filename" => "test3.webp",
                "def_sort" => 3,
                "tmp_image" => null,
                "sort_order" => 1
            ],
            [
                "id" => 2,
                "item_id" => 1,
                "filename" => "test2.webp",
                "def_sort" => 2,
                "tmp_image" => "tmp2.webp",
                "sort_order" => 2,
            ],
            [
                "id" => 1,
                "item_id" => 1,
                "filename" => "test1.webp",
                "def_sort" => 1,
                "tmp_image" => "tmp1.webp",
                "sort_order" => 3,
            ],
            [
                "id" => null,
                "item_id" => 1,
                "filename" => null,
                "def_sort" => 4,
                "tmp_image" => null,
                "sort_order" => 4,
            ]
        ];
    
        $this->assertSame($excpeted, $result);
    }

    #[Test]
    public function sortAndFilterForImage_並べ替えて画像のない配列は排除(): void
    {
        $inputs = [
            [
                "id" => 3,
                "item_id" => 1,
                "filename" => "test3.webp",
                "def_sort" => 3,
                "tmp_image" => null,
                "sort_order" => 1
            ],
            [
                "id" => 2,
                "item_id" => 1,
                "filename" => "test2.webp",
                "def_sort" => 2,
                "tmp_image" => "tmp2.webp",
                "sort_order" => 2,
            ],
            [
                "id" => 1,
                "item_id" => 1,
                "filename" => null,
                "def_sort" => 1,
                "tmp_image" => null,
                "sort_order" => 3,
            ],
            [
                "id" => null,
                "item_id" => 1,
                "filename" => null,
                "def_sort" => 4,
                "tmp_image" => null,
                "sort_order" => 4,
            ]
        ];

        $mock = Mockery::mock(ImageService::class);
        $service = new class($mock) extends ItemImageService {
            public function callSortAndFilterForImage($inputs) {
                return $this->sortAndFilterForImage($inputs);
            }
        };

        $result = $service->callSortAndFilterForImage($inputs);

        $excpeted = [
            [
                "id" => 3,
                "item_id" => 1,
                "filename" => "test3.webp",
                "def_sort" => 3,
                "tmp_image" => null,
                "sort_order" => 1
            ],
            [
                "id" => 2,
                "item_id" => 1,
                "filename" => "test2.webp",
                "def_sort" => 2,
                "tmp_image" => "tmp2.webp",
                "sort_order" => 2,
            ],
        ];

        $this->assertSame($excpeted, $result->toArray());
    }

    #[Test]
    public function storeOrCreateItemImage_tmp画像なしで並びも変更なしで0を返す():void
    {
        $owner = Owner::factory()->withShop()->create();
        $item = Item::factory()->for($owner->shop)->create();
        $img1 = ItemImage::factory()->for($item)->create(['sort_order' => 1]);
        $img2 = ItemImage::factory()->for($item)->create(['sort_order' => 2]);
        $img3 = ItemImage::factory()->for($item)->create(['sort_order' => 3]);
        $avalidated = [
            'item_image_ids' => [$img1->id, $img2->id, $img3->id, null],
            'filenames' => [$img1->filename, $img2->filename, $img3->filename, null],
            'def_sort' => [1, 2, 3, 4],
            'sort_order' => [1, 2, 3, 4]
        ];

        $result = $this->service->storeOrCreateItemImage($avalidated, $item->id, $owner->id);

        $this->assertEquals(0, $result);
        $this->assertDatabaseCount('item_images', 3);
        $this->assertDatabaseHas('item_images', [
            'id' => $img1->id,
            'sort_order' => 1,
        ]);
    }

    #[Test]
    public function storeOrCreateItemImage_並び順のみ変更(): void
    {
        $owner = Owner::factory()->withShop()->create();
        $item = Item::factory()->for($owner->shop)->create();
        $img1 = ItemImage::factory()->for($item)->create(['sort_order' => 1]);
        $img2 = ItemImage::factory()->for($item)->create(['sort_order' => 2]);
        $img3 = ItemImage::factory()->for($item)->create(['sort_order' => 3]);

        $validated = [
            'item_image_ids' => [$img2->id, $img3->id, null, $img1->id],
            'filenames' => [$img2->filename, $img3->filename, null, $img1->filename],
            'def_sort' => [2, 3, 4, 1],
            'sort_order' => [1, 2, 3, 4]
        ];

        $result = $this->service->storeOrCreateItemImage($validated, $item->id, $owner->id);

        $this->assertEquals(3, $result);
        $this->assertDatabaseCount('item_images', 3);
        $this->assertDatabaseHas('item_images', [
                'id' => $img1->id, 'sort_order' => 3,
            ])->assertDatabaseHas('item_images', [
                'id' => $img2->id,
                'sort_order' => 1,
            ])->assertDatabaseHas('item_images', [
                'id' => $img3->id,
                'sort_order' => 2,
            ]);
    }
    #[Test]
    public function storeOrCreateItemImage_idがなくtmp画像があれば新規登録する(): void
    {
        $owner = Owner::factory()->withShop()->create();
        $item = Item::factory()->for($owner->shop)->create();
        $img1 = ItemImage::factory()->for($item)->create(['sort_order' => 1]);
        $validated = [
            'item_image_ids' => [$img1->id, null, null, null],
            'filenames' => [$img1->filename, null, null, null],
            'def_sort' => [1, 2, 3, 4],
            'sort_order' => [1, 2, 3, 4]
        ];
        session(['tmp_item_image.2' => 'tmp2.webp']);

        $mock = Mockery::mock(ImageService::class);
        $mock->shouldReceive('moveToUploads')->once()->andReturn('uploads/tmp2.webp');
        $service = new ItemImageService($mock);

        $result = $service->storeOrCreateItemImage($validated, $item->id, $owner->id);

        $this->assertEquals(1, $result);
        $this->assertDatabaseCount('item_images', 2)
            ->assertDatabaseHas('item_images', [
                'filename' => 'uploads/tmp2.webp',
                'sort_order' => 2,
            ]);
    }
    #[Test]
    public function storeOrCreateItemImage_新規登録と画像及び並び順を更新する(): void
    {
        $owner = Owner::factory()->withShop()->create();
        $item = Item::factory()->for($owner->shop)->create();
        $img1 = ItemImage::factory()->for($item)->create(['sort_order' => 1]);
        $img2 = ItemImage::factory()->for($item)->create(['sort_order' => 2]);
        $img3 = ItemImage::factory()->for($item)->create(['sort_order' => 3]);
        $img3->delete();

        $validated = [
            'item_image_ids' => [null, null, $img2->id, $img1->id],
            'filenames' => [null, null, $img2->filename, $img1->filename],
            'def_sort' => [4, 3, 2, 1],
            'sort_order' => [1, 2, 3, 4]
        ];
        session(['tmp_item_image.1' => 'tmp1.webp']);
        session(['tmp_item_image.3' => 'tmp3.webp']);

        $mock = Mockery::mock(ImageService::class);
        $mock->shouldReceive('moveToUploads')->once()->with('tmp3.webp', 'item-images', $owner->id)->andReturn('uploads/test3.webp');
        $mock->shouldReceive('moveToUploads')->once()->with('tmp1.webp', 'item-images', $owner->id)->andReturn('uploads/test1.webp');
        $mock->shouldReceive('deleteFile')->once()->with($img1->filename);
        $service = new ItemImageService($mock);

        $result = $service->storeOrCreateItemImage($validated, $item->id, $owner->id);

        $this->assertEquals(2, $result);
        $this->assertEquals(3, ItemImage::all()->count());
        $this->assertDatabaseHas('item_images', [
                'id' => $img1->id,
                'filename' => 'uploads/test1.webp',
                'sort_order' => 3,
            ])
            ->assertDatabaseHas('item_images', [
                'id' => $img2->id,
                'sort_order' => 2,
            ])
            ->assertDatabaseHas('item_images', [
                'filename' => 'uploads/test3.webp',
                'sort_order' => 1,
            ]);
    }

    #[Test]
    public function storeOrCreateItemImage_画像の移動に失敗しロールバック(): void
    {
        $owner = Owner::factory()->withShop()->create();
        $item = Item::factory()->for($owner->shop)->create();
        $img1 = ItemImage::factory()->for($item)->create(['sort_order' => 1]);
        $img2 = ItemImage::factory()->for($item)->create(['sort_order' => 2]);

        $validated = [
            'item_image_ids' => [$img1->id, $img2->id, null, null],
            'filenames' => [$img1->filename, $img2->filename, null, null],
            'def_sort' => [1, 2, 3, 4],
            'sort_order' => [1, 2, 3, 4]
        ];

        session(['tmp_item_image.1' => 'tmp1.webp']);
        session(['tmp_item_image.2' => 'tmp2.webp']);
        session(['tmp_item_image.3' => 'tmp3.webp']);

        $mock = Mockery::mock(ImageService::class);
        $mock->shouldReceive('moveToUploads')->with('tmp1.webp', 'item-images', $owner->id)
            ->once()->andReturn('upload/test1.webp');
        $mock->shouldReceive('moveToUploads')->with('tmp2.webp', 'item-images', $owner->id)
            ->once()->andReturn('upload/test2.webp');
        $mock->shouldReceive('moveToUploads')->with('tmp3.webp', 'item-images', $owner->id)
            ->andThrow(new ImageMoveException('Image move error'));
        $mock->shouldReceive('deleteFile')->atLeast()->once();

        $service = new ItemImageService($mock);

        try {
            $service->storeOrCreateItemImage($validated, $item->id, $owner->id);
            $this->fail('ImageMoveException was not thrown');

        } catch (ImageMoveException $e) {
            $this->assertDatabaseCount('item_images', 2)
                ->assertDatabaseHas('item_images', [
                    'id' => $img1->id,
                    'filename' => $img1->filename,
                    'sort_order' => $img1->sort_order
                ])
                ->assertDatabaseHas('item_images', [
                    'id' => $img2->id,
                    'filename' => $img2->filename,
                    'sort_order' => $img2->sort_order
                ]);
        }
    }

}
