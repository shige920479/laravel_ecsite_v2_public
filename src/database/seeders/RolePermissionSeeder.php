<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]
            ->forgetCachedPermissions();

        $adminView = Permission::findOrCreate(
            'admin.view',
            'web_admin',
        );

        $adminCreate = Permission::findOrCreate(
            'admin.create',
            'web_admin',
        );

        $adminUpdate = Permission::findOrCreate(
            'admin.update',
            'web_admin',
        );

        $adminDelete = Permission::findOrCreate(
            'admin.delete',
            'web_admin',
        );

        $reviewDelete = Permission::findOrCreate(
            'review.delete',
            'web_admin'
        );

        $ownerCreate = Permission::findOrCreate(
            'owner.create',
            'web_admin',
        );

        $ownerUpdate = Permission::findOrCreate(
            'owner.update',
            'web_admin',
        );

        $ownerDelete = Permission::findOrCreate(
            'owner.delete',
            'web_admin',
        );

        $categoryCreate = Permission::findOrCreate(
            'category.create',
            'web_admin',
        );

        $categoryUpdate = Permission::findOrCreate(
            'category.update',
            'web_admin',
        );

        $superAdmin = Role::findOrCreate(
            'super_admin',
            'web_admin'
        );

        $reviewManager = Role::findOrCreate(
            'review_manager',
            'web_admin'
        );

        $ownerManager = Role::findOrCreate(
            'owner_manager',
            'web_admin',
        );

        $categoryManager = Role::findOrCreate(
            'category_manager',
            'web_admin',
        );

        $superAdmin->syncPermissions([
            $adminView,
            $adminCreate,
            $adminUpdate,
            $adminDelete,
            $reviewDelete,
            $ownerCreate,
            $ownerUpdate,
            $ownerDelete,
            $categoryCreate,
            $categoryUpdate,
        ]);

        $reviewManager->syncPermissions([
            $reviewDelete,
        ]);

        $ownerManager->syncPermissions([
            $ownerCreate,
            $ownerUpdate,
            $ownerDelete,
        ]);

        $categoryManager->syncPermissions([
            $categoryCreate,
            $categoryUpdate,
        ]);
    }
}
