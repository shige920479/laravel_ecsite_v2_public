@extends('layouts.app')
@section('content')

<x-admin-headline />

<section class="login-wrapper">
  <div class="login-box">
    <h3>管理者 新規登録</h3>
    <x-flash-message status="session('status')"/>
    <form action="{{ route('admin.admins.store') }}" method="post">
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
        <div class="admin-checkbox-group p-0! mb-5">
          @foreach ($roles as $index => $role)
            <label class="checkbox-label text-sm!">
              <input 
                type="checkbox" 
                name="roles[]"
                value="{{ $role->name }}"
                class="h-auto!"
                @checked(in_array($role->name, old('roles', [])))
              >
                {{ $role->name }}
            </label>
          @endforeach
          <x-input-error :messages="$errors->get('roles')" />
          @foreach ($errors->get('roles.*') as $messages)
            <x-input-error :messages="$messages" />
          @endforeach
        </div>
        <button type="submit">新規管理者登録</button>
      </ul>
    </form>
  </div>
</section>
@endsection