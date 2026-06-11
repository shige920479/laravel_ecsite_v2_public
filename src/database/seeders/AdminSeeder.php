<?php

namespace Database\Seeders;

use App\Models\Admin;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $superAdmin = Admin::create([
            'name' => 'admin',
            'email' => 'admin@mail.com',
            'password' => Hash::make('admin123')
        ]);
        $reviewAdmin = Admin::create([
            'name' => 'review_admin',
            'email' => 'review_admin@mail.com',
            'password' => Hash::make('admin123')
        ]);
        $categoryAdmin = Admin::create([
            'name' => 'category_admin',
            'email' => 'category_admin@mail.com',
            'password' => Hash::make('admin123')
        ]);
        $ownerAdmin = Admin::create([
            'name' => 'owner_admin',
            'email' => 'owner_admin@mail.com',
            'password' => Hash::make('admin123')
        ]);

        $superAdmin->assignRole('super_admin');
        $reviewAdmin->assignRole('review_manager');
        $ownerAdmin->assignRole('owner_manager');
        $categoryAdmin->assignRole('category_manager');
    }
}
