@extends('layouts.app')

@section('content')
<x-headline />

<div class="max-w-3xl mx-auto px-4 py-8">
  <h2 class="text-2xl text-gray-500 font-bold mb-6">ご購入履歴</h2>
  <form action={{ route('mypage.orders.index') }} method="GET">
    <div class="bg-white p-4 rounded-lg shadow mb-6 flex gap-3 items-center">
      <input type="text" name="keyword" value="{{ request('keyword') }}" class="border rounded px-3 py-2 w-1/3" placeholder="商品名">
      <input type="date" name="from"  value="{{ request('from') }}" class="border rounded px-3 py-2">
      <span>～</span>
      <input type="date" name="to" value="{{ request('to') }}" class="border rounded px-3 py-2">
      <button type="submit" class="bg-gray-700 text-white px-4 py-2 rounded cursor-pointer">検索</button>
      <input type="hidden" name="status" value="{{ request('status') }}">
    </div>
    <div class="flex gap-3 mb-6">
      <button type="submit" name="status" value="" class="px-4 py-2
        {{ request('status') === null ? 'bg-gray-800 text-white' : 'bg-gray-100'}} rounded cursor-pointer"
      >すべて
      </button>
      <button type="submit" name="status" value="shipped" class="px-4 py-2
        {{ request('status') === 'shipped' ? 'bg-gray-800 text-white' : 'bg-gray-100'}} rounded cursor-pointer"
      >発送済み
      </button>
      <button type="submit" name="status" value="preparing" class="px-4 py-2
        {{ request('status') === 'preparing' ? 'bg-gray-800 text-white' : 'bg-gray-100'}} rounded cursor-pointer"
      >準備中
      </button>
      <button type="submit" name="status" value="canceled" class="px-4 py-2
        {{ request('status') === 'canceled' ? 'bg-gray-800 text-white' : 'bg-gray-100'}} rounded cursor-pointer"
      >キャンセル
      </button>
    </div>
  </form>
  <div class="space-y-6">
    @forelse ($orders as $order)
      <div class="border rounded-lg p-4 bg-white shadow-sm">
        <div class="flex justify-between items-center border-b pb-2 mb-4">
          <div>
            <p class="text-sm text-gray-500">注文日：{{ $order->ordered_at->format('Y/m/d') }}</p>
            <p class="font-semibold">注文番号：{{ $order->order_number }}</p>
          </div>
          <a href="{{ route('mypage.orders.show', ['order' => $order->id]) }}" class="text-sm text-blue-600 hover:underline">
            詳細を見る
          </a>
        </div>
          @foreach ($order->shipments as $shipment)
            <div class="mb-4">
              <div class="flex gap-10 items-center mb-2">
                <p class="font-semibold">{{ $shipment->shop->name }}</p>
                <span class="text-xs px-2 py-1 rounded {{ $shipment->shipping_status->badgeColor() }}">
                    {{ $shipment->shipping_status->label() }}
                </span>
                @if ($shipment->shipping_status === \App\Enums\ShippingStatus::UNSHIPPED)
                  <button class="text-xs text-gray-400 hover:underline cursor-pointer">
                      このショップの注文をキャンセル
                  </button>
                @endif
              </div>
              @foreach ($shipment->orderItems as $orderItem)
                <div class="flex items-center border-b border-gray-300 py-2">
                  <div class="flex items-center gap-3 flex-1">
                    <img src={{ $orderItem->item->mainImageUrl }}
                        class="w-12 h-12 object-cover rounded">
                    <p class="text-sm font-medium {{ $orderItem->is_hit ? 'bg-[#f6fac3fb]' : '' }}">
                      {{ $orderItem->item_name }}
                    </p>
                  </div>
                  <div class="text-right w-32">
                    <div class="text-xs text-gray-500">数量：{{ $orderItem->quantity }}</div>
                    <div class="text-xs text-gray-500">小計 {{ number_format($orderItem->subtotal_in_tax) }}円</div>
                  </div>
                  <div class="ml-8">
                    @if ($orderItem->item->has_review)
                      <p class="text-xs text-gray-400">投稿済み</p>
                    @else
                      <a 
                        href="{{ route('item.reviews', ['item' => $orderItem->item_id]) }}"
                        class="text-xs! text-indigo-600! hover:underline! whitespace-nowrap"
                      >
                        レビューを書く
                      </a>
                    @endif


                  </div>
                </div>
              @endforeach
            </div>
          @endforeach
          <div class="flex justify-end items-center border-t pt-3 mt-4">
            <p class="font-semibold">
              合計：¥{{ number_format($order->total_in_tax) }}
            </p>
          </div>
      </div>
  @empty
    <p class="text-center mt-20">検索結果はございません</p>
  @endforelse
  </div>
  
  <div class="mt-6">
    {{ $orders->links('pagination::tailwind') }}
  </div>
</div>
@endsection