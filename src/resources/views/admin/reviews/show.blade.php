@extends('layouts.app')

@section('content')
<x-admin-headline/>

<div class="bg-gray-100 min-h-screen ">
  <div class="max-w-3xl mx-auto px-4 py-6">
    <div class="bg-white rounded-lg shadow text-gray-500 p-10">
      <dl class="grid grid-cols-[150px_1fr] gap-y-7 mb-5">
        <dt class="font-semibold">タイトル</dt>
        <dd>{{ $review->title }}</dd>
        <dt class="font-semibold">商品名</dt>
        <dd>{{ $review->item->name }}</dd>
        <dt class="font-semibold">投稿者</dt>
        <dd>{{ $review->user->name }}</dd>
        <dt class="font-semibold">投稿内容</dt>
        <dd>{{ $review->review }}</dd>
        <dt class="font-semibold">評価</dt>
        <dd>
          <div class="stars text-xs!">
            <div class="stars-filled" style="width: {{ ($review->star ?? 0) / 5 * 100 }}%">
            </div>
          </div>
          <span class="text-xs ml-1">【{{ $review->star ?? "星無し" }}】</span>
        </dd>
        <dt class="font-semibold">投稿日</dt>
        <dd>{{ $review->created_at->format('Y年m月d日 H時i分') }}</dd>
      </dl>
      <div class="flex justify-between items-center">
        <a href="{{ url()->previous() }}" class="link-text mt-0! cursor-pointer">
          <span><img class="w-10" src="{{ asset('images/prev.png') }}" alt=""></span>
          <span>前のページへ戻る</span>
        </a>
          <form
            x-data="{ submitting: false }"
            @submit.prevent="
              if (! submitting && confirm('このレビューを削除しても宜しいですか？')) {
                submitting = true;
                $el.submit();
              }
            "
            action="{{ route('admin.review.destroy', ['review' => $review]) }}"
            method="post"
          >
            @csrf
            @method('DELETE')
            @can('review.delete')
              <button 
                type="submit"
                class="bg-red-400 text-white px-10 py-2 rounded font-bold hover:cursor-pointer"
                >
                  削除する
              </button>
            @else
              <button
                type="button"
                disabled
                class="text-xs text-white px-3 py-1.5 bg-gray-300 rounded"
              >
                削除権限がありません
              </button>
            @endcan
          </form>
      </div>
    </div>
  </div>
</div>

@endsection