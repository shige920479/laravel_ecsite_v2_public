@extends('layouts.app')

@section('content')
<x-headline />

<main class="owner-main">
  <div class="owner-wrapper">
    <div class="login-box item-edit">
      <div class="flex gap-6">
        <h3>商品登録</h3>
        <x-flash-message status="session('status')"/>
      </div>
      @if(! $shop)
      <p class="text-gray-500 text-center text-2xl">先にショップ登録をお願いします</p>
      @else
      <form action="{{ route('owner.item.confirm') }}" method="post" id="item-edit-flex">
        @csrf
        <ul class="item-left">
          <li class="input">
            <label for="">販売する店舗</label>
            <input type="text" value="{{ $shop->name }}" disabled>
            <input type="hidden" name="shop_id" value="{{ $shop->id }}">
          </li>
          <li class="input">
            <label for="item-category">商品カテゴリー</label>
            <select name="item_category_id" id="item-category" class="leading-10">
              <option value="">選択してください</option>
                @foreach ($categories as $category)
                  <optgroup label="{{ $category->name }}" class="bg-gray-300 text-black"></optgroup>
                  @foreach ($category->subCategories as $subCategory)
                    <optgroup label="{{ $subCategory->name }}" class="bg-gray-200 text-black"></optgroup>
                      @foreach ($subCategory->itemCategories as $itemCategory)
                        <option value="{{ $itemCategory->id }}"
                           @selected((int)old('item_category_id', $input['item_category_id'] ?? '') === $itemCategory->id)>
                           {{ $itemCategory->name }}
                        </option>
                      @endforeach
                  @endforeach
                @endforeach
            </select>
            <x-input-error :messages="$errors->get('item_category_id')" />
          </li>
          <li class="input">
            <label for="name">商品名</label>
            <input type="text" name="name" id="name" value="{{ old('name', $input['name'] ?? '') }}" />
            <x-input-error :messages="$errors->get('name')" />
          </li>
          <li class="input">
            <label for="price">価格</label>
            <input type="number" name="price_ex_tax" id="price" value="{{ old('price_ex_tax', $input['price_ex_tax'] ?? '') }}" />
            <x-input-error :messages="$errors->get('price_ex_tax')" />
          </li>
        </ul>
        <ul class="item-right">
          <li class="input">
            <label for="item-information">商品情報</label>
            <textarea type="text" name="information" id="item-information" rows="11">{{ old('information', $input['information'] ?? '') }}</textarea>
            <x-input-error :messages="$errors->get('information')" />
          </li>
          <li class="input">
            <label>ステータス</label>
            <div>
              <input type="radio" name="is_selling" value="1" id="available" @checked($is_selling === 1)/>
              <label for="available" class="">販売中</label>
              <input type="radio" name="is_selling" value="0" id="stop" @checked($is_selling === 0)/>
              <label for="stop">停止中</label>
              <x-input-error :messages="$errors->get('is_selling')" />
            </div>
          </li>
          <li>
            <button type="submit">商品登録</button>
          </li>
        </ul>
      </form>
      @endif
    </div>
  </div>
</main>
@endsection