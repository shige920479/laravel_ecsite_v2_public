@extends('layouts.app')
@section('content')

<x-admin-headline />

<section class="login-wrapper">
  <div class="login-box">
    <h3>ショップオーナー登録</h3>
    <form action="{{ route('admin.owners.store') }}" method="post">
      @csrf
      <ul>
        <div class="input account">
        <div>
            <label for="name">名前</label>
          </div>
          <input type="text" name="name" id="name" value="{{ old('name') }}"/>
          <x-input-error :messages="$errors->get('name')" className="-mb-4!"/>
        </div>
        <div class="input account">
          <div>
            <label for="email">メールアドレス</label>
          </div>
          <input type="email" name="email" id="email" value="{{ old('email') }}"/>
          <x-input-error :messages="$errors->get('email')" className="-mb-4!"/>
        </div>
        <div class="input account">
          <div>
            <label for="password">パスワード</label>
          </div>
          <input type="password" name="password" id="password" />
          <x-input-error :messages="$errors->get('password')" className="-mb-4!"/>
        </div>
        <div class="input">
          <label for="confirm_password">パスワード（確認用）</label>
          <input type="password" name="password_confirmation" id="confirm_password" />
        </div>
        <button type="submit">新規オーナー登録</button>
      </ul>
    </form>
  </div>
</section>
@endsection