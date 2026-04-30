@extends('layouts.app')

@section('content')
<x-headline />
<div class="max-w-2xl mx-auto mt-10 px-4 text-center">
    <h1 class="text-2xl font-bold mb-4">
        決済は完了していません
    </h1>
    <p class="text-gray-600 mb-6 leading-relaxed">
        もう一度お試しいただくか、<br>
        カートからご購入手続きを行ってください。
    </p>
    <div class="flex justify-center gap-4">
        <a href="{{ route('cart.index')}}"
           class="px-6 py-2 bg-gray-500 text-white! rounded-md hover:bg-gray-400 transition">
            <div class="text-lg">🛒 カートに戻る</div>
        </a>
        <a href="{{ route('home.index') }}"
           class="text-lg px-10 py-2 bg-gray-200 text-gray-500! rounded-md hover:bg-gray-300 transition">
            トップに戻る
        </a>
    </div>
</div>
@endsection