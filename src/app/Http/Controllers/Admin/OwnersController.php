<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreOwnerRequest;
use App\Http\Requests\UpdateOwnerRequest;
use App\Models\Owner;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;

class OwnersController extends Controller
{
    public function index()
    {
        $owners = Owner::query()->orderBy('id')->paginate(10);

        return view('admin.owners.index', ['owners' => $owners]);
    }

    public function create()
    {
        Gate::authorize('owner.create');
        return view('admin.owners.create');
    }

    public function store(StoreOwnerRequest $request)
    {
        Gate::authorize('owner.create');
        $validated = $request->validated();
        $validated['password'] = Hash::make($validated['password']);
        $owner = Owner::create($validated);

        return to_route('admin.owners.index')->with([
            'status' => 'info',
            'message' => "新規オーナー（{$owner->name}）を登録しました"
        ]);
    }

    public function edit(Owner $owner)
    {
        Gate::authorize('owner.update');
        return view('admin.owners.edit', ['owner' => $owner]);
    }

    public function update(UpdateOwnerRequest $request, Owner $owner)
    {
        Gate::authorize('owner.update');
        $owner->fill($request->validated());
        if ($owner->isClean()) {
            return back()->with([
                'status' => 'alert',
                'message' => '登録内容に変更がありません、ご確認願います' 
            ]);
        }
        $owner->save();

        return to_route('admin.owners.index')->with([
            'status' => 'info',
            'message' => "オーナー情報（{$owner->name}）を変更しました"
        ]);
    }

    public function destroy(Owner $owner)
    {
        Gate::authorize('owner.delete');
        $message = "id: {$owner->id} / 名前: {$owner->name} を削除しました";
        $owner->delete();
        return to_route('admin.owners.index')->with([
            'status' => 'info',
            'message' => $message,
        ]);
    }
}
