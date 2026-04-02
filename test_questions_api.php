<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$user = \App\Models\User::first();
$token = $user->createToken('test')->plainTextToken;
$httpKernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

$assessment = \App\Models\Assessment::first();
echo "Testing Start Attempt for Assessment {$assessment->id}\n";

$req1 = \Illuminate\Http\Request::create('/api/v1/assessments/' . $assessment->id . '/start', 'POST');
$req1->headers->set('Authorization', "Bearer $token");
$req1->headers->set('Accept', 'application/json');
$res1 = $httpKernel->handle($req1);
echo "Start Attempt Status: " . $res1->getStatusCode() . "\n";
echo "Start Content: " . substr($res1->getContent(), 0, 500) . "\n";

$data = json_decode($res1->getContent(), true);
if (isset($data['data']['id'])) {
    $attemptId = $data['data']['id'];
    echo "\nTesting Get Questions for Attempt {$attemptId}\n";
    
    $req2 = \Illuminate\Http\Request::create('/api/v1/assessments/attempt/' . $attemptId . '/questions', 'GET');
    $req2->headers->set('Authorization', "Bearer $token");
    $req2->headers->set('Accept', 'application/json');
    $res2 = $httpKernel->handle($req2);
    
    echo "Get Questions Status: " . $res2->getStatusCode() . "\n";
    echo "Get Questions Content:\n" . substr($res2->getContent(), 0, 1500) . "\n";
} else {
    echo "\nFailed to generate attempt ID.";
}
