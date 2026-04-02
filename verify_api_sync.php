<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\EducationContent;
use App\Http\Resources\Api\V1\Content\EducationResource;
use Illuminate\Http\Request;

echo "--- VERIFYING API FILTERING (TYPE=MODULE) ---\n";

// Mock a request with a specific host
$request = Request::create('/api/v1/education?type=module', 'GET');
$request->headers->set('host', '192.168.1.50:8000'); // Simulate real IP

$controller = app(\App\Http\Controllers\Api\V1\Content\EducationController::class);
$response = $controller->index($request);

$data = $response->toArray($request);

echo "Total Modules Returned: " . count($data) . "\n";
foreach ($data as $item) {
    echo "ID: {$item['id']} | Title: {$item['title']}\n";
    echo "  Thumb URL: {$item['thumbnail_url']}\n";
}

// Double check orphans directly
$orphanCount = EducationContent::whereIn('type', ['module', 'Module'])
    ->where('is_active', true)
    ->whereNull('training_module_id')
    ->count();

echo "\nActive Orphans remaining: " . $orphanCount . "\n";
