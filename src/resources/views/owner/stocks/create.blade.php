@extends('layouts.app')

@section('content')
<x-headline />

<main>
  <div class="owner-wrapper">
    <div class="login-box shop-info">
      <div class="flex justify-between">
        <h3 class="text-lg font-bold">在庫登録・更新</h3>
        <a href="{{ route('owner.item.stocks.index', ['item' => $item]) }}" class="px-6 py-2 text-sm bg-gray-400 text-white! rounded-lg hover:bg-gray-800">
          入出庫履歴一覧へ
        </a>
      </div>
      <x-flash-message status="session('status')"/>
      <div id="stock-flex">
        <div id="img-side">
          <p class="mb-2">商品名 : {{ $item->name }}</p>
          <p class="mb-2">カテゴリー : {{ $item->itemCategory->subCategory->name }} >> {{ $item->itemCategory->name }}</p>
          <div><img src="{{ $item->mainImageUrl }}" alt="" /></div>
        </div>
        <form action="{{ route('owner.item.stock.store', ['item' => $item]) }}" method="post" id="text-side">
          @csrf
          <ul>
              <li class="input">
                <label for="name">現在の在庫数</label>
                @if($item->stock_current === 0)
                  <p><span id="stock-qty">在庫がありません、登録願います</span></p>
                @else
                  <p>数量 : <span id="stock-qty">{{ $item->stock_current }}</span> pcs</p>
                @endif
              </li>
              <li class="input">
                <label for="stock_diff">数量</label>
                <div class="quantity-flex">
                  <div>
                    <input type="number" name="stock_diff" id="stock_diff" value="{{ old('stock_diff') }}" min="0" />
                  </div>
                  <div>
                    <input type="radio" name="up_down" id="radio-add" value="1" @checked((int)old('up_down', 1) === 1)/>
                    <label for="radio-add">増やす</label>
                    <input type="radio" name="up_down" id="radio-reduce" value="0" @checked((int)old('up_down', 1) === 0)/>
                    <label for="radio-reduce">減らす</label>
                  </div>
                </div>
                <x-input-error :messages="$errors->get('stock_diff')" />
                <x-input-error :messages="$errors->get('up_down')" />
              </li>
              <li class="input">
                <label for="reason">増減理由/備考</label>
                <input type="text" name="reason" id="reason">
                <x-input-error :messages="$errors->get('reason')" />
              </li>
            </ul>
            <ul>
              <li class="input">
                <button type="submit">登録する</button>
              </li>
            </ul>
        </form>
      </div>
    </div>
  </div>
</main>

@endsection