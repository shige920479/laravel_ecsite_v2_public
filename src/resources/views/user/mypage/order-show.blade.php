@extends('layouts.app')

@section('content')
<x-headline />
<div class="max-w-3xl mx-auto p-4 space-y-6">
  <div>
    <a href="{{ route('mypage.orders.index') }}" class="text-sm text-blue-500 hover:underline">
      <img src="{{ asset('images/prev.png') }}" class="w-8 inline">
      <span>一覧へ戻る</span>
    </a>
  </div>
  <div class="bg-white shadow rounded-lg p-4">
    <h1 class="text-lg font-bold mb-3">注文詳細</h1>

    <div class="grid grid-cols-2 gap-y-2 text-sm">
      <div class="text-gray-500">注文番号</div>
      <div>{{ $order->order_number }}</div>

      <div class="text-gray-500">注文日</div>
      <div>{{ $order->ordered_at }}</div>

      <div class="text-gray-500">合計金額</div>
      <div class="font-semibold">¥{{ number_format($order->total_in_tax) }}</div>

      <div class="text-gray-500">支払状況</div>
      <div>{{ $order->payment_status->label() }}</div>
    </div>
  </div>
  @foreach ($order->shipments as $shipment)
    <div class="bg-white shadow rounded-lg p-4 space-y-4">
      <div class="border-b pb-3">
        <h2 class="font-semibold text-base mb-2">配送情報</h2>
        <div class="text-sm text-gray-700 space-y-1">
          <div>
            <span class="font-medium">宛名：</span>
            {{ $shipment->shipping_name }}
          </div>
          <div class="flex flex-wrap gap-x-4 gap-y-1 text-gray-600">
            <span>〒{{ $shipment->shipping_postcode }}</span>
            <span>{{ $shipment->shipping_address }}</span>
            <span>TEL：{{ $shipment->shipping_phone }}</span>
          </div>
          <div>
            <span class="font-medium">発送日：</span>
            {{ $shipment->shipped_at?->format('Y-m-d') ?? '未発送' }}
          </div>
        </div>
      </div>
      <div class="space-y-3">
        @foreach ($shipment->orderItems as $item)
          <div class="flex justify-between items-start border rounded p-3">
            <div class="space-y-1">
              <div class="font-medium">
                {{ $item->item_name }}
              </div>
              <div class="text-sm text-gray-500">
                数量：{{ $item->quantity }}
              </div>
            </div>
            <div class="text-right font-semibold text-sm">
              ¥{{ number_format($item->subtotal_in_tax) }}
            </div>
          </div>
        @endforeach
      </div>
    </div>
  @endforeach
</div>
@endsection