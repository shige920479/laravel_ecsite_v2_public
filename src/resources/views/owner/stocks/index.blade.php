@extends('layouts.app')

@section('content')

<x-headline />

<main class="owner-main">
  <div class="owner-wrapper max-w-4xl!">
    <div class="mb-6">
      <h3 class="text-lg font-semibold mb-1">在庫履歴</h3>
      <div class="flex flex-wrap items-center gap-4 text-sm text-gray-700">
        <div>
          <span class="text-gray-500">商品名：</span>
          <span class="font-medium">{{ $item->name }}</span>
        </div>
        <div>
          <span class="text-gray-500">商品ID：</span>
          {{ $item->id }}
        </div>
        <div>
          <span class="text-gray-500">現在在庫：</span>
          <span class="font-semibold">
            {{ $item->stock_current }} pcs
          </span>
        </div>
      </div>
    </div>
    <div class="bg-white shadow rounded-lg overflow-hidden">
      <div class="flex justify-between px-4 py-5">
        <form action="" method="get" class="flex items-center gap-3">
          <input type="date" name="start_date" value="{{ request('start_date') }}" class="py-2 px-3 text-sm border border-gray-300 rounded-lg">
          <span class="text-gray-500">〜</span>
          <input type="date" name="end_date" value="{{ request('end_date') }}" class="py-2 px-3 text-sm border border-gray-300 rounded-lg">
          <select name="type" class="h-auto! py-2 px-3 text-sm border! rounded-lg!">
            <option value="">すべて</option>
            <option value="in"  @selected(request('type') === 'in')>入庫</option>
            <option value="out" @selected(request('type') === 'out')>出庫</option>
          </select>
          <button type="submit" class="px-4 py-2 text-sm bg-gray-700 text-white rounded-lg hover:bg-gray-800">
            検索
          </button>
        </form>
        <div class="flex justify-end">
          <a href="{{ route('owner.item.stocks.csv', ['item' => $item]) . '?' . request()->getQueryString() }}"
            class="flex items-center px-3 py-2 text-sm font-medium text-gray-900 bg-white border border-gray-300 rounded-lg hover:bg-gray-100">
              <svg class="w-4 h-4 mr-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5" />
              </svg>
              Download in CSV
          </a>
        </div>
      </div>
      <div class="overflow-x-auto">
        <table class="w-full text-sm text-left text-gray-600">
          <thead class="text-xs uppercase bg-gray-100 text-gray-700">
            <tr>
              <th class="px-4 py-3 w-50">日時</th>
              <th class="px-4 py-3 w-40">種別</th>
              <th class="px-4 py-3 w-40">増減</th>
              <th class="px-4 py-3 w-40">在庫後</th>
              <th class="px-4 py-3">理由</th>
            </tr>
          </thead>
          <tbody>
          @forelse ($histories as $history)
            <tr class="border-b border-gray-200 hover:bg-indigo-50">
              <td class="px-4 py-3">
                {{ $history->created_at->format('Y-m-d H:i') }}
              </td>
              <td class="px-4 py-3">
                @if ($history->stock_diff > 0)
                  <span class="text-green-600 font-medium">入庫</span>
                @else
                  <span class="text-red-600 font-medium">出庫</span>
                @endif
              </td>
              <td class="px-4 py-3 font-semibold">
                @if ($history->stock_diff > 0)
                  <span class="text-green-600">
                    +{{ $history->stock_diff }}
                  </span>
                @else
                  <span class="text-red-600">
                    {{ $history->stock_diff }}
                  </span>
                @endif
              </td>
              <td class="px-4 py-3 font-medium">
                {{ $history->stock_after ?? '—' }}
              </td>
              <td class="px-4 py-3">
                {{ $history->reason ?: '—' }}
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="5" class="px-4 py-6 text-center text-gray-500">
                在庫履歴はまだ登録されていません
              </td>
            </tr>
          @endforelse
          </tbody>
        </table>
      </div>
    </div>
    <div class="flex justify-between mt-4">
      <a href="{{ route('owner.item.stock.create', $item) }}"
         class="text-sm text-indigo-600 hover:underline">
        ← 在庫登録画面へ戻る
      </a>
      {{ $histories->links() }}
    </div>
  </div>
</main>

@endsection
