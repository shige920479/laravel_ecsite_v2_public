@extends('layouts.app')

@section('content')
<x-headline />
<div class="owner-wrapper">
  <div class="login-box shop-edit">
    <h3>ショップ登録</h3>
    <x-flash-message status="session('status')"/>
    <form action="{{ route('owner.shop.store') }}" method="post" enctype="multipart/form-data">
      @csrf
      <ul>
        <li class="input">
          <label for="name">販売する店舗名</label>
          <input type="text" name="name" value="{{ old('name') }}" id="name" />
          <x-input-error :messages="$errors->get('name')" />
        </li>
        <li class="input">
          <label for="shop-info">店舗情報</label>
          <textarea name="information" id="shop-info">{{ old('information') }}</textarea>
          <x-input-error :messages="$errors->get('information')" />
        </li>
        <li class="input">
          <label for="">ステータス</label>
          <div class="flex items-center">
            @php $isSelling = old('is_selling') ?? true; @endphp
            <input type="radio" name="is_selling" value="1" id="available" @checked($isSelling) />
            <label for="available" class="mb-0! ml-1">販売中</label>
            <input type="radio" name="is_selling" value="0" id="stop" @checked(! $isSelling) />
            <label for="stop" class="mb-0! ml-1">停止中</label>
          </div>
          <x-input-error :messages="$errors->get('is_selling')" />
        </li>
        <li class="input">
          <label for="image">画像を選択</label>
          <div id="file-input-flex">
            <div>
              <input type="file" id="input-image" name="image" data-upload-url="{{ route('owner.tmp.image.store') }}"/>
            </div>
            <div id="file-preview">
            </div>
          </div>
          <div>
            <p><label>現在選択中の画像</label></p>
              @session('tmp_image')
                <img src="{{ Storage::url(session('tmp_image')) }}" alt="preview" id="shop-tmp-img">
              @endsession
          </div>
          <x-input-error :messages="$errors->get('image')"/>
        </li>
      </ul>
      <button type="submit">店舗登録</button>
    </form>
  </div>
</div>
@endsection
