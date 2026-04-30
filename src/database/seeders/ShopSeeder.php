<?php

namespace Database\Seeders;

use App\Models\Owner;
use App\Support\AppLog;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManager;
use RuntimeException;

class ShopSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (! $this->deleteS3AllFiles()) {
            throw new \Exception('S3ショップ画像の削除に失敗');
        }

        $owners = Owner::query()->get();
        $shopsData = [];

        try {
            foreach($owners as $owner) {

                $from = public_path("images/shop{$owner->id}.jpg");

                if (! file_exists($from)) {
                    throw new \Exception("ショップの元画像が存在しません: {$from}");
                }

                $content = file_get_contents($from);
                if ($content === false) {
                    throw new \Exception("file_get_contents失敗: {$from}");
                }

                $resized = app(ImageManager::class)->read($content)->scale(width:500)->toWebp(80);
                $path = "uploads/shops/shop{$owner->id}.webp";

                $result = Storage::put($path, $resized);
                if (! $result) {
                    throw new \Exception("shop{$owner->id}.jpgのコピーに失敗");
                }

                $shopsData[] = [
                    'owner_id' => $owner->id,
                    'name' => "{$owner->name}-shop",
                    'information' => "{$owner->name}-shopの公式オンラインショップです。\n"
                        . "{$owner->name}-shopでしか手に入らないこだわりの\n"
                        . "オリジナルデザインの商品を取り揃えています。",
                    'filename' => $path,
                ];
            }

            DB::table('shops')->insert($shopsData);

        } catch (\Throwable $e) {
            AppLog::error($e->getMessage(), $e);
            if (! $this->deleteS3AllFiles()) {
                Log::error('ダミー画像生成に失敗し更にS3の掃除にも失敗');
            }
            throw $e;
        }
    }

    // S3に保存しているショップ画像を全削除
    private function deleteS3AllFiles():bool
    {
        $files = Storage::allFiles('uploads/shops');

        if (! empty($files)) {
            if (! Storage::delete($files)) {
            return false;
            }
        }
        
        return true;
    }
}
