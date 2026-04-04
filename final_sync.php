<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\TrainingModule;
use App\Models\EducationContent;
use Illuminate\Support\Facades\Log;

echo "--- FINAL SYNC ---\n";
$modules = TrainingModule::all();
foreach ($modules as $m) {
    echo "Module [ID: {$m->id}] '{$m->title}'\n";
    $content = EducationContent::where('training_module_id', $m->id)->first();
    if ($content) {
        echo "  Found Content [ID: {$content->id}] '{$content->title}'\n";
        // Sync thumbnail if module has it but content doesn't
        if ($m->cover_image_path && empty($content->thumbnail_url)) {
            echo "    Updating Content thumb -> {$m->cover_image_path}\n";
            $content->thumbnail_url = $m->cover_image_path;
            $content->save();
        }
        // Sync thumbnail if content has it but module doesn't
        if ($content->thumbnail_url && empty($m->cover_image_path)) {
            echo "    Updating Module thumb -> {$content->thumbnail_url}\n";
            $m->cover_image_path = $content->thumbnail_url;
            $m->save();
        }
    } else {
        echo "  No Content found. (This is weird if sync_data.php ran)\n";
    }
}
echo "Done\n";
