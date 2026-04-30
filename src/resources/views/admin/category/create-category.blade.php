@extends('layouts.app')
@section('content')

<x-admin-headline />
<section class="login-wrapper">
  <div class="login-box">
    <h3>カテゴリー登録</h3>
    <x-flash-message status="session('status')" />
    <form action="{{ route('admin.category.store') }}" method="post">
      @csrf
      <div>
        <div class="input account">
          <div><label for="name">カテゴリー名</label></div>
          <input type="text" name="name" id="name" value="{{ old('name') }}"/>
          <x-input-error :messages="$errors->get('name')" className="-mb-3"/>
        </div>
        <div class="input account">
          <div><label for="slug">スラグ（英数字2～50文字以内）</label></div>
          <input type="text" name="slug" id="slug" value="{{ old('slug') }}"/>
          <x-input-error :messages="$errors->get('slug')" className="-mb-3" />
        </div>
        <button type="submit">登録する</button>
      </div>
    </form>
  </div>
  <div id="other-category-link">
    <a href="{{ route('admin.subCategory.create') }}">サブカテゴリー登録</a>
    <a href="{{ route('admin.itemCategory.create')}}">商品カテゴリー登録</a>
  </div>
</section>

@endsection