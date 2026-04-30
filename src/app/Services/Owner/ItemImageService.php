<?php
namespace App\Services\Owner;

use App\Models\Item;
use App\Models\ItemImage;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Throwable;

class ItemImageService
{
    public function __construct(private ImageService $imageService)
    {
    }

    /**
     * 商品画像の新規登録処理（画像の移動とDB登録）
     */
    public function storeItemImages(Item $item, int $ownerId): void
    {
        // sort_order順に並べ替え（keyでソートし、更にindexをゼロからふり直し）
        $tmpImagePaths = collect(session()->pull('tmp_item_image', []))->sortKeys()->values();
        $movedFiles = [];

        try {
            DB::transaction(function () use ($tmpImagePaths, $item, $ownerId, &$movedFiles) {

                foreach($tmpImagePaths as $index => $path) {
                    $filename = $this->imageService->moveToUploads($path, 'item-images', $ownerId);
                    $movedFiles[] = $filename;

                    ItemImage::create([
                        'item_id' => $item->id,
                        'filename' => $filename,
                        'sort_order' => $index + 1
                    ]);
                }
            });

        } catch (\Throwable $e) {
            $this->resetUpload($movedFiles);
            throw $e;
        }
    }

    /**
     * 商品画像の更新処理：更新or登録or変更無を判定し実行数を返却
     */
    public function storeOrCreateItemImage(array $validated, int $itemId, int $ownerId): int
    {
        $inputs = $this->normalizeValited($validated, $itemId);
        $validInputs = $this->sortAndFilterForImage($inputs);
        if ($validInputs->isEmpty()) return 0;

        $execCount = 0;
        $movedFiles = []; // tmp=>uploads/item-imagesに移動したファイル:ロールバック時に削除

        try {
            DB::transaction(function () use ($validInputs, $ownerId, &$execCount, &$movedFiles) {

                foreach ($validInputs as $i => $input) {
                    if ($this->handleInput($input, $ownerId, $i, $movedFiles)) {
                        $execCount++;
                    }
                }
            });

            return $execCount;

        } catch (\Throwable $e) {
            $this->resetUpload($movedFiles);
            throw $e;

        }
    }

    protected function handleInput(array $input,  int $ownerId, int $i, array &$movedFiles): bool
    {
        if ($this->isNew($input)) {
            return $this->createImage($input, $ownerId, $i, $movedFiles);
        }
        if ($this->isSkip($input, $i)) {
            return false;
        }
        if ($this->isSortOnly($input)) {
            return $this->updateSort($input, $i);
        }
        return $this->updateImage($input, $ownerId, $i, $movedFiles);
    }

    protected function isNew(array $input): bool
    {
        return is_null($input['id']);
    }

    protected function isSkip(array $input, int $i): bool
    {
        return is_null($input['tmp_image']) && (int)$input['def_sort'] === $i + 1;
    }

    protected function isSortOnly(array $input): bool
    {
        return is_null($input['tmp_image']);
    }

    protected function updateSort(array $input, int $i): bool
    {
        ItemImage::findOrFail($input['id'])->update(['sort_order' => $i + 1]);

        return true;
    }

    protected function createImage(array $input, int $ownerId, int $i, array &$movedFiles)
    {
        $filename = $this->imageService->moveToUploads($input['tmp_image'], 'item-images', $ownerId);
        $movedFiles[] = $filename;
        $input['filename'] = $filename;

        ItemImage::create([
            'item_id' => $input['item_id'],
            'filename' => $filename,
            'sort_order' => $i + 1,
        ]);

        return true;
    }

    protected function updateImage(array $input, int $ownerId, int $i, array &$movedFiles)
    {
        $image = ItemImage::findOrFail($input['id']);
        $oldFile = $image->filename;

        $filename = $this->imageService->moveToUploads($input['tmp_image'], 'item-images', $ownerId);
        $movedFiles[] = $filename;
        ItemImage::findOrFail($input['id'])->update([
            'filename' => $filename,
            'sort_order' => $i + 1,
        ]);

        if ($oldFile) {
            $this->imageService->deleteFile($oldFile);
        }
        
        return true;
    }

    /**
     * 一括で受け取ったリクエストデータをDB処理用に整形（再配列化）
     */
    protected function normalizeValited(array $array, int $itemId): array
    {
        $inputs = [];

        foreach ($array['sort_order'] as $i => $order) {

            $tmpImagePath = session('tmp_item_image.' . $array['def_sort'][$i]);

            $inputs[] = [
                'id' => $array['item_image_ids'][$i],
                'item_id' => $itemId,
                'filename' => $array['filenames'][$i],
                'def_sort' => $array['def_sort'][$i],
                'tmp_image' => $tmpImagePath,
                'sort_order' => $order,
            ];
        }

        return $inputs;
    }

    /**
     * DB処理用に整形したデータを並べ替え、tmpにもDBにも画像が無いものを排除
     */
    protected function sortAndFilterForImage(array $inputs): Collection
    {
        return collect($inputs)
            ->sortBy('sort_order')
            ->filter(fn ($input) => $input['tmp_image'] !== null || $input['filename'] !== null)
            ->values();
    }
    
    /**
     * ロールバック時にアップロード済みのファイルを削除
     */
    protected function resetUpload(array $files): void
    {
        if (! empty($files)) {
            $this->imageService->deleteFile($files);
        }
    }

}