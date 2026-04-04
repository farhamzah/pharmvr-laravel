<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

$user = \App\Models\User::first();
if (!$user) {
    echo "No user found";
    exit;
}

$request = Illuminate\Http\Request::create('/api/v1/home', 'GET');
$request->setUserResolver(function () use ($user) {
    return $user;
});

$response = $kernel->handle($request);
echo $response->getContent();
