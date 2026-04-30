@extends('layouts.app')
@section('content')

<x-headline />

<main class="owner-main">
  <div class="owner-wrapper">
    <div class="login-box item-edit">
      @if (request()->routeIs('owner.item.confirm'))
        <h3>商品登録内容のご確認</h3>
        <form action="{{ route('owner.item.store') }}" method="post">
          @csrf
      @elseif(request()->routeIs('owner.item.updateConfirm'))
        <h3> 商品変更内容のご確認</h3>
        <form action="{{ route('owner.item.update', ['item' => $item]) }}" method="post">
          @csrf
          @method('PUT')
      @endif
          <div id="item-edit-flex">
            <ul class="item-left">
              <li class="input">
                <label for="">販売する店舗</label>
                <p class="item-preview">{{ $shopName }}</p>
              </li>
              <li class="input">
                <label for="item-category">商品カテゴリー</label>
                <p class="item-preview">{{ $categoryName }}</p>
              </li>
              <li class="input">
                <label for="name">商品名</label>
                <p class="item-preview">{{ $item['name'] }}</p>
              </li>
              <li class="input">
                <label for="price">価格</label>
                <p class="item-preview">{{ number_format($item['price_ex_tax']) . '円 (税抜き)' }}</p>
              </li>
              <li class="input">
                <label for="sort_order">表示順</label>
                <p class="item-preview">{{ $item['sort_order'] ?? '指定なし' }}</p>
              </li>
            </ul>
            <ul class="item-right">
              <li class="input">
                <label for="item-information">商品情報</label>
                <p class="item-preview">{{ $item['information'] }}</p>
              </li>
              <li class="input">
                <label>ステータス</label>
                <p class="item-preview">{{ $item['is_selling'] ? '販売中' : '停止中' }}</p>
              </li>
            </ul>
          </div>
          <div id="item-preview-btn-flex">
            @if (request()->routeIs('owner.item.confirm'))
              <a href="{{ route('owner.item.create') }}">入力画面へ戻る</a>
              <button type="submit">商品登録</button>
            @elseif(request()->routeIs('owner.item.updateConfirm'))
              <a href="{{ route('owner.item.edit', ['item' => $item]) }}">入力画面へ戻る</a>
              <button type="submit">商品変更登録</button>
            @endif
          </div>
        </form>
    </div>
  </div>
</main>
@endsection