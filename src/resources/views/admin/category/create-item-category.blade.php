@extends('layouts.app')
@section('content')

<x-admin-headline />
<section class="login-wrapper">
  <div class="login-box">
    <h3>商品カテゴリー登録</h3>
    <x-flash-message status="session('status')" />
    <form action="{{ route('admin.itemCategory.store') }}" method="post">
      @csrf
      <ul>
        <div class="input account">
          <div>
            <label for="name">サブカテゴリー選択</label>
          </div>
          <select name="sub_category_id" id="sub-categories">
            @if($categories->isEmpty())
              <option value="">先にカテゴリーを登録して下さい</option>
            @else
              <option value="">選択してください</option>
              @foreach ($categories as $category)
                <optgroup class="text-base bg-gray-200 text-gray-400!" label="{{ $category->name }}"></optgroup>
                @foreach ($category->subCategories as $subCat)
                  <option
                    value="{{ $subCat->id }}" 
                    @selected((int)old('sub_category_id', $subCategoryId) === $subCat->id) 
                  >
                    {{ $subCat->name }}
                  </option>
                @endforeach
              @endforeach
            @endif
          </select>
          <x-input-error :messages="$errors->get('sub_category_id')" className="-mb-3"/>
        </div>
        <div class="input account">
          <div>
            <label for="name">商品カテゴリー名</label>
          </div>
          <input type="text" name="name" id="name" value="{{ old('name') }}"/>
          <x-input-error :messages="$errors->get('name')" className="-mb-3"/>
        </div>
        <div class="input account">
          <div>
            <label for="slug">商品スラグ名</label>
          </div>
          <input type="text" name="slug" id="slug" value="{{ old('slug') }}"/>
          <x-input-error :messages="$errors->get('slug')" className="-mb-3"/>
        </div>
        <button type="submit">登録する</button>
      </ul>
    </form>
  </div>
  <div id="other-category-link">
    <a href="{{ route('admin.category.create') }}">カテゴリー登録</a>
    <a href="{{ route('admin.subCategory.create') }}">サブカテゴリー登録</a>
  </div>
</section>
@endsection