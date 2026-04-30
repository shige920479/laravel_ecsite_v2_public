@extends('layouts.app')

@section('content')
<x-admin-headline />

<section class="login-wrapper">
  <div class="login-box">
    <h3 class="text-lg font-bold">サイト管理者ログイン</h3>
    <form action="{{ route('admin.login.submit') }}" method="post">
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
      </ul>
    </form>
  </div>
</section>

@endsection