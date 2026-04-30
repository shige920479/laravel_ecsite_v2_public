<?php

namespace Tests\Feature\Controllers;

use App\Models\Owner;
use App\Services\Owner\ImageService;
use Exception;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Mockery;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class TmpImageControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
    }

    #[Test]
    public function store_ショップ用画像ファイルを一時保存する(): void
    {
        $owner = Owner::factory()->create();
        $file = UploadedFile::fake()->image('test.png', 600, 400);

        $mock = Mockery::mock(ImageService::class);
        $mock->shouldReceive('resizeAndSaveTmp')->once()->andReturn('tmp/test.webp');
        $this->app->instance(ImageService::class, $mock);

        $res = $this->actingAs($owner, 'web_owner')->postJson(route('owner.tmp.image.store'), [
            'image' => $file
        ]);

        $res->assertStatus(200)
            ->assertJson([
                'path' => Storage::url('tmp/test.webp')
            ])
            ->assertSessionHasAll([
                'tmp_image' => 'tmp/test.webp'
            ]);
    }
    #[Test]
    public function store_ショップ用画像ファイルを一時保存して旧画像を削除(): void
    {
        $owner = Owner::factory()->create();
        $oldImagePath = "tmp/old-image.webp";
        session(['tmp_image' => $oldImagePath]);
        
        $file = UploadedFile::fake()->image('new-image.png', 600, 400);

        $mock = Mockery::mock(ImageService::class);
        $mock->shouldReceive('resizeAndSaveTmp')->once()->andReturn("tmp/new-image.webp");
        $mock->shouldReceive('deleteFile')->once();
        $this->app->instance(ImageService::class, $mock);

        $res = $this->actingAs($owner, 'web_owner')->postJson(route('owner.tmp.image.store'), [
            'image' => $file
        ]);

        $res->assertStatus(200)
            ->assertJson([
                'path' => Storage::url("tmp/new-image.webp")
            ])
            ->assertSessionHasAll([
                'tmp_image' => "tmp/new-image.webp"
            ]);
    }
    #[Test]
    public function store_商品用画像ファイルを一時保存する(): void
    {
        $owner = Owner::factory()->create();
        $file = UploadedFile::fake()->image('test.png', 600, 400);

        $mock = Mockery::mock(ImageService::class);
        $mock->shouldReceive('resizeAndSaveTmp')->once()->andReturn('tmp/test.webp');
        $this->app->instance(ImageService::class, $mock);

        $res = $this->actingAs($owner, 'web_owner')->postJson(route('owner.tmp.image.store'), [
            'image' => $file,
            'index' => 2
        ]);

        $res->assertStatus(200)
            ->assertJson([
                'path' => Storage::url('tmp/test.webp')
            ])
            ->assertSessionHasAll([
                'tmp_item_image.2' => 'tmp/test.webp'
            ]);
    }
    #[Test]
    public function store_商品用画像ファイルを一時保存して旧画像を削除(): void
    {
        // ここはモックを外す
        $owner = Owner::factory()->create();
        $oldFile = UploadedFile::fake()->image("old-image.png", 600, 400);
        $oldImagePath = "tmp/old-image.webp";
        Storage::put($oldImagePath, (string)$oldFile);

        session(['tmp_item_image.3' => $oldImagePath]);
        session(['tmp_item_image.1' => 'dummy.webp']);
        
        $newFile = UploadedFile::fake()->image('new-image.png', 600, 400);

        $res = $this->actingAs($owner, 'web_owner')->postJson(route('owner.tmp.image.store'), [
            'image' => $newFile,
            'index' => 3
        ]);

        $newImagePath = session('tmp_item_image.3');
        $res->assertStatus(200)
            ->assertJson([
                'path' => Storage::url($newImagePath)
            ])
            ->assertSessionHasAll([
                'tmp_item_image.3' => $newImagePath,
                'tmp_item_image.1' => 'dummy.webp',

            ]);
        Storage::assertExists($newImagePath);
        Storage::assertMissing($oldImagePath);
    }

    #[Test]
    public function store_アップロードに失敗しエラーメッセージを返す(): void
    {
        $owner = Owner::factory()->create();
        $file = UploadedFile::fake()->image('error.png');

        $mock = Mockery::mock(ImageService::class);
        $mock->shouldReceive('resizeAndSaveTmp')->once()->andThrow(new Exception('Upload-error'));
        $this->app->instance(ImageService::class, $mock);

        $res = $this->actingAs($owner, 'web_owner')->postJson(route('owner.tmp.image.store'), [
            'image' => $file
        ]);

        $res->assertStatus(500)
            ->assertJson([
            'error' => ['message' => '処理中にエラーが発生しました']
            ]);
    }

    #[Test]
    public function deleteTmpImage_一時保存した画像を削除する(): void
    {
        $owner = Owner::factory()->create();

        session(['tmp_item_image.1' => 'tmp/test1.webp']);
        session(['tmp_item_image.2' => 'tmp/test2.webp']);

        $mock = Mockery::mock(ImageService::class);
        $mock->shouldReceive('deleteFile')->once()->andReturnNull();
        $this->app->instance(ImageService::class, $mock);

        $res = $this->actingAs($owner, 'web_owner')
            ->deleteJson(route('owner.tmp.itemImage.delete', ['index' => 1]));

        $res->assertStatus(204)
            ->assertSessionMissing('tmp_item_image.1');
    }
    #[Test]
    public function deleteTmpImage_存在しないindexでもエラーにならない(): void
    {
        $owner = Owner::factory()->create();

        $res = $this->actingAs($owner, 'web_owner')
            ->deleteJson(route('owner.tmp.itemImage.delete', ['index' => 999]));

        $res->assertNoContent();
    }

}
