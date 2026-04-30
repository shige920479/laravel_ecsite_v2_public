<?php
namespace App\Services\Owner;

use App\Exceptions\ImageMoveException;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;
use Str;

class ImageService
{
  public function __construct(
    private ImageManager $imageManager,
    private ImagePathService $imagePathService,
    )
  {
  }

  /**
   * 画像ファイルをリサイズ -> tmp/***.webp で保存 -> パスを返す
   */
  public function resizeAndSaveTmp($file): string
  {
    $filename = Str::uuid() . '.webp';
    $tmpPath = $this->imagePathService->tmp($filename);

    $resized = $this->imageManager->read($file)->scale(width:500)->toWebp(80);

    Storage::put($tmpPath, (string)$resized);

    return $tmpPath;
  }

  /**
   * tmp/***.webp を uploads/<subDir>/***.webpへ画像を移動->パスを返す
   */
  public function moveToUploads(string $tmpPath, string $subDir, int $id): string
  {
    if (! Storage::exists($tmpPath)) {
      throw new ImageMoveException("❎画像ファイルなし: {$tmpPath}, owner_id: {$id} ");
    }

    $filename = basename($tmpPath);
    $storagePath = $this->imagePathService->uploads($subDir, $filename);

    if (! Storage::move($tmpPath, $storagePath)) {
      throw new ImageMoveException("❎画像の保存に失敗: {$tmpPath}=>{$storagePath}, owner_id: {$id}");
    }

    return $storagePath;
  }

  /**
   * パスを指定し画像ファイルを削除
   */
  public function deleteFile(string|array $filePath): void
  {
    $paths = array_filter((array)$filePath); // 空やnull等のノイズ除去

    if ($paths === []) {
        return;
    }

    if (! Storage::delete($paths)) {
        Log::info("⚠️ 画像ファイルの削除失敗", [
            'paths' => $paths,
        ]);
    }
  }
}