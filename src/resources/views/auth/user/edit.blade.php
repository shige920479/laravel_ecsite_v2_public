@extends('layouts.app')

@section('content')
<x-headline />
<div class="max-w-2xl mx-auto py-10">
  <h1 class="text-2xl font-bold mb-6">アカウント情報</h1>
    {{-- 未登録時の警告 --}}
  @if (! auth()->user()->isRegistered())
  <div class="mb-6 p-4 bg-red-100 text-red-700 rounded">
    ⚠ 商品を購入するにはプロフィール登録が必要です
  </div>
  @endif

  <x-flash-message status="session('status')" />

  <form method="POST" action="{{ route('mypage.account.update', ['user' => $user]) }}">
    @csrf
    @method('PATCH')

    <div class="space-y-6">
      <div>
        <label class="block">
          ニックネーム
          <small class="ml-1.5 text-blue-500">※任意</small>
        </label>
        <input type="text" name="nickname"
          value="{{ old('nickname', $user->nickname) }}"
          class="w-full border border-gray-400 rounded px-3 py-2">
        @error('nickname')
          <p class="text-red-500 text-sm">{{ $message }}</p>
        @enderror
      </div>

      <div>
        <label class="block">
          郵便番号
          <small class="ml-1.5 text-red-500">※必須</small>
        </label>
        <x-input-error :messages="$errors->get('postcode')"/>
        <input
          type="text"
          id="postcode"
          name="postcode"
          value="{{ old('postcode', $user->postcode) }}"
          class="border border-gray-400 rounded px-3 py-2"
        >
        <button
          type="button"
          id="searchAddress"
          class="bg-gray-400 text-white font-bold rounded px-3 py-2 cursor-pointer"
        >
          住所検索
        </button>
      </div>
      

      <div>
        <label class="block">
          住所
          <small class="ml-1.5 text-red-500">※必須</small>
        </label>
        <x-input-error :messages="$errors->get('address')"/>
        <input 
          type="text"
          id="address"
          name="address"
          value="{{ old('address', $user->address) }}"
          class="w-full border border-gray-400 rounded px-3 py-2">
      </div>

      <div>
        <label class="block">
          電話番号
          <small class="ml-1.5 text-red-500">※必須</small>
        </label>
        <x-input-error :messages="$errors->get('phone')"/>
        <input type="text" name="phone"
          value="{{ old('phone', $user->phone) }}"
          class="w-full border border-gray-400 rounded px-3 py-2">
      </div>
    </div>

    <div class="mt-8 flex justify-between items-center">
      <a href="{{ route('home.index') }}" class="text-gray-500">← 戻る</a>
      <button 
        type="submit" 
        class="bg-gray-600 text-white px-6 py-2 font-bold rounded hover:bg-gray-700 cursor-pointer"
      >
          この内容で保存する
      </button>
    </div>
  </form>
</div>
@endsection