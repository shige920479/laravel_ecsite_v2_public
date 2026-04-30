@extends('layouts.app')

@section('content')

<main class="owner-main pt-17">
  <div class="owner-wrapper">
    <div class="login-box item-edit">
      <h3 class="text-lg font-bold">画像登録</h3>
      <x-flash-message status="session('status')"/>
      <form action="{{ route('owner.item.image.store', ['item' => $item]) }}" method="post"
          id="item-edit-flex" enctype="multipart/form-data">
        @csrf
        <ul id="item-info">
          <li class="input">
            <label for="" class="font-bold underline">商品名</label>
            <p>{{ $item->name }}</p>
          </li>
          <li class="input">
            <label for="" class="font-bold underline">カテゴリー</label>
            <p>{{ $item->itemCategory->name }}</p>
          </li>
          <li class="input">
            <label for="" class="font-bold underline">商品情報</label>
            <p>{{ $item->information }}</p>
          </li>
          <li class="input">
            <label for="" class="font-bold underline">価格</label>
            <p>{{ number_format($item->price_ex_tax) . '円' }}</p>
          </li>
        </ul>
        <div class="store-images">
          <div class="input">
            <label class="font-bold underline">イメージ</label>
            <button type="submit" id="images-store-btn">登録する</button>
            <x-input-error :messages="$errors->get('image')" />
            <div class="image-grid" data-item-image>
              <div class="grid-img" data-image-block>
                <div class="flex justify-between">
                  <label>画像1</label>
                  <button class="tmp-image-delete" type="button" data-image-delete data-index="1">画像削除</button>
                </div>
                <input type="file" name="image" class="input-image" data-image-upload data-preview="1"/>
                <div class="preview-selected-flex">
                  <div class="preview-div">
                    <p><label>画像プレビュー</label></p>
                    @session('tmp_item_image.1')
                        <img class="preview-img" src="{{ Storage::url('tmp_item_image.1') }}" alt="">
                    @endsession
                  </div>
                </div>
              </div>
              <div class="grid-img" data-image-block>
                <div class="flex justify-between">
                  <label>画像2</label>
                  <button class="tmp-image-delete" type="button" data-image-delete data-index="2">画像削除</button>
                </div>
                <input type="file" name="image" class="input-image" data-image-upload data-preview="2"/>
                <div class="preview-selected-flex">
                  <div class="preview-div">
                    <p><label>画像プレビュー</label></p>
                    @session('tmp_item_image.2')
                        <img class="preview-img" src="{{ Storage::url('tmp_item_image.2') }}" alt="">
                    @endsession
                  </div>
                </div>
              </div>
              <div class="grid-img" data-image-block>
                <div class="flex justify-between">
                  <label>画像3</label>
                  <button class="tmp-image-delete" type="button" data-image-delete data-index="3">画像削除</button>
                </div>
                <input type="file" name="image" class="input-image" data-image-upload data-preview="3"/>
                <div class="preview-selected-flex">
                  <div class="preview-div">
                    <p><label>画像プレビュー</label></p>
                    @session('tmp_item_image.3')
                        <img class="preview-img" src="{{ Storage::url('tmp_item_image.3') }}" alt="">
                    @endsession
                  </div>
                </div>
              </div>
              <div class="grid-img" data-image-block>
                <div class="flex justify-between">
                  <label>画像4</label>
                  <button class="tmp-image-delete" type="button" data-image-delete data-index="4">画像削除</button>
                </div>
                <input type="file" name="image" class="input-image" data-image-upload data-preview="4"/>
                <div class="preview-selected-flex">
                  <div class="preview-div">
                    <p><label>画像プレビュー</label></p>
                    @session('tmp_item_image.4')
                        <img class="preview-img" src="{{ Storage::url('tmp_item_image.4') }}" alt="">
                    @endsession
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </form>
    </div>
  </div>
</main>
@endsection
@push('scripts')
  <script>
    const uploadUrl = @json(route('owner.tmp.image.store'));
    const deleteUrl = @json(route('owner.tmp.itemImage.delete', ['index' => '_index_']));
  </script>
@endpush