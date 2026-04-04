<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\TrainingModule;

$m = TrainingModule::where('slug', 'gudang')->first();
if ($m) {
    echo "Gudang ID: " . $m->id . "\n";
    echo "Raw Path: " . $m->getRawOriginal('cover_image_path') . "\n";
    echo "Accessor URL: " . $m->cover_image_url . "\n";
} else {
    echo "Gudang not found.\n";
}
