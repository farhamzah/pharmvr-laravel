<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$user = \App\Models\User::first();
$token = $user->createToken('test')->plainTextToken;
$httpKernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

$assessment = \App\Models\Assessment::first();

// Request 1: Start
$req1 = \Illuminate\Http\Request::create('/api/v1/assessments/' . $assessment->id . '/start', 'POST');
$req1->headers->set('Authorization', "Bearer $token");
$req1->headers->set('Accept', 'application/json');
$res1 = $httpKernel->handle($req1);

$data = json_decode($res1->getContent(), true);
$attemptId = $data['data']['id'] ?? null;

if ($attemptId) {
    echo "Started attempt: $attemptId\n";
    // Request 2: Questions using EXACTLY the Flutter expected route
    $req2 = \Illuminate\Http\Request::create('/api/v1/assessment-attempts/' . $attemptId, 'GET');
    $req2->headers->set('Authorization', "Bearer $token");
    $req2->headers->set('Accept', 'application/json');
    $res2 = $httpKernel->handle($req2);
    
    echo "STATUS: " . $res2->getStatusCode() . "\n";
    echo "BODY:\n" . substr($res2->getContent(), 0, 500) . "\n";
} else {
    echo "Failed to start. Response: " . substr($res1->getContent(), 0, 200) . "\n";
}
