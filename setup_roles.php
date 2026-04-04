<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Artisan;

// 1. Update sriwidod@unpad.com
$student = User::where('email', 'sriwidod@unpad.com')->orWhere('email', 'sriwidodo@unpad.ac.id')->first();
if ($student) {
    $student->email = 'sriwidodo@unpad.ac.id';
    $student->save();
    echo "Updated student email to sriwidodo@unpad.ac.id\n";
} else {
    echo "Student not found\n";
}

// 2. Re-run AdminUserSeeder to bring back moderator and viewer
echo Artisan::call('db:seed', ['--class' => 'AdminUserSeeder']);
echo "AdminUserSeeder completed\n";

// 3. Ensure we have an instructor role
$instructor = User::firstOrCreate(
    ['email' => 'instructor@pharmvr.com'],
    [
        'name' => 'Lead Instructor',
        'password' => Hash::make('PharmVR@Inst2026!'),
        'role' => 'instructor',
        'status' => 'active',
    ]
);
echo "Instructor user ensured.\n";

// Output current roles:
$users = User::all();
echo "\n--- CURRENT USERS ---\n";
foreach($users as $u) {
    $rbacRoles = method_exists($u, 'roles') ? $u->roles->pluck('name')->join(', ') : 'N/A';
    echo str_pad($u->email, 25) . " | UI Role: " . str_pad($u->role, 10) . " | RBAC Roles: $rbacRoles\n";
}
