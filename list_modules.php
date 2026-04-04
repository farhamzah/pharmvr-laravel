<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\TrainingModule;

echo "--- ALL TRAINING MODULES ---\n";
foreach (TrainingModule::all() as $m) {
    echo "ID: {$m->id} | Slug: {$m->slug} | Title: {$m->title}\n";
}
echo "--- END ---\n";
