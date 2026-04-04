<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$content = \App\Models\EducationContent::where('type', 'video')->get();
echo json_encode($content->toArray());
