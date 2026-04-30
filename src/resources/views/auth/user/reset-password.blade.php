@extends('layouts.app')

@section('content')
<x-headline />

  <section class="login-wrapper">
    <div class="login-box">
      <h3>パスワード再登録</h3>
      <small class="text-blue-600">こちらに新しいパスワードを入力し送信してください</small>
      <form action="{{ route('password.update') }}" method="post">
        @csrf
        <input type="hidden" name="token" value="{{ request()->route('token') }}">
      <ul>
        <div class="input account">
          <div>
            <label for="email">メールアドレス</label>
          </div>
          <input type="email" name="email" id="email" value="{{ old('email') }}"/>
          <div class="-mb-3"><x-input-error :messages="$errors->get('email')"/></div>
        </div>
        <div class="input account">
          <div class="flex gap-2">
            <label for="password">パスワード</label>
            <p class="text-xs text-gray-500">
              ※8文字以上・英・数字を含めてください
            </p>
          </div>
          <input type="password" name="password" id="password" />
          <div class="-mb-3"><x-input-error :messages="$errors->get('password')"/></div>
        </div>
        <div class="input">
          <label for="confirm-password">パスワード（確認用）</label>
          <input type="password" name="password_confirmation" id="confirm-password" />
        </div>
        <button type="submit">更新する</button>
      </ul>
      </form>
    </div>
  </section>

@endsection