@extends('layouts.app')

@section('content')
<x-admin-headline />
<div class="bg-gray-100 min-h-screen">
  <div class="max-w-4xl mx-auto px-4 py-6">
    <div class="bg-white rounded-lg shadow p-6">
      <div class="flex justify-between mb-5">
        <h2 class="text-xl font-bold text-gray-500">【権限停止中】システム管理者一覧</h2>
      </div>
      <x-flash-message message="session('status')"/>
      <table class="w-full table-auto text-sm text-left text-gray-500 mb-4">
        <thead class="text-xs text-gray-700 uppercase bg-gray-100">
          <tr>
            <th class="text-left p-2">ID</th>
            <th class="text-left p-2">管理者名</th>
            <th class="text-left p-2">Role</th>
            <th class="w-1 whitespace-nowrap p-2 text-center">編集</th>
          </tr>
        </thead>
        <tbody>
          @foreach ($admins as $admin)
            <tr>
              <td class="text-left px-2 py-4">{{ $admin->id }}</td>
              <td class="text-left px-2 py-4">{{ $admin->name }}</td>
              <td class="text-left px-2 py-4">{{ $admin->roles->pluck('name')->implode(', ') }}</td>
              <td class="text-left px-2 py-4">
                <form 
                  x-data="{ submitting: false }"
                  @submit.prevent="
                    if (! submitting && confirm('この管理者を再有効化しても宜しいですか？')) {
                      submitting = true;
                      $el.submit();
                    }
                  "
                  action="{{ route('admin.admins.restore', ['admin' => $admin]) }}"
                  method="post"
                >
                  @csrf
                  @method('PATCH')
                  <button
                    type="submit"
                    class="whitespace-nowrap text-white! bg-[#7e7ebf] font-bold px-6 py-2 rounded cursor-pointer">
                    再有効化
                  </button>
                </form>
              </td>
            </tr>
          @endforeach
        </tbody>
      </table>
    </div>
  </div>
</div>
@endsection
