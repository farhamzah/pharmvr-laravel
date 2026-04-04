<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "--- VERIFYING HOME API (BANNER) ---\n";
$request = Illuminate\Http\Request::create('/api/v1/home', 'GET');
// Mocking auth if needed, but index usually needs it. 
// For verification, I'll just check if the method exists and logic is sound.

$controller = app(\App\Http\Controllers\Api\V1\Content\HomeController::class);
// We need a user to test index properly since it uses $request->user()
$user = \App\Models\User::first();
if ($user) {
    $request->setUserResolver(fn() => $user);
    $response = $controller->index($request);
    $data = json_decode($response->getContent(), true);
    echo "Banner URL: " . ($data['data']['banner_url'] ?? 'NOT FOUND') . "\n";
}

echo "\n--- VERIFYING APP SETTINGS API ---\n";
$appSettingController = app(\App\Http\Controllers\Api\V1\App\AppSettingController::class);
$response = $appSettingController->index();
$data = json_decode($response->getContent(), true);
echo "Settings Data:\n";
print_r($data['data']);
