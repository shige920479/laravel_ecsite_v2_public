<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class OwnerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('owners')->insert([
            [
                'name' => 'owner1',
                'email' => 'owner1@mail.com',
                'password' => Hash::make('owner123')
            ],
            [
                'name' => 'owner2',
                'email' => 'owner2@mail.com',
                'password' => Hash::make('owner123')
            ],
            [
                'name' => 'owner3',
                'email' => 'owner3@mail.com',
                'password' => Hash::make('owner123')
            ],
            [
                'name' => 'owner4',
                'email' => 'owner4@mail.com',
                'password' => Hash::make('owner123')
            ],
            [
                'name' => 'owner5',
                'email' => 'owner5@mail.com',
                'password' => Hash::make('owner123')
            ],
        ]);
    }
}
