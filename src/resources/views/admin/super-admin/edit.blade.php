@extends('layouts.app')

@section('content')
<x-admin-headline />
<div class="bg-gray-100 min-h-screen">
  <div class="max-w-lg mx-auto px-4 py-6">
    <div class="bg-white rounded-lg shadow p-6">
      <h2 class="text-xl font-bold text-gray-500">システム管理者編集</h2>
      <x-flash-message message="session('status')"/>
      <form action="{{ route('admin.admins.update', ['admin' => $admin]) }}" method="post">
        @csrf
        @method('PATCH')
        <h3 class="text-xs font-normal! text-gray-500 mt-5 mb-2">⚙️基本情報</h3>
          <hr class="text-gray-200">
          <div class="admin-form-group">
            <label for="name">名前</label>
            <input
                type="text"
                id="name"
                name="name"
                value="{{ old('name', $admin->name) }}"
            >
            <x-input-error :messages="$errors->get('name')" />
          </div>
          <div class="admin-form-group">
            <label for="email">メールアドレス</label>
            <input
                type="text"
                id="email"
                name="email"
                value="{{ old('email', $admin->email) }}"
            >
            <x-input-error :messages="$errors->get('email')" />
          </div>
        <h3 class="text-xs font-normal! text-gray-500 mt-5 mb-2">⚙️権限設定</h3>
        <hr class="text-gray-200">

        <div class="admin-checkbox-group">
          @foreach ($roles as $role)
            <label class="checkbox-label">
              <input 
                type="checkbox" 
                name="roles[]"
                value="{{ $role->name }}"
                @checked(in_array($role->name, old('roles', $admin->roles->pluck('name')->all())))
              >
                {{ $role->name }}
            </label>
          @endforeach
          <x-input-error :messages="$errors->get('roles')" />
          @foreach ($errors->get('roles.*') as $messages)
            <x-input-error :messages="$messages" />
          @endforeach
        </div>
        <button class="admin-edit-button">変更する</button>
      </form>
      <h3 class="text-xs font-normal! text-gray-500 mt-5 mb-2">⛔危険な操作</h3>
      <hr class="text-gray-200">
      <p class="mt-2">この管理者を無効化するとログインできなくなります。</p>
      <form 
        x-data="{ submitting: false }"
        @submit.prevent="
          if (! submitting && confirm('本当にこの管理者を停止しても宜しいですか？')) {
            submitting = true;
            $el.submit();
          }
        "
        action="{{ route('admin.admins.destroy', ['admin' => $admin]) }}"
        method="post"
      >
        @csrf
        @method('DELETE')
        <button type="submit" class="text-white bg-red-500 font-bold px-7 py-1 mt-2 rounded cursor-pointer">
          ⚠️管理者を停止する
        </button>
      </form>
    </div>
  </div>
</div>
@endsection