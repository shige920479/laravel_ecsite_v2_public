@extends('layouts.app')

@section('content')
<x-headline />

  <section class="login-wrapper">
    <div class="login-box">
    @if (session('status') == 'verification-link-sent')
      <div class="mb-7 text-indigo-600">認証メールを再送信しました</div>
      <small>メールが届かない場合は、以下のボタンより再送信願います</small>
    @else
      <div class="mb-7 text-sm">
        <p class="text-purple-500 font-bold">
          新規ご登録ありがとうございます。<br>
          ご登録のメールに認証リンクを送信しました。<br>
        </p>
        <p class="mt-2">お手数ですが、リンクをクリックし登録を完了させてください。</p>
        <small class="text-gray-500">※このページは自動で閉じませんのでご留意ください</small>
      </div>
      <small>メールが届かない場合は、以下のボタンより再送信願います</small>
    @endif

    <form method="POST" action="{{ route('verification.send') }}">
      @csrf
      <button type="submit">認証メールを再送信</button>
    </form>
    </div>
  </section>

@endsection