<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCategoryRequest;
use App\Http\Requests\StoreItemCategoryRequest;
use App\Http\Requests\StoreSubCategoryRequest;
use App\Http\Requests\UpdateItemCategoryRequest;
use App\Models\Category;
use App\Models\ItemCategory;
use App\Models\SubCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::query()->with(['subCategories.itemCategories'])->get();

        return view('admin.category.index', ['categories' => $categories]);
    }

    public function createCategory()
    {
        Gate::authorize('category.create');
        return view('admin.category.create-category');
    }
    public function storeCategory(StoreCategoryRequest $request)
    {
        Gate::authorize('category.create');
        $validated = $request->validated();
        $newCategory = Category::create($validated);

        return to_route('admin.subCategory.create', ['category_id' => $newCategory->id])->with([
            'status' => 'info',
            'message' => "カテゴリー：{$newCategory->name} を新規登録しました"
        ]);
    }
    public function createSubCategory(Request $request)
    {
        Gate::authorize('category.create');
        $categoryId = $request->input('category_id');
        $categories = Category::orderBy('id')->get();

        return view('admin.category.create-sub-category', [
            'categoryId' => $categoryId,
            'categories' => $categories
        ]);
    }

    public function storeSubCategory(StoreSubCategoryRequest $request)
    {
        Gate::authorize('category.create');
        $validated = $request->validated();
        $newSubCategory = SubCategory::create($validated);

        return to_route('admin.itemCategory.create', ['sub_category_id' => $newSubCategory->id])->with([
            'status' => 'info',
            'message' => "サブカテゴリー：{$newSubCategory->name} を新規登録しました"
        ]);

    }

    public function createItemCategory(Request $request)
    {
        Gate::authorize('category.create');
        $subCategoryId = $request->input('sub_category_id');
        $categories = Category::with(['subCategories'])->orderBy('id')->get();

        return view('admin.category.create-item-category', [
            'subCategoryId' => $subCategoryId,
            'categories' => $categories
        ]);
    }

    public function storeItemCategory(StoreItemCategoryRequest $request)
    {
        Gate::authorize('category.create');
        $validated = $request->validated();
        $newItemCategory = ItemCategory::create($validated);

        return to_route('admin.categories.index')->with([
            'status' => 'info',
            'message' => "商品カテゴリー：{$newItemCategory->name} を新規登録しました"
        ]);
    }
    
    public function editItemCategory(ItemCategory $itemCategory)
    {
        Gate::authorize('category.update');
        return view('admin.category.edit-item-category', ['itemCategory' => $itemCategory]);
    }

    public function updateItemCategory(UpdateItemCategoryRequest $request, ItemCategory $itemCategory)
    {
        Gate::authorize('category.update');
        $itemCategory->fill($request->validated());
        if ($itemCategory->isClean()) {
            return back()->with([
                'status' => 'alert',
                'message' => '登録内容が更新されておりません'
            ]);
        }
        $itemCategory->save();

        return to_route('admin.categories.index')->with([
            'status' => 'info',
            'message' => "商品カテゴリー（{$itemCategory->name}）を変更しました"
        ]);
    }
}
