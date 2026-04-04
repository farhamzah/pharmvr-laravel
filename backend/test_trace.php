<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$user = \App\Models\User::where('email', 'student@pharmvr.com')->first();
\Illuminate\Support\Facades\Auth::login($user);

// get latest attempt
$attempt = \App\Models\AssessmentAttempt::where('user_id', $user->id)
    ->where('status', 'in_progress')
    ->latest()
    ->first();

if (!$attempt) {
    echo "No attempt found for student.\n";
    exit;
}

try {
    $controller = $app->make(\App\Http\Controllers\Api\V1\Assessment\AssessmentController::class);
    $response = $controller->questions($attempt->id);
    echo "SUCCESS\n";
} catch (\Throwable $e) {
    echo "FATAL ERROR:\n";
    echo $e->getMessage() . "\n";
    echo "FILE: " . $e->getFile() . " ON LINE: " . $e->getLine() . "\n";
}
