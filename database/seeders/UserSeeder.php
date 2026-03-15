<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::create([
            'name' => 'Rowan',
            'email' => 'rowan@vivala.com',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);

        User::create([
            'name' => 'Ben',
            'email' => 'ben@vivala.com',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);

        User::factory()
            ->count(100)
            ->create([
                'password' => Hash::make('password'),
            ]);
    }
}
