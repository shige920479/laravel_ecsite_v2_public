@extends('layouts.app')

@section('content')
<x-admin-headline />

<div class="bg-gray-100 min-h-screen">
  <div class="max-w-6xl mx-auto px-4 py-6">
    <div class="bg-white rounded-lg shadow p-6">
      <x-flash-message message="session('status')"/>
      <form action="" method="get" class="flex items-end gap-3 mb-3">
        <div>
          <label for="" class="block text-xs">投稿者 / 商品名 / レビュー本文</label>
          <input type="text" name="search_word" value="{{ $query['search_word'] ?? '' }}" class="pl-5 pr-3 py-2 w-52 text-sm border border-gray-300! rounded-lg!">
        </div>
        <div>
          <label for="" class="block text-xs">評価</label>
          <select name="rating" class="pl-5 pr-3 py-2 h-auto! w-48 text-sm border border-gray-300! rounded-lg!">
            <option value="" class="text-gray-400!">選択してください</option>
            <option value="5" @selected((int)($query['rating'] ?? '') === 5)>評価5</option>
            <option value="4" @selected((int)($query['rating'] ?? '') === 4)>評価4</option>
            <option value="3" @selected((int)($query['rating'] ?? '') === 3)>評価3</option>
            <option value="2" @selected((int)($query['rating'] ?? '') === 2)>評価2</option>
            <option value="1" @selected((int)($query['rating'] ?? '') === 1)>評価1</option>
          </select>
        </div>
        <button type="submit" class="px-4 py-2 text-sm bg-gray-700 text-white rounded-lg hover:bg-gray-800 hover:cursor-pointer" >
          検索
        </button>
      </form>
      <table class="w-full text-sm text-left text-gray-500 mb-4">
        <thead class="text-xs text-gray-700 uppercase bg-gray-100">
          <tr>
            <th scope="col" class="text-left p-2">投稿ID</th>
            <th scope="col" class="text-left p-2">商品名</th>
            <th scope="col" class="text-left p-2">投稿者</th>
            <th scope="col" class="text-left p-2">投稿タイトル</th>
            <th scope="col" class="text-left p-2">評価</th>
            <th scope="col" class="text-left p-2"> 投稿日</th>
            <th scope="col" class="text-left p-2">操作</th>
          </tr>
        </thead>
        <tbody>
          @foreach($reviews as $review)
            <tr class="border-b border-gray-200 hover:bg-gray-100">
              <td class="p-2">{{ $review->id }}</td>
              <td class="p-2">{{ $review->item->name }}</td>
              <td class="p-2">{{ $review->user->name }}</td>
              <td class="p-2"><a href="{{ route('admin.review.show', ['review' => $review->id]) }}" class="text-indigo-500! hover:underline!">{{ $review->title }}</a></td>
              <td class="p-2">
                <div class="stars text-xs!">
                  <div class="stars-filled" style="width: {{ ($review->star ?? 0) / 5 * 100 }}%">
                  </div>
                </div>
                <span class="text-xs ml-1">【{{ $review->star ?? "星無し" }}】</span>
              </td>
              <td class="p-2">{{ $review->created_at->format('Y/m/d') }}</td>
              <td class="p-2">
                <form
                  x-data="{ submitting: false }"
                  @submit.prevent="
                    if (! submitting && confirm('このレビューを削除しますが宜しいですか？')) {
                      submitting = true;
                      $el.submit();
                    }"
                  action="{{ route('admin.review.destroy', ['review' => $review]) }}"
                  method="post"
                >
                  @csrf
                  @method('DELETE')
                  @can('review.delete')
                    <button
                      type="submit"
                      class="text-xs text-white px-3 py-1.5 bg-red-400 rounded hover:cursor-pointer"
                    >
                      削除
                    </button>
                  @else
                    <button
                      type="button"
                      disabled
                      class="text-xs text-white px-3 py-1.5 bg-gray-300 rounded"
                    >
                      権限がありません
                    </button>
                  @endcan
                </form>
              </td>
            </tr>
          @endforeach
        </tbody>
      </table>
      {{ $reviews->links() }}
    </div>
  </div>
</div>
@endsection
