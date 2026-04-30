@extends('layouts.app')
@section('content')

<x-admin-headline />
<section class="login-wrapper">
  <div class="login-box">
    <h3>商品カテゴリー編集</h3>
    <x-flash-message status="session('status')" />
    <form action="{{ route('admin.itemCategory.update', ['itemCategory' => $itemCategory]) }}" method="post">
      @csrf
      @method('PUT')
      <ul>
        <div class="input account">
          <div>
            <label for="name">サブカテゴリー</label>
          </div>
          <input type="text" value="{{ $itemCategory->subCategory->name }}" disabled class="bg-gray-200!">
        </div>
        <div class="input account">
          <div>
            <label for="name">商品カテゴリー名</label>
          </div>
          <input type="text" name="name" id="name" value="{{ old('name', $itemCategory->name) }}"/>
          <x-input-error :messages="$errors->get('name')" />
        </div>
        <div class="input account">
          <div>
            <label for="slug">商品スラグ名 <span class="ml-5">※上書きできません</span></label>
          </div>
          <input type="text" name="slug" id="slug" value="{{ $itemCategory->slug }}" disabled class="bg-gray-200!"/>
          <x-input-error :messages="$errors->get('slug')" />
        </div>
        <button type="submit">変更する</button>
      </ul>
    </form>
  </div>
</section>
@endsection