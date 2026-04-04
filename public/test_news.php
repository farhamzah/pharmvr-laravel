<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$request = Illuminate\Http\Request::create('/api/v1/news?type=internal', 'GET');
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$controller = new \App\Http\Controllers\Api\V1\Content\NewsController();
echo json_encode($controller->index($request)->resource->toArray());
