<?php

namespace Tests\Unit\Services\Owner;

use App\Exceptions\ImageMoveException;
use App\Services\Owner\ImagePathService;
use App\Services\Owner\ImageService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;
use PHPUnit\Framework\Attributes\Test;
// use PHPUnit\Framework\TestCase;
use Tests\TestCase;

class ImageServiceTest extends TestCase
{
    private ImageService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new ImageService(
            new ImageManager(new Driver()),
            new ImagePathService()
        );
        Storage::fake('public');
    }

    #[Test]
    public function resizeAndSaveTmp_リサイズしtmpへ保存後パスを返す(): void
    {
        $file = UploadedFile::fake()->image('test.png', 1000, 1000);

        $filePath = $this->service->resizeAndSaveTmp($file);

        Storage::assertExists($filePath);

        $path = Storage::path($filePath);
        $this->assertEquals('image/webp', File::mimeType($path));

        $this->assertStringStartsWith("tmp/", $filePath);
        $this->assertStringEndsWith(".webp", $filePath);
    }

    #[Test]
    public function moveToUploads_ファイルを移動しパスを返す(): void
    {
        $id = 1;
        Storage::put("tmp/test.webp", 'dummy-content');

        $res = $this->service->moveToUploads("tmp/test.webp", 'shops', $id);

        $this->assertStringStartsWith("uploads/shops", $res);
        $this->assertStringEndsWith(".webp", $res);
        Storage::assertExists($res);
        Storage::assertMissing("tmp/test.webp");
    }

    #[Test]
    public function moveToUploads_画像ファイルがなく例外をスロー(): void
    {
        $id = 1;
        $tmpPath = 'tmp/1/test.webp'; // ファイル無
        $this->expectException(ImageMoveException::class);
        $this->expectExceptionMessage("画像ファイルなし: {$tmpPath}, owner_id: {$id} ");

        $this->service->moveToUploads($tmpPath, 'shops', $id);
    }

    #[Test]
    public function deleteFile_指定した画像ファイルを削除(): void
    {
        $filePath1 = "tmp/test1.webp";
        $filePath2 = "tmp/test2.webp";
        Storage::put($filePath1, 'dummy-content');
        Storage::put($filePath2, 'dummy-content');

        $this->service->deleteFile($filePath1);

        Storage::assertMissing($filePath1);
        Storage::assertExists($filePath2);
    }

    #[Test]
    public function deleteFile_配列で渡した画像ファイルを削除(): void
    {
        $filePath1 = "tmp/test1.webp";
        $filePath2 = "tmp/test2.webp";
        $filePath3 = "tmp/test3.webp";
        Storage::put($filePath1, 'dummy-content');
        Storage::put($filePath2, 'dummy-content');
        Storage::put($filePath3, 'dummy-content');

        $this->service->deleteFile([$filePath1, $filePath2]);

        Storage::assertMissing($filePath1);
        Storage::assertMissing($filePath2);
        Storage::assertExists($filePath3);
    }
    
}
