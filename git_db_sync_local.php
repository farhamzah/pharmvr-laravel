<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$users = \App\Models\User::with('profile')->get()->map(function ($user) {
    return [
        'id' => $user->id,
        'email' => $user->email,
        'name' => $user->name,
        'role' => $user->role,
        'university' => $user->profile->university ?? 'University of Padjadjaran',
    ];
});

echo json_encode($users, JSON_PRETTY_PRINT);
