<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Http\Requests\SearchItemRequest;
use App\Http\Requests\StoreItemRequest;
use App\Http\Requests\UpdateItemRequest;
use App\Models\Category;
use App\Models\Item;
use App\Models\ItemCategory;
use App\Models\Owner;
use App\Models\Shop;
use App\Policies\ItemPolicy;
use App\Services\Owner\Csv\CsvExportService;
use App\Services\Owner\Csv\ItemCsvDefinition;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class ItemController extends Controller
{
    public function index(SearchItemRequest $request)
    {
        /** @var \App\Models\Owner $owner */
        $owner = Auth::user();
        $query = Item::query();
        $query = $this->indexQuery($query, $request, $owner);
        $items = $query->paginate(10)->withQueryString();

        return view('owner.items.index', ['items' => $items]);
    }

    public function create()
    {
        $input = session()->pull('item', []);
        $categories = Category::with(['subCategories.itemCategories'])->get();

        return view('owner.items.create', [
            'shop' => Auth::user()->loadMissing('shop')->shop,
            'categories' => $categories,
            'is_selling' => (int) oldSessionOrModel('is_selling', $input ?? null, null, 1),
            'input' => $input,
        ]);
    }

    public function confirm(StoreItemRequest $request) 
    {
        session(['item' => $request->validated()]);
        
        return view('owner.items.confirm', [
            'item' => session('item'),
            'shopName' => Shop::findOrFail($request->shop_id)->name,
            'categoryName' => ItemCategory::findOrFail($request->item_category_id)->name,
        ]);
    }

    public function store()
    {
        Gate::authorize('create', Item::class);
        $input = session()->pull('item', []);
        if (empty($input)) {
            return to_route('owner.item.create')->with([
                'status' => 'alert',
                'message' => '入力情報が確認できません、再度入力願います'
            ]);
        }

        $item = Item::create($input);
        
        return to_route('owner.item.image.create', ['item' => $item])->with([
            'status' => 'info',
            'message' => '商品情報を登録しました。引き続き商品画像を登録願います'
        ]);
    }

    public function edit(Item $item)
    {
        Gate::authorize('view', $item);

        $input = session()->pull('update_item', []);
        $categories = Category::query()->withTree()->get();

        return view('owner.items.edit', [
            'item' => $item,
            'input' => $input,
            'categories' => $categories
        ]);
    }

    public function updateConfirm(UpdateItemRequest $request, Item $item)
    {
        Gate::authorize('update', $item);

        $item->fill($request->validated());
        if ($item->isClean()) {
            return back()->with([
                'status' => 'alert',
                'message' => '変更内容がありません、入力内容をご確認ください'
            ])->withInput();
        }

        session(['update_item' => $request->validated()]);

        return view('owner.items.confirm', [
            'item' => $item,
            'shopName' => $item->shop->name,
            'categoryName' => $item->itemCategory->name
        ]);
    }

    public function update(Item $item)
    {
        Gate::authorize('update', $item);

        $input = session()->pull('update_item');
        if (empty($input)) {
            return to_route('owner.item.edit', ['item' => $item])->with([
                'status'  => 'alert',
                'message' => '更新内容が見つかりませんでした。もう一度操作してください。'
            ]);
        }

        $item->update($input);

        return to_route('owner.item.index')->with([
            'status' => 'info',
            'message' => '商品情報を更新しました'
        ]);
    }

    public function toggleIsSelling(Item $item)
    {
        Gate::authorize('update', $item);

        $status = $item->is_selling ? '販売停止' : '販売中';

        $item->update(['is_selling' => ! $item->is_selling]);

        return redirect()->back(fallback: route('owner.item.index'))->with([
            'status' => 'info',
            'message' => "【商品No:{$item->id} {$item->name}】を{$status}にしました"
        ]);
    }

    public function destroy(Item $item)
    {
        Gate::authorize('delete', $item);

        $item->delete();

        return to_route('owner.item.index')->with([
            'status' => 'info',
            'message' => '商品を1点削除しました'
        ]);
    }

    public function downloadCsvItem(SearchItemRequest $request, CsvExportService $service)
    {
        /** @var \App\Models\Owner $owner */
        $owner = Auth::user();
        $query = Item::query();
        $query = $this->indexQuery($query, $request, $owner);

        return $service->download($query, new ItemCsvDefinition());
    }

    private function indexQuery(Builder $query, SearchItemRequest $request, Owner $owner): Builder
    {
        return $query->with(['mainImage', 'itemCategory.subCategory'])
            ->where('shop_id', optional($owner->shop)->id)
            ->withSales(30)
            ->withAvgStar(30)
            ->withViewCounts(30)
            ->when($request->filled('search'), fn ($q) => $q->searchItemName($request->search))
            ->when($request->filled('sort'), fn ($q) => $q->sortBy($request->sort))
            ->latest();
    }

}
