@extends('layouts.app')

@section('content')
<x-headline />

  <section class="login-wrapper">

    <div id="direct-loing-box">
      <p>⬇️こちらからログインできます⬇️</p>
      <div id="direct-login-flex">
        <form action="{{ route('demo.userLogin') }}" method="post" class="direct-login-form">
          @csrf
          <button type="submit" class="direct-login-btn">User Login</button>
        </form>
        <form action="{{ route('demo.ownerLogin') }}" method="post" class="direct-login-form">
          @csrf
          <button type="submit" class="direct-login-btn">Owner Login</button>
        </form>
        <form action="{{ route('demo.adminLogin') }}" method="post" class="direct-login-form">
          @csrf
          <button type="submit" class="direct-login-btn">Admin Login</button>
        </form>
      </div>
    </div>

    <div class="login-box">
      <h3>ログイン</h3>
      <small>登録済みのお客様はこちらからログインしてください。</small>
      <form action="{{ route('login') }}" method="post">
        @csrf
        <ul>
          <div class="input">
            <label for="email">メールアドレス</label>
            <input type="email" name="email" id="email" value="{{ old('email') }}" />
            <x-input-error :messages="$errors->get('email')" />
          </div>
          <div class="input">
            <label for="password">パスワード</label>
            <input type="password" name="password" id="password" />
            <x-input-error :messages="$errors->get('password')" />
          </div>
          <button type="submit">Login</button>
          <div class="flex items-center">
            <input type="checkbox" name="remember" id="remember" class="cursor-pointer" checked>
            <label for="remember" class="mb-0! ml-2 cursor-pointer">ログイン状態を保持する</label>
          </div>
        </ul>
        
      </form>
      <div id="to-register">
        <span>アカントが未登録ですか？</span>
        <a href="{{ route('register.store') }}" class="auth-link underline!">→ アカウントを作成</a>
      </div>

    </div>
    <div class="text-center -mt-2">
      @if (Route::has('password.request'))
        <a href="{{ route('password.request') }}" class="text-xs text-blue-500 underline!">
          パスワードをお忘れですか？
        </a>
      @endif
      </div>
  </section>

@endsection