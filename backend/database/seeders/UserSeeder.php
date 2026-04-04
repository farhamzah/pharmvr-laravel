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
        // Default Test User for Frontend
        User::updateOrCreate(
            ['email' => 'test@pharmvr.com'],
            [
                'name' => 'Test Student',
                'password' => Hash::make('Password123!'),
                'role' => 'user',
            ]
        );

        User::updateOrCreate(
            ['email' => 'nana@ui.com'],
            [
                'name' => 'Nana',
                'password' => Hash::make('Farhamzah34#'),
                'role' => 'user',
            ]
        );

        // General random users for UI
        User::factory()->count(5)->create();
    }
}
