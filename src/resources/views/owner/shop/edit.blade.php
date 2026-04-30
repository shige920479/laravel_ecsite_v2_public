@extends('layouts.app')

@section('content')
<x-headline />
<div class="owner-wrapper">
  <div class="login-box shop-edit">
    <h3>ショップ登録内容の変更</h3>
    <x-flash-message status="session('status')"/>
    <form action="{{ route('owner.shop.update', ['shop' => $shop]) }}" method="post" enctype="multipart/form-data">
      @csrf
      @method('PUT')
      <ul>
        <li class="input">
          <label for="name">販売する店舗名</label>
          <input type="text" name="name" value="{{ old('name', $shop->name ?? '') }}" id="name" />
          <x-input-error :messages="$errors->get('name')"/>
        </li>
        <li class="input">
          <label for="shop-info">店舗情報</label>
          <textarea name="information" id="shop-info">{{ old('information', $shop->information ?? '') }}</textarea>
          <x-input-error :messages="$errors->get('information')" />
        </li>
        <li class="input">
          <label>ステータス</label>
          <div>
            @php $isSelling = old('is_selling', $shop->is_selling ?? 1); @endphp
            <input type="radio" name="is_selling" value="1" id="available" @checked($isSelling)/>
            <label for="available">販売中</label>
            <input type="radio" name="is_selling" value="0" id="stop" @checked(! $isSelling)/>
            <label for="stop">停止中</label>
          </div>
          <x-input-error :messages="$errors->get('is_selling')" />
        </li>
        <li class="input">
          <div id="shop-edit">
            @if(! empty($shop->filename))
              <div class="mb-5">
                <p><label>現在の登録画像</label></p>
                <img src="{{ $shop->imageUrl }}" alt="" class="shop-img">
              </div>
            @endif
            <label for="image">画像を変更する</label>
            <div id="file-input-flex">
              <div>
                <input type="file" id="input-image" name="image" data-upload-url="{{ route('owner.tmp.image.store') }}"/>
              </div>
              <div id="file-preview">
              </div>
            </div>
            <p class="mt-5"><label>現在選択中の画像</label></p>
            <div id="tmp-img-wrapper">
              @session('tmp_image')
                <img src="{{ Storage::url(session('tmp_image')) }}" alt="preview" id="shop-tmp-img">
              @endsession
            </div>
          </div>
          <x-input-error :messages="$errors->get('image')"/>
        </li>
      </ul>
      <button type="submit">店舗登録</button>
    </form>
  </div>
</div>
@endsection