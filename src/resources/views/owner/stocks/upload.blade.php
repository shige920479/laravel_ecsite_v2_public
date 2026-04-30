@extends('layouts.app')

@section('content')
<x-headline />

<div class="max-w-lg mx-auto mt-8">


<h3 class="mb-4">在庫 一括登録（CSV）</h3>

  <p class="text-sm text-gray-600 mb-4">
    CSVファイルをアップロードして在庫を一括登録します。<br>
    1行でもエラーがある場合、全件登録されません。
  </p>

  <div class="mb-6 text-sm">
    <p class="font-semibold mb-1">CSVフォーマット</p>
    <pre class="bg-gray-100 p-3 rounded">
  item_id,quantity,reason
  12,10,初期入荷
  15,-3,棚卸調整
    </pre>
  </div>

  <x-input-error :messages="$errors->get('csv')" />
  <form action="{{ route('owner.stocks.csv.store') }}" method="post" enctype="multipart/form-data" class="space-y-4">
    @csrf
    <input type="file" name="csv" accept=".csv" class="cursor-grab px-3 text-sm border border-gray-300 rounded-lg" required>
    <button class="px-4 py-2 bg-indigo-600 text-white rounded cursor-pointer">
      アップロード
    </button>
  </form>

  <div class="mt-4">
    <a href="{{ route('owner.item.index') }}" class="text-sm text-indigo-600">
      ← 商品一覧へ戻る
    </a>
  </div>
</div>
@endsection