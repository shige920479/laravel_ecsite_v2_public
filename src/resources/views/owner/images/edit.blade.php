@extends('layouts.app')
@section('content')

<main class="owner-main pt-17">
  <div class="owner-wrapper pt-10">
    <div class="login-box item-edit">
      <h3>登録済み画像の編集</h3>
      <div id="item-edit-flex" class="image-edit-wrapper">
        <ul id="item-info">
          <li class="input">
            <label for="">商品名</label>
            <p>{{ $item->name }}</p>
          </li>
          <li class="input">
            <label for="">カテゴリー</label>
            <p>{{ $item->itemCategory->name }}</p>
          </li>
          <li class="input">
            <label for="">商品情報</label>
            <p>{{ $item->information }}</p>
          </li>
          <li class="input">
            <label for="">価格</label>
            <p>{{ number_format($item->price_ex_tax) . '円'}}</p>
          </li>
        </ul>
        <form class="store-images edit" action="{{ route('owner.item.image.update', ['item' => $item]) }}" method="post" enctype="multipart/form-data" >
          @csrf
          @method('PUT')
          <input type="hidden" name="has-deleted" value="0">
          <div class="input image-edit">
            <label>イメージ</label>
            <button type="submit" id="images-store-btn">登録する</button>
            <x-input-error :messages="$errors->get('images')"/>
            <x-input-error :messages="$errors->get('filename')"/>
            <ul id="sortable-list" class="image-grid" data-item-image>
              @for ($i = 1; $i <= 4; $i++)
                <li  class="sortable-item grid-img" data-image-block>
                  <label for="{{ "image{$i}" }}" class="mb-0!">画像{{ $i }}
                  </label>
                  <input type="file" name="image[]" class="input-image" data-update-upload data-preview="{{ $i }}"/>
                  <div class="preview-selected-flex gap-1" data-image-flex>
                    @if ($tmpPath = session("tmp_item_image.{$i}"))
                      <div class="preview-div">
                        <p><label>画像プレビュー</label></p>
                        <div class="preview-img">
                          <img src="{{ Storage::url($tmpPath) }}" alt="">
                          <button type="button" class="image-trush" data-update-delete data-index="{{ $i }}">
                            <img src="{{ asset('images/trush.png')}}" alt="" class="delete-icon">
                          </button>
                        </div>
                      </div>
                      @endif
                    @if ($itemImage = $itemImages->firstWhere('sort_order', $i))
                      <div class="current-div">
                        <p><label>登録済みの画像</label></p>
                        <img src="{{ Storage::url($itemImage->filename) }}" alt="" id="{{ "selected{$i}" }}">
                        <button type="button" class="image-trush" data-uploads-delete data-index="{{ $i }}">
                          <img src="{{ asset('images/trush.png')}}" alt="" class="delete-icon">
                        </button>
                      </div>
                    @else
                        <div class="current-img-wrapper">
                          <p><label>登録済みの画像</label></p>
                          <img src="{{ asset('images/noimage.png') }}" alt="" id="{{ "selected{$i}" }}">
                        </div>
                    @endif
                  </div>
                  <input type="hidden" name="item_image_ids[]" value="{{ $itemImage->id ?? '' }}"> 
                  <input type="hidden" name="filenames[]" value="{{ $itemImage->filename ?? '' }}">
                  <input type="hidden" name="sort_order[]" value="{{ $i }}">
                  <input type="hidden" name="def_sort[]" value="{{ $i }}">
                </li>
              @endfor
            </ul>
          </div>
        </form>
      </div>
    </div>
  </div>
</main>
@endsection
@push('scripts')
  <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
  <script>
    const uploadUrl = @json(route('owner.tmp.image.store'));
    const deleteUrl = @json(route('owner.tmp.itemImage.delete', ['index' => '_index_']));
    const uploadsDeleteUrl = @json(route('owner.item.image.delete', ['itemImage' => 'PLACEHOLDER']));
  </script>
@endpush