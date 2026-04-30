<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreItemImageRequest;
use App\Http\Requests\UpdateItemImageRequest;
use App\Models\Item;
use App\Models\ItemImage;
use App\Services\Owner\ImageService;
use App\Services\Owner\ItemImageService;
use App\Support\AppLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class ItemImageController extends Controller
{
    public function __construct(private ItemImageService $itemImageService)
    {
    }

    public function create(Item $item)
    {
        Gate::authorize('view', $item);
        return view('owner.images.create', ['item' => $item]);
    }

    public function store(StoreItemImageRequest $request, Item $item) // requestは画像有無の判定用
    {
        Gate::authorize('update', $item);
        try {
            $this->itemImageService->storeItemImages($item, $request->user()->id);
            
        } catch (\Throwable $e) {
            AppLog::error($e->getMessage(), $e);
            return back()->with([
                'status' => 'alert',
                'message' => 'システムエラーが発生しました。お手数ですが時間をおいて再度お試しください。'
            ]);
            
        } finally {
            session()->forget('tmp_item_image');
        }
            
        return to_route('owner.item.stock.create', ['item' => $item])->with([
            'status' => 'info',
            'message' => '商品画像を登録しました。引き続き在庫数を登録してください。'
        ]);
    }

    public function edit(Item $item)
    {
        Gate::authorize('update', $item);
        $itemImages = ItemImage::where('item_id', $item->id)->orderBy('sort_order', 'asc')->get();

        return view('owner.images.edit', [
            'item' => $item,
            'itemImages' => $itemImages,
        ]);
    }

    public function update(UpdateItemImageRequest $request, Item $item)
    {
        Gate::authorize('update', $item);
        $validated = $request->validated();
        try {
            $count = $this->itemImageService->storeOrCreateItemImage($validated, $item->id, Auth::id());
            if ($count === 0) {
                if (! $request->boolean('has-deleted')) {
                    $message = '登録情報が更新されておりません、再度入力し直しください';
                } else {
                    $message = '商品には必ず画像ファイルを最低1つ登録願います';
                }
                return back()->withErrors(['images' => $message]);
            }

            return to_route('owner.item.index')->with([
                'status' => 'info',
                'message' => "商品番号: {$item->id} 商品名: {$item->name} の画像登録を更新しました"
            ]);

        } catch (\Throwable $e) {
            AppLog::error($e->getMessage(), $e);
            return back()->withErrors([
                'images' => 'システムエラーです、少し時間をおいてから再度お試しください'
            ]);

        } finally {
            session()->forget('tmp_item_image');
        }
    }

    public function destroy(ItemImage $itemImage, ImageService $imageService)
    {
        Gate::authorize('delete', $itemImage);

        $filePath = $itemImage->filename;
        
        try {
            $itemImage->delete();
            $imageService->deleteFile($filePath);

        } catch (\Throwable $e) {
            AppLog::error('画像情報のDB削除に失敗', $e);
            return response()->json([
                'error' => '画像削除に失敗しました、お手数ですが再度お試しください'
            ], 500);
        }

        return response()->json(['message' => '登録画像を削除しました'], 200);
    }
}
