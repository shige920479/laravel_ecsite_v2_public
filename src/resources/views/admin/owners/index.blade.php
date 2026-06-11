@extends('layouts.app')
@section('content')

<x-admin-headline />

<div class="admin-wrapper">
  <div class="admin-home">
    <h3>オーナー情報</h3>
    <x-flash-message status="session('status')" />

    <div id="owner-info">
      <table>
        <thead>
          <tr>
            <th id="th-1">オーナー名</th>
            <th id="th-2">メールアドレス</th>
            <th id="th-3">登録日</th>
            <th id="th-4">変更</th>
            <th id="th-5">停止</th>
          </tr>
        </thead>
        <tbody>
          @if($owners->isEmpty())
            <tr><td colspan="5">オーナー情報は未登録です</td></tr>
          @else
            @foreach ($owners as $owner)
              <tr>
                <td class="td-1">{{ $owner->name }}</td>
                <td class="td-2">{{ $owner->email }}</td>
                <td class="td-3">{{ $owner->updated_at }}</td>
                <td class="td-4">
                  @can('owner.update')
                    <a href="{{ route('admin.owners.edit', ['owner' => $owner]) }}">編集</a>
                  @else
                    <span class="text-xs font-bold text-white bg-gray-300! w-full h-full flex items-center justify-center rounded">権限なし</span>
                  @endcan
                </td>
                <td class="td-5">
                  <form
                    x-data
                    @submit.prevent="if (confirm('削除してよろしいですか？')) $el.submit()"
                    action="{{ route('admin.owners.destroy', ['owner' => $owner]) }}"
                    method="post"
                  >
                    @csrf
                    @method('DELETE')
                    @can('owner.delete')
                      <button type="submit" class="del-btn cursor-pointer">削除</button>
                    @else
                      <button type="button" disabled class="text-xs text-white bg-gray-300! pointer-events-none!">権限なし</button>
                    @endcan
                  </form>
                </td>
              </tr>
            @endforeach
          @endif
        </tbody>
      </table>
    </div>
    <div class="mt-3">{{ $owners->links() }}</div>
  </div>
</div>
@endsection