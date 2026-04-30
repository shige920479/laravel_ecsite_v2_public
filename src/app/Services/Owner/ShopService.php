<?php
namespace App\Services\Owner;

use App\Exceptions\ImageMoveException;
use App\Models\Shop;
use App\Support\AppLog;
use Exception;
use Illuminate\Support\Facades\Auth;
use Throwable;

class ShopService
{
  public function __construct(private ImageService $imageService)
  {
  }

  /**
   * ショップ情報の新規登録(画像保存＋DB登録)
   */
  public function create(array $validated, string $tmpImagePath, int $ownerId): void
  {
    $filename = null;

    try {
      $filename = $this->imageService->moveToUploads($tmpImagePath, 'shops', $ownerId);

      $validated['filename'] = $filename;
      $validated['owner_id'] = $ownerId;
      Shop::create($validated);

    // uploadエラー
    } catch (ImageMoveException $e) {
      AppLog::error('画像の保存に失敗: ', $e);
      throw $e;

    // DBエラー
    } catch(\Exception $e) {
      if ($filename) {
          $this->imageService->deleteFile($filename);
      }
      AppLog::error('システムエラー: ', $e);
      throw $e;
      
    } 
  }

  /**
   * ショップ情報の更新(画像保存＋DB登録)
   */
  public function update(Shop $shop, ?string $tmpImagePath, int $ownerId) :void
  {
    $oldImagePath = $shop->getOriginal('filename');

    try {
      if($tmpImagePath) {
          $filename = $this->imageService->moveToUploads($tmpImagePath, 'shops', $ownerId);
          $shop->filename = $filename;
      }

      $shop->save();

      if($tmpImagePath && $oldImagePath) {
        $this->imageService->deleteFile($oldImagePath);
      }

    // uploadエラー
    } catch (ImageMoveException $e) {
      AppLog::error('画像の保存に失敗: ', $e);
      throw $e;

    // DBエラー
    } catch (\Exception $e) {
      if($filename) {
          $this->imageService->deleteFile($filename);
      }
      AppLog::error('システムエラー: ', $e);
      throw $e;

    }
  }
}