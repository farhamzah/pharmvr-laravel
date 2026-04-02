<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\TrainingModule;
use App\Models\EducationContent;

echo "--- DEEP SYNC ---\n";

foreach (EducationContent::whereNotNull('thumbnail_url')->get() as $c) {
    // Try to find module by stripping (VR) and trimming
    $cleanTitle = trim(str_replace('(VR)', '', $c->title));
    
    $module = TrainingModule::where('title', 'like', "%{$cleanTitle}%")
        ->where('cover_image_path', null)
        ->first();
        
    if ($module) {
        echo "Linking Thumbnail for Module [ID: {$module->id}] '{$module->title}' from Content '{$c->title}'\n";
        $module->cover_image_path = $c->thumbnail_url;
        $module->save();
        
        if ($c instanceof EducationContent && !$c->training_module_id) {
            $c->training_module_id = $module->id;
            $c->save();
        }
    }
}
echo "Done\n";
