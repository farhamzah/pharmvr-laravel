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
                'password' => Hash::make('PharmVR@dmin2026!'),
                'role' => 'admin',
                'status' => 'active',
            ]
        );
        $admin->assignRole('super-admin');

        $moderator = User::updateOrCreate(
            ['email' => 'moderator@pharmvr.com'],
            [
                'name' => 'Content Moderator',
                'password' => Hash::make('PharmVR@Mod2026!'),
                'role' => 'admin',
                'status' => 'active',
            ]
        );
        $moderator->assignRole('moderator');

        $viewer = User::updateOrCreate(
            ['email' => 'viewer@pharmvr.com'],
            [
                'name' => 'System Auditor',
                'password' => Hash::make('PharmVR@View2026!'),
                'role' => 'admin',
                'status' => 'active',
            ]
        );
        $viewer->assignRole('viewer');
    }
}
