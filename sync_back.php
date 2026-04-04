<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\TrainingModule;
use App\Models\EducationContent;

echo "--- SYNCING THUMBNAILS BACK TO MODULES ---\n";
$contents = EducationContent::whereNotNull('thumbnail_url')->get();
foreach ($contents as $c) {
    if ($c->training_module_id) {
        $module = TrainingModule::find($c->training_module_id);
        if ($module && empty($module->cover_image_path)) {
            echo "Updating Module [ID: {$module->id}] '{$module->title}' from Content '{$c->title}'\n";
            echo "  New Path: {$c->thumbnail_url}\n";
            $module->cover_image_path = $c->thumbnail_url;
            $module->save();
        }
    }
}
echo "Done\n";
