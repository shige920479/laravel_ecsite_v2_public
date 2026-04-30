@extends('layouts.app')
@section('content')

<x-admin-headline />
<section class="login-wrapper">
  <div class="login-box">
    <h3>サブカテゴリー登録</h3>
    <x-flash-message status="session('status')" />
    <form action="{{ route('admin.subCategory.store') }}" method="post">
      @csrf
      <ul>
        <div class="input account">
          <div>
            <label for="name">カテゴリー選択</label>
          </div>
          <select name="category_id">
            @if($categories->isEmpty())
              <option value="">先にカテゴリーを登録して下さい</option>
            @else
              <option value="">選択してください</option>
              @foreach ($categories as $category)
                <option 
                 value="{{ $category->id }}" 
                 @selected((int)old('category_id', $categoryId) === $category->id)>
                  {{ $category->name }}
                </option>
              @endforeach
            @endif
          </select>
          <x-input-error :messages="$errors->get('category_id')" className="-mb-3"/>
        </div>
        <div class="input account">
          <div>
            <label for="name">サブカテゴリー名</label>
          </div>
          <input type="text" name="name" id="name" value="{{ old('name') }}"/>
          <x-input-error :messages="$errors->get('name')" className="-mb-3"/>
        </div>
        <div class="input account">
          <div>
            <label for="slug">スラグ名</label>
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
    <a href="{{ route('admin.itemCategory.create')}}">商品カテゴリー登録</a>
  </div>
</section>
@endsection