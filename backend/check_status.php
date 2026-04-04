<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\TrainingModule;

echo "--- THUMBNAIL STATUS ---\n";
foreach (TrainingModule::all() as $m) {
    echo "ID: {$m->id} | Title: {$m->title}\n";
    echo "  DB Path: " . ($m->getRawOriginal('cover_image_path') ?? 'NULL') . "\n";
    echo "  Final URL: " . ($m->cover_image_url ?? 'NULL') . "\n";
    echo "------------------------\n";
}
