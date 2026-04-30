<?php

namespace Database\Seeders;

use App\Models\Item;
use App\Support\AppLog;
use Carbon\Carbon;
use Exception;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManager;
use Log;

class ItemImageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (! $this->deleteS3AllFiles()) {
            throw new \Exception('S3商品画像の削除に失敗');
        }

        Item::with(['itemCategory.subCategory'])
            ->chunkById(80, function ($items) {
                try {
                    $sources = range(1, 20); // 元データの画像数
                    $now = now();
                    $batch = [];

                    foreach($items as $item) {
                        $insertData = $this->generateItemImage($item, $sources, $now);
                        foreach ($insertData as $data) {
                            $batch[] = $data;
                            if (count($batch) >= 100) {
                                DB::table('item_images')->insert($batch);
                                $batch = [];
                            }
                        }
                    }

                    if (! empty($batch)) {
                        DB::table('item_images')->insert($batch);
                    }

                } catch(\Throwable $e) {
                    AppLog::error($e->getMessage(), $e);
                    if (! $this->deleteS3AllFiles()) {
                        Log::error('ダミー画像生成に失敗し更にS3の掃除にも失敗');
                    }
                    throw $e;
                }
            });
    }

    private function generateItemImage(Item $item, array $sources, Carbon $now): array
    {
        $itemId = $item->id;
        $categoryId = $item->itemCategory->subCategory->category_id;
        $isMug = (int)$categoryId === 1;
        $categoryPrefix = $isMug ? 'mug' : 'towel';
        $imageCount = rand(3, 4);

        // 画像が重複しない様に調整
        $used = collect($sources)->shuffle()->take($imageCount)->all();

        $insertData = [];

        foreach($used as $i => $num) {
            $originalFilename = "{$categoryPrefix}{$num}.jpg";
            $newFilename = "{$categoryPrefix}_{$itemId}_" . ($i + 1) . ".webp";

            $from = public_path("images/{$originalFilename}");

            $path = $this->storeImage($from, $originalFilename, $newFilename);

            $insertData[] = [
                'item_id' => $itemId,
                'filename' => $path,
                'sort_order' => $i + 1,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        return $insertData;
    }

    private function storeImage(string $from, string $originalFilename, string $newFilename): string
    {
        if (! file_exists($from)) {
            throw new \Exception("元画像が存在しません: {$from}");
        }

        $content = file_get_contents($from);
        if ($content === false) {
            throw new \Exception("file_get_contents失敗: {$from}");
        }

        $resized = app(ImageManager::class)->read($content)->scale(width:500)->toWebp(80);
        $path = "uploads/item-images/{$newFilename}";
        $result = Storage::put($path, (string) $resized);

        if (! $result) {
            throw new \Exception("❎{$originalFilename}:画像ファイルのコピーに失敗");
        }

        return $path;
    }


    // S3に保存している商品画像を全削除
    private function deleteS3AllFiles():bool
    {
        $files = Storage::allFiles('uploads/item-images');

        if (! empty($files)) {
            if (! Storage::delete($files)) {
            return false;
            }
        }
        
        return true;
    }

}
