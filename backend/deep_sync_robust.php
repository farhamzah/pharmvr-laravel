<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\TrainingModule;
use App\Models\EducationContent;

echo "--- ROBUST DEEP SYNC ---\n";

$contents = EducationContent::whereNotNull('thumbnail_url')->get();
foreach ($contents as $c) {
    if (!$c instanceof EducationContent) continue;
    
    $cleanTitle = trim(str_replace('(VR)', '', $c->title));
    $module = TrainingModule::where('title', 'like', "%{$cleanTitle}%")->first();
        
    if ($module) {
        if (empty($module->cover_image_path) || str_contains($module->cover_image_path, 'placeholder')) {
            echo "Syncing Thumbnail for Module [ID: {$module->id}] '{$module->title}' from Content '{$c->title}'\n";
            $module->cover_image_path = $c->thumbnail_url;
            $module->save();
        }
        
        if (!$c->training_module_id) {
            echo "Linking Content [ID: {$c->id}] to Module [ID: {$module->id}]\n";
            $c->training_module_id = $module->id;
            $c->save();
        }
    }
}

echo "\n--- VERIFYING STORAGE LINK ACCESSIBILITY ---\n";
$modules = TrainingModule::all();
foreach ($modules as $m) {
    echo "Module ID: {$m->id} | Image: " . ($m->cover_image_path ?? 'NONE') . "\n";
    echo "  URL: " . ($m->cover_image_url ?? 'NONE') . "\n";
}

echo "Done\n";
