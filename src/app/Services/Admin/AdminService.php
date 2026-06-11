<?php
namespace App\Services\Admin;

use App\Exceptions\ExistInDeletedAdmins;
use App\Exceptions\NotModifiedException;
use App\Models\Admin;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class AdminService implements AdminServiceInterface
{
    public function store(array $data): string
    {
        if ($this->existInDeleted($data['email'])) {
            throw new ExistInDeletedAdmins();
        }

        $newAdmin = DB::transaction(function () use ($data) {
            $admin = Admin::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => Hash::make($data['password'])
            ]);
            $admin->assignRole($data['roles']);

            return $admin;
        });

        return $newAdmin->name;
    }

    public function update(Admin $admin, array $inputs, array $roles): Admin
    {
        $admin->fill($inputs);

        $profileChanged = $admin->isDirty();
        $rolesChanged = $this->rolesChanged($admin, $roles);

        if (! $profileChanged && ! $rolesChanged) {
            throw new NotModifiedException();
        }

        if ($profileChanged) {
            $admin->save();
        }

        if ($rolesChanged) {
            $admin->syncRoles($roles);
        }
        return $admin->refresh()->load('roles');
    }

    private function existInDeleted(string $email): bool
    {
        return Admin::onlyTrashed()->where('email', $email)->exists();
    }

    private function rolesChanged(Admin $admin, array $roles)
    {
        $currentRoles = $admin->roles->pluck('name')->sort()->values()->all();
        $newRoles = collect($roles)->sort()->values()->all();

        return $newRoles !== $currentRoles;
    }
}



