<?php

namespace App\Http\Controllers\Admin;

use App\Exceptions\ExistInDeletedAdmins;
use App\Exceptions\NotModifiedException;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAdminRequest;
use App\Http\Requests\UpdateAdminRequest;
use App\Models\Admin;
use App\Services\Admin\AdminServiceInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Spatie\Permission\Models\Role;

class AdminController extends Controller
{
    public function __construct(private AdminServiceInterface $adminService)
    {
    }

    public function index()
    {
        Gate::authorize('admin.view');
        $admins = Admin::query()->with('roles')->orderBy('id')->paginate(10);

        return view('admin.super-admin.index', ['admins' => $admins]);
    }

    public function create()
    {
        $roles = Role::all();
        return view('admin.super-admin.create', ['roles' => $roles]);
    }


    /**
     * contains() 使いかた
     *  toArray(), all()使い分け？
     * 
     * Notion
     *  コレクションと配列の比較方法、
     */
    public function store(StoreAdminRequest $request)
    {
        Gate::authorize('admin.create');

        try {
            $adminName = $this->adminService->store($request->validated());

        } catch (ExistInDeletedAdmins $e) {
            return to_route('admin.admins.trashed')->with([
                'status' => 'alert',
                'message' => $e->getMessage()
            ]);

        } catch (\Throwable $e) {
            return redirect()->back()->with([
                'status' => 'alert',
                'message' => '予期せぬシステムエラーが発生しました: ' . $e->getMessage()
            ]);
        }

        return to_route('admin.admins.index')->with([
            'status' => 'info',
            'message' => "新規管理者：{$adminName} を登録しました",
        ]);
    }

    public function edit(Admin $admin)
    {
        Gate::authorize('admin.update');

        $admin->load('roles');
        $roles = Role::all();
        return view('admin.super-admin.edit', [
            'admin' => $admin,
            'roles' => $roles,
        ]);
    }

    public function update(UpdateAdminRequest $request, Admin $admin)
    {
        Gate::authorize('admin.update');

        try {
            $admin = $this->adminService->update(
                $admin, 
                $request->only(['name', 'email']),
                $request->roles
            );
            
        } catch (NotModifiedException $e) {
            return redirect()->back()->with([
                'status' => 'alert',
                'message' => $e->getMessage()
            ]);

        } catch (\Throwable $e) {
            Log::error('システムエラー' . $e->getMessage());
            return redirect()->back()->with([
                'status' => 'alert',
                'message' => '予期せぬシステムエラーが発生しました: ' . $e->getMessage()
            ]);
        }

        return to_route('admin.admins.edit', ['admin' => $admin])->with([
            'status' => 'info',
            'message' => '管理者情報を変更しました'
        ]);
    }

    public function destroy(Admin $admin)
    {
        Gate::authorize('admin.delete');
        $adminName = $admin->name;

        $admin->delete();

        return to_route('admin.admins.index')->with([
            'status' => 'info',
            'message' => "管理者：{$adminName} の管理権限を停止しました"
        ]);
    }

    public function trashed()
    {
        Gate::authorize('admin.update');
        $trashedAdmins = Admin::query()->onlyTrashed()->get();

        return view('admin.super-admin.trashed', ['admins' => $trashedAdmins]);
    }

    public function restore(int $id)
    {
        Gate::authorize('admin.update');
        $admin = Admin::onlyTrashed()->findOrFail($id);
        $admin->restore();

        return to_route('admin.admins.index')->with([
            'status' => 'info',
            'message' => "管理者：{$admin->name} を再有効化しました",
        ]);
    }
}
