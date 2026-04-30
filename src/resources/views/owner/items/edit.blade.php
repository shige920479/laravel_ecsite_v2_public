@extends('layouts.app')

@section('content')
<x-headline />

<main class="owner-main">
  <div class="owner-wrapper">
    <div class="login-box item-edit">
      <h3>商品情報の変更</h3>
      <x-flash-message status="session('status')"/>
      <form action="{{ route('owner.item.updateConfirm', ['item' => $item]) }}" method="post" id="item-edit-flex">
        @csrf
        <ul class="item-left">
          <li class="input">
            <label for="">販売する店舗</label>
            <input type="text" value="{{ $item->shop->name }}" disabled>
            <input type="hidden" name="shop_id" value="{{ $item->shop->id }}">
          </li>
          <li class="input">
            <label for="item-category">商品カテゴリー</label>
            <select name="item_category_id" id="item-category" class="leading-10">
              <option value="">選択してください</option>
                @foreach ($categories as $category)
                  <optgroup label="{{ $category->name }}"></optgroup>
                  @foreach ($category->subCategories as $subCategory)
                    <optgroup label="{{ $subCategory->name }}"></optgroup>
                      @foreach ($subCategory->itemCategories as $itemCategory)
                        <option value="{{ $itemCategory->id }}"
                          @selected((int)oldSessionOrModel('item_category_id', $input ?? null, $item ?? null) === $itemCategory->id)>
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
            <input type="text" name="name" id="name" value="{{ old('name', $input['name'] ?? $item->name) }}" />
            <x-input-error :messages="$errors->get('name')" />
          </li>
          <li class="input">
            <label for="price">価格</label>
            <input type="number" name="price_ex_tax" id="price" value="{{ (int)old('price_ex_tax', $input['price_ex_tax'] ?? $item->price_ex_tax) }}" />
            <x-input-error :messages="$errors->get('price_ex_tax')" />
          </li>
        </ul>
        <ul class="item-right">
          <li class="input">
            <label for="item-information">商品情報</label>
            <textarea type="text" name="information" id="item-information" rows="11">{{ old('information', $input['information'] ?? $item->information) }}</textarea>
            <x-input-error :messages="$errors->get('information')" />
          </li>
          <li class="input">
            <label>ステータス</label>
            <div>
              @php
                $isSelling = (int)oldSessionOrModel('is_selling', $input ?? null, $item ?? null, 1);
              @endphp
              <input type="radio" name="is_selling" value="1" id="available" @checked($isSelling === 1)/>
              <label for="available" class="">販売中</label>
              <input type="radio" name="is_selling" value="0" id="stop" @checked($isSelling === 0)/>
              <label for="stop">停止中</label>
              <x-input-error :messages="$errors->get('is_selling')" />
            </div>
          </li>
          <li>
            <button type="submit">商品登録</button>
          </li>
        </ul>
      </form>
    </div>
  </div>
</main>
@endsection