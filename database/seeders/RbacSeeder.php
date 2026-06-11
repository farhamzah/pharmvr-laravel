<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;
use App\Models\Permission;
use App\Models\User;

class RbacSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Define Permissions
        $permissions = [
            'manage-users' => 'Manage administrators and students',
            'manage-content' => 'Manage training modules and documents',
            'manage-news' => 'Manage platform news and announcements',
            'manage-assessments' => 'Manage question banks and results',
            'view-monitoring' => 'Access real-time VR/AI telemetry',
            'view-audit-logs' => 'View system governance logs',
            'manage-system' => 'Modify global system settings',
        ];

        foreach ($permissions as $slug => $label) {
            Permission::updateOrCreate(['name' => $slug], ['label' => $label]);
        }

        // Canonical product roles used by API contracts and user.role values.
        $student = Role::updateOrCreate(['name' => User::ROLE_STUDENT], ['label' => 'Student']);
        $instructor = Role::updateOrCreate(['name' => User::ROLE_INSTRUCTOR], ['label' => 'Instructor']);
        $adminRole = Role::updateOrCreate(['name' => User::ROLE_ADMIN], ['label' => 'Administrator']);
        $superAdminCanonical = Role::updateOrCreate(['name' => User::ROLE_SUPER_ADMIN], ['label' => 'Super Administrator']);

        // Legacy/admin-panel roles are kept for backward compatibility.
        $superAdmin = Role::updateOrCreate(['name' => 'super-admin'], ['label' => 'Super Administrator']);
        $moderator = Role::updateOrCreate(['name' => 'moderator'], ['label' => 'Content Moderator']);
        $viewer = Role::updateOrCreate(['name' => 'viewer'], ['label' => 'System Auditor']);

        // Assign Permissions to Roles
        $instructor->permissions()->syncWithoutDetaching(
            Permission::whereIn('name', ['view-monitoring'])->get()
        );
        $adminRole->permissions()->syncWithoutDetaching(
            Permission::whereIn('name', ['manage-users', 'manage-content', 'manage-news', 'manage-assessments', 'view-monitoring', 'view-audit-logs'])->get()
        );
        $superAdminCanonical->permissions()->syncWithoutDetaching(Permission::all());
        $superAdmin->permissions()->sync(Permission::all());
        $moderator->permissions()->sync(
            Permission::whereIn('name', ['manage-content', 'manage-news', 'manage-assessments', 'view-monitoring'])->get()
        );
        $viewer->permissions()->sync(
            Permission::whereIn('name', ['view-monitoring', 'view-audit-logs'])->get()
        );

        // Assign Super Admin role to the first admin user found
        $admin = User::where('role', 'admin')->first();
        if ($admin) {
            $admin->assignRole(User::ROLE_ADMIN);
            $admin->assignRole(User::ROLE_SUPER_ADMIN);
            $admin->assignRole('super-admin');
        }
    }
}
