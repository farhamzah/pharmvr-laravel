<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::updateOrCreate(
            ['email' => 'admin@pharmvr.com'],
            [
                'name' => 'Super Admin',
                'password' => Hash::make('Password123!'),
                'role' => 'admin',
            ]
        );
        $admin->assignRole('super-admin');

        $moderator = User::updateOrCreate(
            ['email' => 'moderator@pharmvr.com'],
            [
                'name' => 'Content Moderator',
                'password' => Hash::make('Password123!'),
                'role' => 'admin',
            ]
        );
        $moderator->assignRole('moderator');

        $viewer = User::updateOrCreate(
            ['email' => 'viewer@pharmvr.com'],
            [
                'name' => 'System Auditor',
                'password' => Hash::make('Password123!'),
                'role' => 'admin',
            ]
        );
        $viewer->assignRole('viewer');
    }
}
