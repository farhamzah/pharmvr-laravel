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
        // Default Test User for Frontend (dev/testing only)
        User::updateOrCreate(
            ['email' => 'test@pharmvr.com'],
            [
                'name' => 'Test Student',
                'password' => Hash::make('DevTest@2026!'),
                'role' => 'user',
            ]
        );

        User::updateOrCreate(
            ['email' => 'student@pharmvr.com'],
            [
                'name' => 'Demo Student',
                'password' => Hash::make('DevTest@2026!'),
                'role' => 'user',
            ]
        );

        // General random users for UI
        User::factory()->count(5)->create();
    }
}
