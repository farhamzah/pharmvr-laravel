<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\TrainingModule;
use App\Models\EducationContent;

echo "=== TRAINING MODULES ===\n";
foreach (TrainingModule::all() as $m) {
    echo "ID: {$m->id} | Title: {$m->title} | Slug: {$m->slug}\n";
    echo "  Path: " . $m->getRawOriginal('cover_image_path') . "\n";
    echo "  URL (accessor): " . $m->cover_image_url . "\n";
    
    $contents = EducationContent::where('training_module_id', $m->id)->get();
    echo "  Linked Contents (" . $contents->count() . "):\n";
    foreach ($contents as $c) {
        echo "    - [ID: {$c->id}] {$c->title} ({$c->type})\n";
        echo "      Thumb: {$c->thumbnail_url}\n";
    }
    echo "------------------------\n";
}

echo "\n=== OTHER MODULE-TYPE CONTENTS ===\n";
$others = EducationContent::where('type', 'module')->whereNull('training_module_id')->get();
foreach ($others as $c) {
    echo "[ID: {$c->id}] {$c->title} (ORPHAN)\n";
}
