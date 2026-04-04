<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\TrainingModule;

$m = TrainingModule::find(3);
if ($m) {
    echo "ID: " . $m->id . "\n";
    echo "Title: " . $m->title . "\n";
    echo "Slug: " . $m->slug . "\n";
    echo "Is Active: " . ($m->is_active ? 'YES' : 'NO') . "\n";
    echo "Cover Path: " . $m->cover_image_path . "\n";
} else {
    echo "Module ID 3 not found.\n";
}
