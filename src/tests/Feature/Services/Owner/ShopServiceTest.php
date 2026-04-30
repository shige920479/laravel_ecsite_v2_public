<?php

namespace Tests\Feature\Services\Owner;

use App\Exceptions\ImageMoveException;
use App\Models\Item;
use App\Models\Owner;
use App\Models\Shop;
use App\Services\Owner\ImagePathService;
use App\Services\Owner\ImageService;
use App\Services\Owner\ShopService;
use Exception;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;
use Mockery;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ShopServiceTest extends TestCase
{
    use RefreshDatabase;
    private ShopService $shopService;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
        $this->shopService = new ShopService(
            new ImageService(
                new ImageManager(new Driver()), new ImagePathService()
                )
        );
    }

    #[Test]
    public function create_ショップ情報を登録できる(): void
    {
        $owner = Owner::factory()->create();
        $tmpPath = "tmp/test.webp";
        Storage::put($tmpPath, 'dummy-content');
        session(['tmp_image' => $tmpPath]);

        $input = [
            'name' => 'owner1-shop',
            'information' => 'info',
            'is_selling' => 1,
        ];

        $this->shopService->create($input, $tmpPath, $owner->id);

        $this->assertDatabaseHas('shops', [
            'owner_id' => $owner->id,
            'name' => 'owner1-shop',
        ]);

        Storage::assertExists('uploads/shops/test.webp');
        Storage::assertMissing("tmp/*");
    }

    #[Test]
    public function create_画像保存に失敗しImageMove例外をスロー(): void
    {
        $input = [];
        $ownerId = 1;
        $tmpPath = 'tmp/test.webp';
        $serviceMock = Mockery::mock(ImageService::class);
        $serviceMock->shouldReceive('moveToUploads')->andThrow(new ImageMoveException());
        $this->expectException(ImageMoveException::class);

        $service = new ShopService($serviceMock);
        $service->create($input, $tmpPath, $ownerId);
    }

    #[Test]
    public function update_画像保存しDBを更新(): void
    {
        $owner = Owner::factory()->create();
        // 更新前のshop情報
        $oldPath = "uploads/shops/old.webp";
        Storage::put($oldPath, 'dummy-content');
        $shop = Shop::factory()->for($owner)->create([
            'filename' => $oldPath,
        ]);
        // 更新情報
        $tmpPath = "tmp/new.webp";
        Storage::put($tmpPath, 'dummy-content');
        session(['tmp_image' => $tmpPath]);

        $this->shopService->update($shop, $tmpPath, $owner->id);

        $this->assertDatabaseCount('shops', 1);
        $this->assertDatabaseHas('shops', [
            'name' => $shop->name,
            'filename' => 'uploads/shops/new.webp'
        ]);
        Storage::assertExists('uploads/shops/new.webp');
        Storage::assertMissing($oldPath);
        Storage::assertMissing("tmp/*");
    }
    #[Test]
    public function update_画像保存に失敗し例外をスロー(): void
    {
        $owner = Owner::factory()->create();
        // 更新前のshop情報
        $oldPath = "uploads/shops/old.webp";
        Storage::put($oldPath, 'dummy-content');
        $shop = Shop::factory()->for($owner)->create([
            'filename' => $oldPath,
        ]);

        $tmpPath = 'tmp/test.webp';

        $serviceMock = Mockery::mock(ImageService::class);
        $serviceMock->shouldReceive('moveToUploads')->andThrow(new ImageMoveException());
        $this->expectException(ImageMoveException::class);

        $service = new ShopService($serviceMock);
        $service->update($shop, $tmpPath, $owner->id);

        $this->assertDatabaseCount('shops', 1)
            ->assertDatabaseHas('shops', [
                'name' => $shop->name,
                'information' => $shop->information,
                'flename' => $shop->filename,
                'is_selling' => $shop->is_selling
            ]);
        Storage::assertExists($oldPath);
    }
    #[Test]
    public function update_画像保存に失敗し例外をスローしロールバック(): void
    {
        $owner = Owner::factory()->create();
        // 更新前のshop情報
        $oldPath = "uploads/shops/old.webp";
        Storage::put($oldPath, 'dummy-content');
        $shop = Shop::factory()->for($owner)->create([
            'filename' => $oldPath,
        ]);

        $tmpPath = 'tmp/test.webp';

        $serviceMock = Mockery::mock(ImageService::class);
        $serviceMock->shouldReceive('moveToUploads')->andThrow(new ImageMoveException());

        $service = new ShopService($serviceMock);

        try {
            $service->update($shop, $tmpPath, $owner->id);
            $this->fail('強制的に例外発生');

        } catch (\Throwable $e) {
            $this->assertDatabaseCount('shops', 1)
                ->assertDatabaseHas('shops', [
                    'name' => $shop->name,
                    'information' => $shop->information,
                    'filename' => $shop->filename,
                    'is_selling' => $shop->is_selling
                ]);
            Storage::assertExists($oldPath);
        }


    }
}
