<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\TrainingModule;

echo "--- FINAL STATUS ---\n";
$modules = TrainingModule::all();
foreach ($modules as $m) {
    echo "ID: {$m->id} | Title: {$m->title} | Slug: {$m->slug}\n";
    echo "  Raw Path: " . ($m->getRawOriginal('cover_image_path') ?? 'NULL') . "\n";
    echo "  Accessor URL: " . ($m->cover_image_url ?? 'NULL') . "\n";
    echo "------------------------\n";
}
echo "Done\n";
