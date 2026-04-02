<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\TrainingModule;
use App\Models\EducationContent;

echo "=== TRAINING MODULES (ADMIN) ===\n";
foreach (TrainingModule::all() as $m) {
    echo "[ID: {$m->id}] {$m->title} | Active: " . ($m->is_active ? 'Y' : 'N') . "\n";
}

echo "\n=== EDUCATION CONTENTS (TYPE=MODULE/Module) ===\n";
$contents = EducationContent::whereIn('type', ['module', 'Module'])->get();
foreach ($contents as $c) {
    $linkedModule = $c->training_module_id ? TrainingModule::find($c->training_module_id) : null;
    $status = $linkedModule ? "Linked to [ID: {$linkedModule->id}]" : "ORPHAN";
    echo "[ID: {$c->id}] {$c->title} ({$c->type}) ({$status}) | Active: " . ($c->is_active ? 'Y' : 'N') . "\n";
}

echo "\n=== THUMBNAILS CHECK ===\n";
foreach ($contents as $c) {
    echo "ID: {$c->id} | Title: {$c->title} | Thumb: {$c->thumbnail_url}\n";
}
