<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\EducationContent;
use App\Http\Resources\Api\V1\Content\EducationResource;
use Illuminate\Http\Request;

$contents = EducationContent::with('trainingModule')->where('type', 'Module')->get();
foreach ($contents as $c) {
    $resource = new EducationResource($c);
    $data = $resource->toArray(new Request());
    echo "ID: {$c->id} | Title: {$c->title}\n";
    echo "  Final Thumb URL: " . ($data['thumbnail_url'] ?? 'NULL') . "\n";
    echo "------------------------\n";
}
