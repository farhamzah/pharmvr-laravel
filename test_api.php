<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$user = \App\Models\User::first();
\Illuminate\Support\Facades\Auth::login($user);

$attempt = \App\Models\AssessmentAttempt::latest()->first();

$controller = app(\App\Http\Controllers\Api\V1\Assessment\AssessmentController::class);

try {
    $response = $controller->questions($attempt->id);
    file_put_contents('output_test_api.json', json_encode($response->getData(true), JSON_PRETTY_PRINT));
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n" . $e->getTraceAsString();
}
