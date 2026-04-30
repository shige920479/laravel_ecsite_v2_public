<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Http\Requests\ImageRequest;
use App\Services\Owner\ImageService;
use App\Support\AppLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class TmpImageController extends Controller
{
    public function __construct(private ImageService $imageService)
    {
    }

    public function store(ImageRequest $request)
    {
        $index = $request->input('index');
        $key = is_null($index)
            ? 'tmp_image'
            : 'tmp_item_image.' . $index;

        try {
            $oldImagePath = session($key);
            $filePath = $this->imageService->resizeAndSaveTmp($request->file('image'));
            session([$key => $filePath]);
            if($oldImagePath) $this->imageService->deleteFile($oldImagePath);

            return response()->json(['path' => Storage::url($filePath) ], 200);

        } catch (\Throwable $e) {
            AppLog::error('画像のアップロード失敗', $e);
            return response()->json([
                'error' => ['message' => '処理中にエラーが発生しました']
            ], 500);
        }
    }

    public function deleteTmpImage(int $index)
    {
        $key = 'tmp_item_image.' . $index;
        $deletePath = session()->pull($key);
        if($deletePath) $this->imageService->deleteFile($deletePath);

        return response()->noContent();
    }
}
