<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$user = \App\Models\User::where('email', 'test@pharmvr.com')->first();
if(!$user) { $user = \App\Models\User::first(); }
\Illuminate\Support\Facades\Auth::login($user);

$controller = app(\App\Http\Controllers\Api\V1\Assessment\AssessmentController::class);

// 1. Test Intro
$module = \App\Models\TrainingModule::where('title', 'LIKE', '%steril%')->first();
try {
    echo "--- Testing Intro Endpoint ---\n";
    $response = $controller->intro($module->slug, 'pretest');
    echo json_encode($response->toArray(request()), JSON_PRETTY_PRINT) . "\n";
} catch (\Exception $e) {
    echo "Intro Exception: " . $e->getMessage() . "\n" . $e->getTraceAsString() . "\n";
}

// 2. Test Start
$assessment = \App\Models\Assessment::where('module_id', $module->id)->where('type', 'pretest')->first();
try {
    echo "\n--- Testing Start Endpoint ---\n";
    $response = $controller->start($assessment->id);
    if (method_exists($response, 'getData')) {
        echo json_encode($response->getData(true), JSON_PRETTY_PRINT) . "\n";
    } else {
        echo "Response was not JSON\n";
    }
} catch (\Exception $e) {
    echo "Start Exception: " . $e->getMessage() . "\n" . $e->getTraceAsString() . "\n";
}
