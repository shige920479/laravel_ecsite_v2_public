@extends('layouts.app')
@section('content')

<x-headline />
<div id="top" class="wrapper">
  <form action="{{ route('items.ranking') }}" method="GET" x-data x-ref="form">
    <div class="ranking-filter">
      <div class="filter-item">
        <label>ランキング種別</label>
        <select name="rank_type" @change="$refs.form.submit()">
          @foreach (config('constants.ranking.type') as $key => $type)
            <option value="{{ $key }}" @selected(request('rank_type') === $key)>
              {{ $type['label'] }}
            </option>
          @endforeach
        </select>
      </div>
      <div class="filter-item">
        <label>期間</label>
        <select name="period" @change="$refs.form.submit()"
          @if(request('rank_type') === 'review') 
            disabled style="color:white"
          @endif>
          <option 
            value="{{ config('constants.ranking.period.weekly') }}"
            @selected((int)request('period') === config('constants.ranking.period.weekly'))
            >過去1週間
          </option>
          <option 
            value="{{ config('constants.ranking.period.monthly') }}"
            @selected((int)request('period') === config('constants.ranking.period.monthly'))
            >過去30日間
          </option>
        </select>
        <input type="hidden" name="slug" value="{{ request('slug') }}">
      </div>
    </div>  

    <div class="category-tabs">
      <button type="submit" name="slug" value="" class="{{ request('slug') === null ? 'bg-gray-800 text-white disabled' : 'hover:bg-gray-200 cursor-pointer'}} text-xs px-4 py-2 rounded-full">
        全てのカテゴリー
      </button>
      @foreach ($categories as $category)
          <button type="submit" name="slug" value="{{ $category->slug }}"
            class="{{ $category->slug === request('slug') ? 'bg-gray-800 text-white' : 'hover:bg-gray-200 cursor-pointer disabled' }} text-xs px-4 py-2 rounded-full">
            {{ $category->name }}
          </button>
      @endforeach
      <button class="text-xs px-4 py-2 rounded-full hover:bg-gray-200 cursor-pointer disabled">バッグ</button>
      <button class="text-xs px-4 py-2 rounded-full hover:bg-gray-200 cursor-pointer disabled">入浴剤</button>
      <button class="text-xs px-4 py-2 rounded-full hover:bg-gray-200 cursor-pointer disabled">その他雑貨</button>
    </div>
  </form>
  <ul class="product-list">
    @forelse ($items as $index => $item)
    <li class="ranking border-2 border-gray-200 px-3 pt-15 pb-3 rounded">
      <div class="ranking-badge rank-{{$index + 1}}">
        {{ $index + 1 }}
      </div>

      <a href="{{ route('item.show', ['item' => $item->id]) }}" class="card-link"></a>

      <div class="card-content">

          <img src="{{ $item->mainImageUrl }}" alt="" />
          <p>{{ $item->name }}</p>
          <p>{{ $item->shop->name }}</p>
          <p>&yen;{{ number_format($item->priceTaxIn) }}<small>(税込)</small></p> 


        @if(isset($item->reviews_count))
          <span class="text-gray-400 text-xs">({{ $item->reviews_count }}件のレビュー)</span>
        @endif
        <div class="stars">
          <div class="stars-filled" style="width:{{ $item->avg_star ? $item->avg_star / 5 * 100 : 0}}%"></div>
          <span class="text-xs">{{ round($item->avg_star, 1) }}</span>
        </div>
        <div>
          <small>閲覧数: {{$item->view_counts ?? 0}} ビュー</small>
          <small>販売数: {{$item->sales ?? 0}} 個</small>
        </div>
      </div>

    </li>
    @empty
    <p>商品が見つかりません</p>
    @endforelse
  </ul>
</div>
@endsection