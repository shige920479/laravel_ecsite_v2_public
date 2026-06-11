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
      <x-flash-message status="session('status')"/>
      <h3>ログイン</h3>
      <small>登録済みのお客様はこちらからログインしてください。</small>
      <form action="{{ route('login') }}" method="post">
        @csrf
        <div>
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

          <a href="{{ route('auth.google.redirect') }}" class="gsi-material-button block mt-5">
            <div class="gsi-material-button-content-wrapper">
              <div class="gsi-material-button-icon">
                <svg version="1.1" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 48 48" xmlns:xlink="http://www.w3.org/1999/xlink" style="display: block;">
                  <path fill="#EA4335" d="M24 9.5c3.54 0 6.71 1.22 9.21 3.6l6.85-6.85C35.9 2.38 30.47 0 24 0 14.62 0 6.51 5.38 2.56 13.22l7.98 6.19C12.43 13.72 17.74 9.5 24 9.5z"></path>
                  <path fill="#4285F4" d="M46.98 24.55c0-1.57-.15-3.09-.38-4.55H24v9.02h12.94c-.58 2.96-2.26 5.48-4.78 7.18l7.73 6c4.51-4.18 7.09-10.36 7.09-17.65z"></path>
                  <path fill="#FBBC05" d="M10.53 28.59c-.48-1.45-.76-2.99-.76-4.59s.27-3.14.76-4.59l-7.98-6.19C.92 16.46 0 20.12 0 24c0 3.88.92 7.54 2.56 10.78l7.97-6.19z"></path>
                  <path fill="#34A853" d="M24 48c6.48 0 11.93-2.13 15.89-5.81l-7.73-6c-2.15 1.45-4.92 2.3-8.16 2.3-6.26 0-11.57-4.22-13.47-9.91l-7.98 6.19C6.51 42.62 14.62 48 24 48z"></path>
                  <path fill="none" d="M0 0h48v48H0z"></path>
                </svg>
              </div>
              <span class="gsi-material-button-contents">Google でログイン</span>
            </div>
          </a>

        </div>
        
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