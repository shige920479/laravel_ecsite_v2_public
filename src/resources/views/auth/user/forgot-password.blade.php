@extends('layouts.app')

@section('content')
<x-headline />

  <section class="login-wrapper">
    <div class="login-box">
      @if (session('status'))
        <div class="bg-green-400 text-white text-center mb-5 py-1 rounded">
          {{ session('status') }}
        </div>
      @endif
      <h3>パスワードリセット</h3>
      <small class="text-blue-600">ご登録のメールアドレスを入力して送信してください</small>
      <form action="{{ route('password.email') }}" method="post">
        @csrf
        <div class="input">
          <label for="email">メールアドレス</label>
          <input type="email" name="email" id="email" required/>
          <x-input-error :messages="$errors->get('email')" />
        </div>
        <button type="submit">送信する</button>
      </form>
    </div>
  </section>

@endsection