<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        Role::firstOrCreate(['name' => 'super-admin'], ['label' => 'Super Administrator']);

        $superAdminData = [
            'name' => 'PharmVR Super Admin',
            'password' => Hash::make('SuperAdmin@2026!'),
            'role' => User::ROLE_SUPER_ADMIN,
            'status' => User::STATUS_ACTIVE,
        ];

        if (Schema::hasColumn('users', 'can_bypass_prerequisites')) {
            $superAdminData['can_bypass_prerequisites'] = true;
        }

        $superAdmin = User::updateOrCreate(
            ['email' => 'superadmin@pharmvr.com'],
            $superAdminData
        );
        $superAdmin->assignRole('super-admin');

        $adminData = [
            'name' => 'Super Admin',
            'password' => Hash::make('PharmVR@dmin2026!'),
            'role' => 'admin',
            'status' => 'active',
        ];

        if (Schema::hasColumn('users', 'can_bypass_prerequisites')) {
            $adminData['can_bypass_prerequisites'] = true;
        }

        $admin = User::updateOrCreate(
            ['email' => 'admin@pharmvr.com'],
            $adminData
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
