@extends('layouts.app')

@section('content')

<x-headline/>

<main class="owner-main item-list-view">
  <div class="owner-wrapper max-w-7xl!">
    <h3 class="px-4">商品一覧</h3>
    <x-flash-message status="session('status')" />
    <x-input-error :messages="$errors->get('search')" />
    <x-input-error :messages="$errors->get('sort')" />
    <div class="px-4 mt-1.5">
      <div class="relative overflow-hidden bg-white shadow-md sm:rounded-lg">
        <div class="flex items-center justify-between px-4 py-3">
          <form action="{{ route('owner.item.index') }}" method="get" class="flex items-center gap-3">
            <div class="relative">
              <input
                type="text"
                name="search"
                value="{{ request('search') }}"
                placeholder="商品名"
                class="pl-10 pr-3 py-2 w-48 text-sm border border-gray-300 rounded-lg"
              >
              <svg class="absolute left-3 top-2.5 w-4 h-4 text-gray-400"
                  fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd"
                  d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z"
                  clip-rule="evenodd" />
              </svg>
            </div>
            <select name="sort" class="pl-10 pr-3 py-2 w-50 h-auto! text-sm border border-gray-300! rounded-lg!">
              <option value="" @selected(! request()->filled('sort'))>新規登録順</option>
              <option value="sales" @selected(request('sort') === 'sales')>販売数の多い順</option>
              <option value="rating" @selected(request('sort') === 'rating')>レーティング順</option>
              <option value="view" @selected(request('sort') === 'view')>閲覧数順</option>
            </select>
            <button type="submit" class="px-4 py-2 text-sm bg-gray-700 text-white rounded-lg hover:bg-gray-800">
              検索
            </button>
          </form>
          <a href="{{ route('owner.items.csv') . '?' . request()->getQueryString() }}"
            class="flex items-center px-4 py-2 text-sm font-medium text-gray-900 bg-white border border-gray-300 rounded-lg hover:bg-gray-100 ml-4">
              <svg class="w-4 h-4 mr-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5" />
              </svg>
              Download in CSV
          </a>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left text-gray-500">
                <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                    <tr>
                        <th scope="col" class="p-4">id</th>
                        <th scope="col" class="px-4 py-3">商品</th>
                        <th scope="col" class="px-4 py-3">カテゴリー</th>
                        <th scope="col" class="px-4 py-3">商品単価</th>
                        <th scope="col" class="px-4 py-3">在庫数量</th>
                        <th scope="col" class="px-4 py-3">Sales<br><small>※過去30日</small></th>
                        <th scope="col" class="px-4 py-3">レーティング</th>
                        <th scope="col" class="px-4 py-3">閲覧数<br><small>※過去30日</small></th>
                        <th scope="col" class="px-4 py-3">最新登録日</th>
                        <th scope="col" class="px-4 py-3">商品の更新・削除</th>
                    </tr>
                </thead>
                <tbody>
                  @forelse ($items as $item)
                    <tr class="border-b border-gray-200 hover:bg-gray-100">
                        <td class="w-4 px-4 py-3">
                          {{ $item->id }}
                        </td>
                        <th scope="row" class="flex items-center px-4 py-2 font-medium text-gray-900 whitespace-nowrap">
                          <div class="w-12 h-8 overflow-hidden mr-3">
                              <img
                                  src="{{ $item->mainImageUrl }}"
                                  alt="iMac Front Image"
                                  class="w-full h-full object-cover rounded-2xl"
                              >
                          </div>
                            {{ $item->name }}
                        </th>
                        <td class="px-4 py-2">
                            <span class="bg-primary-100 text-primary-800 text-xs font-medium px-2 py-0.5 rounded">
                              {{ $item->itemCategory->name}}
                            </span>
                        </td>
                        <td class="px-4 py-2 font-medium text-gray-900 whitespace-nowrap">
                            <div class="flex items-center">¥{{ number_format($item->priceTaxIn) }}</div>
                        </td>
                        <td class="px-4 py-2 font-medium text-gray-900 whitespace-nowrap">
                          {{ $item->stock_current ?? 0 }} pcs
                        </td>
                        <td class="px-4 py-2 font-medium text-gray-900 whitespace-nowrap">
                          {{ $item->sales ?? 0 }} pcs
                        </td>
                        <td class="px-4 py-2 font-medium text-gray-900 whitespace-nowrap">
                            <div class="flex items-center">
                              <div class="stars">
                                <div class="stars-filled" style="width: {{ $item->avg_star / 5 * 100 }}%"></div>
                              </div>
                              <span class="rating-text">
                                {{ round($item->avg_star, 1)}}
                              </span>
                            </div>
                        </td>
                        <td class="px-4 py-2 font-medium text-gray-900 whitespace-nowrap">
                            <div class="flex items-center">
                                {{ $item->view_counts }} view
                            </div>
                        </td>
                        <td class="px-4 py-2">{{ $item->createdAtFormatted }}</td>

                        @if ($item->is_selling)
                          <td class="px-4 py-2 font-medium whitespace-nowrap">
                            <a class="text-indigo-600! px-1" href="{{ route('owner.item.edit', ['item' => $item]) }}">商品編集</a> |
                            <a class="text-indigo-600! px-1" href="{{ route('owner.item.image.edit', ['item' => $item]) }}">画像</a> |
                            <a class="text-indigo-600! px-1" href="{{ route('owner.item.stock.create', ['item' => $item]) }}">在庫</a> |
                            <form 
                              x-data="{ submitting: false}"
                              @submit.prevent="
                                if(!submitting && confirm('この商品を販売停止にしても宜しいですか？')) {
                                  submitting = true;
                                  $el.submit();
                                } 
                              "
                              action="{{ route('owner.item.toggleIsSelling', ['item' => $item]) }}"
                              method="post"
                              class="delete-form"
                            >
                              @csrf
                              @method('PATCH')
                              <button type="submit" class="text-red-500! px-1 cursor-pointer">停止</button>
                            </form>
                          </td>
                        @else
                          <td class="px-4 py-2 font-medium whitespace-nowrap text-center">
                              <form
                                x-data="{ submitting: false }"
                                @submit.prevent="
                                  if(!submitting && confirm('この商品を販売再開しますか？')){
                                    submitting = true;
                                    $el.submit();
                                  } 
                                "
                                action="{{ route('owner.item.toggleIsSelling', ['item' => $item]) }}"
                                method="post"
                              >
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="text-white bg-gray-400 px-5 py-1 rounded cursor-pointer">販売再開</button>
                              </form>
                          </td>
                        @endif


                    </tr>
                  @empty
                    <tr>
                      <td colspan="3">
                        <p class="text-gray-500 text-center text-base py-3">商品がみつかりません</p>
                      </td>
                    </tr>
                  @endforelse

                </tbody>
            </table>
        </div>

        <nav class="flex flex-col items-start justify-between p-4 space-y-3 md:flex-row md:items-center md:space-y-0" aria-label="Table navigation">
          {{ $items->links() }}
        </nav>
      </div>
    </div>
  </div>
</main>

@endsection