<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('users')->insert([
            'name' => 'user',
            'email' => 'user@mail.com',
            'password' => Hash::make('user123'),
            'postcode' => fake()->postcode(),
            'address' => fake()->address(),
            'phone' => fake()->phoneNumber(),
        ]);

        User::factory()->registered()->count(99)->create();
    }
}
