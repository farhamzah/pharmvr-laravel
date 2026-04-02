<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$user = \App\Models\User::first();
$token = $user->createToken('test')->plainTextToken;
$httpKernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

$assessment = \App\Models\Assessment::first();

// Request 1
$req1 = \Illuminate\Http\Request::create('/api/v1/assessments/' . $assessment->id . '/start', 'POST');
$req1->headers->set('Authorization', "Bearer $token");
$req1->headers->set('Accept', 'application/json');
$res1 = $httpKernel->handle($req1);
echo "Req 1 Status: " . $res1->getStatusCode() . "\n";

// Request 2 (Duplicate)
$req2 = \Illuminate\Http\Request::create('/api/v1/assessments/' . $assessment->id . '/start', 'POST');
$req2->headers->set('Authorization', "Bearer $token");
$req2->headers->set('Accept', 'application/json');
$res2 = $httpKernel->handle($req2);

echo "Req 2 Status: " . $res2->getStatusCode() . "\n";
$decoded = json_decode($res2->getContent(), true);
if ($decoded && isset($decoded['message'])) {
    echo "Req 2 MESSAGE: " . $decoded['message'] . "\n";
} else {
    echo "Req 2 HTML/BODY:\n" . substr($res2->getContent(), 0, 500) . "\n";
}
