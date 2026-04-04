<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$user = \App\Models\User::where('email', 'student@pharmvr.com')->first();
$attempts = \App\Models\AssessmentAttempt::where('user_id', $user->id)->get();

echo "User ID: {$user->id}\n";
echo "Total Attempts: " . $attempts->count() . "\n";
foreach($attempts as $a) {
    echo "Attempt ID: {$a->id}, Assessment: {$a->assessment_id}, Status: {$a->status}\n";
}
