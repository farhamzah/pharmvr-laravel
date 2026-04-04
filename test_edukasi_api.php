<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$user = \App\Models\User::first();
$token = $user->createToken('test')->plainTextToken;

$httpKernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

$uri = '/api/v1/edukasi';
echo "--- Testing $uri ---\n";
$request = \Illuminate\Http\Request::create($uri, 'GET');
$request->headers->set('Authorization', "Bearer $token");
$request->headers->set('Accept', 'application/json');

$response = $httpKernel->handle($request);

echo "Status: " . $response->getStatusCode() . "\n";
echo "Content:\n" . substr($response->getContent(), 0, 2000) . "\n\n";

$httpKernel->terminate($request, $response);
