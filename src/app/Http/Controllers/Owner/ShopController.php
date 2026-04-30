<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreShopRequest;
use App\Http\Requests\UpdateShopRequest;
use App\Models\Owner;
use App\Models\Shop;
use App\Services\Owner\ShopService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class ShopController extends Controller
{
    public function __construct(private ShopService $shopService)
    {
    }

    public function index()
    {
        $owner = Owner::findOrFail(Auth::id());

        return view('owner.home', ['owner' => $owner]);
    }

    public function create()
    {
        Gate::authorize('create', Shop::class);
        return view('owner.shop.create');
    }

    public function store(StoreShopRequest $request)
    {
        Gate::authorize('create', Shop::class);

        $validated = $request->validated();
        $tmpImagePath = session()->pull('tmp_image');

        try {
            $this->shopService->create($validated, $tmpImagePath, Auth::id());

        } catch (\Throwable $e) {
            return back()->with([
                'status' => 'alert',
                'message' => 'システムエラーが発生しました。お手数ですが時間をおいて再度お試しください。'
            ]);
        }

        return to_route('owner.shop.index')->with([
            'status' => 'info',
            'message' => '新規ショップを登録しました'
        ]);
    }

    public function edit(Shop $shop)
    {
        Gate::authorize('update', $shop);

        return view('owner.shop.edit', ['shop' => $shop]);
    }

    public function update(UpdateShopRequest $request, Shop $shop)
    {
        Gate::authorize('update', $shop);

        $tmpImagePath = session()->pull('tmp_image');
        $shop->fill($request->validated());

        if(! $tmpImagePath && $shop->isClean()) {
            return back()->with([
                'status' => 'alert',
                'message' => '登録された内容に変更がありません、お手数ですが再度入力願います'
            ]);
        }

        try {
            $this->shopService->update($shop, $tmpImagePath, Auth::id());

        } catch (\Throwable $e) {
           return back()->with([
                'status' => 'alert',
                'message' => 'システムエラーが発生しました。お手数ですが時間をおいて再度お試しください。'
            ]);
        }

        return to_route('owner.shop.index')->with([
            'status' => 'info',
            'message' => 'ショップ情報を更新しました'
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    // public function destroy(string $id)
    // {
    //     //
    // }
}
