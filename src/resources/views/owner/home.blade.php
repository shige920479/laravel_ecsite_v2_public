@extends('layouts.app')

@section('content')
<x-headline />

<main>
  <div class="owner-wrapper">
    <div class="login-box shop-info">
    
    @if (empty($owner->shop))
      <h3 class="unregistered">ショップ情報がありません、新規登録願います</h3>
      <div id="link-shop-register"><a href="{{ route('owner.shop.create') }}">新規登録</a></div>

    @else
      <h3 class="text-lg font-bold">店舗情報</h3>
      <x-flash-message status="session('status')"/>
      <div class="flex gap-4 items-baseline mt-1.5">
        <p class="text-lg font-bold">ショップ名：{{ $owner->shop->name }}</p>
        <p>【 {{ $owner->shop->is_selling ? "販売中" : "停止中" }} 】</p>
      </div>
      <div id="shop-flex">
        <div id="img-side">
          <div><img src="{{ $owner->shop->imageUrl }}" alt="" /></div>
        </div>
        <div id="text-side">
          <p class="leading-6 w-full h-50 overflow-y-scroll bg-white rounded-md p-2">
            {!! nl2br(e($owner->shop->information)) !!}
          </p>
          
          <a href="{{ route('owner.shop.edit', ['shop' => $owner->shop]) }}" id="shop-edit-link-btn">
            ショップ情報編集
          </a>
        </div>
      </div>
    @endif
    </div>
  </div>
</main>

@endsection