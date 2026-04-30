@extends('layouts.app')
@section('content')

<x-admin-headline />
<section class="login-wrapper">
  <div class="login-box">
    <h3>ショップオーナー編集</h3>
    <x-flash-message status="session('status')" />
    <form action="{{ route('admin.owners.update', ['owner' => $owner]) }}" method="post">
      @csrf
      @method('PUT')
      <ul>
        <div class="input account">
          <div>
            <label for="name">名前</label>
          </div>
          <input type="text" name="name" id="name" value="{{ old('name', $owner->name) }}"/>
          <x-input-error :messages="$errors->get('name')" className="-mb-4!"/>
        </div>
        <div class="input account">
          <div>
            <label for="email">メールアドレス</label>
          </div>
          <input type="email" name="email" id="email" value="{{ old('email', $owner->email) }}"/>
          <x-input-error :messages="$errors->get('email')" className="-mb-4!"/>
        </div>
        <button type="submit">変更する</button>
      </ul>
    </form>
  </div>
</section>
@endsection