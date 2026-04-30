@extends('layouts.app')

@section('content')

<x-headline />
<div class="max-w-2xl mx-auto mt-10 px-4">

    <h1 class="text-2xl font-bold text-center mb-6">
        ご注文ありがとうございます
    </h1>

    @if ($order)
      <div class="text-center mb-6">
          <span class="text-gray-500 text-sm">注文番号</span><br>
          <span class="font-semibold text-lg">{{ $order->order_number }}</span>
      </div>
      <div class="border-t border-gray-200"></div>
      <h3 class="mt-6 mb-3 font-semibold text-lg">ご注文内容</h3>

      <div>
          @foreach ($order->orderItems as $orderItem)
              <div class="flex justify-between items-center mb-2">
                  <div class="flex justify-between items-center gap-10">
                    <div>
                      <img class="w-18" src={{ $orderItem->item->mainImageUrl }} alt="">
                    </div>
                    <div>
                        {{ $orderItem->item_name }} × {{ $orderItem->quantity }}
                    </div>
                  </div>
                  <div>
                      ¥{{ number_format($orderItem->subtotal_in_tax) }}
                  </div>
              </div>
          @endforeach
      </div>
      <div class="border-t border-gray-200 mt-4"></div>
      <div class="flex justify-between mt-4 text-lg font-bold">
          <div>合計</div>
          <div>¥{{ number_format($order->total_in_tax) }}</div>
      </div>

      <p class="mt-6 text-sm text-gray-500 text-center">
          確認メールを送信しました。内容をご確認ください。
      </p>
      <div class="mt-6 text-center">
          <a href="{{ route('home.index') }}"
              class="inline-block px-6 py-2 bg-gray-800 text-white! rounded hover:bg-gray-700 transition">
              トップページへ戻る
          </a>
      </div>
    @else
      <div class="text-center mt-10">
          <p class="text-lg font-medium">ご注文を受け付けました</p>

          <p class="text-sm text-gray-500 mt-2">
              現在、決済の最終確認を行っています。<br>
              処理に少し時間がかかる場合があります。
          </p>

          <p class="text-sm mt-4">
              数分後に注文履歴ページよりご確認ください。
          </p>

          <a href="{{ route('mypage.orders.index') }}"
            class="inline-block mt-4 text-blue-600 underline!">
              注文履歴を見る
          </a>
      </div>
    @endif
</div>
@endsection