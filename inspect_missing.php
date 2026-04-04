<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\TrainingModule;
use App\Models\EducationContent;

$modules = TrainingModule::whereIn('slug', ['pengenalan-lab-steril', 'prosedur-gowning-level-3'])->get();
foreach ($modules as $m) {
    echo "Module [ID: {$m->id}] '{$m->title}'\n";
    echo "  DB Path: " . ($m->getRawOriginal('cover_image_path') ?? 'NULL') . "\n";
    
    $content = EducationContent::where('training_module_id', $m->id)->first();
    if ($content) {
        echo "  Content [ID: {$content->id}] '{$content->title}'\n";
        echo "    Thumb: " . ($content->thumbnail_url ?? 'NULL') . "\n";
    } else {
        echo "  No linked content found.\n";
    }
    echo "------------------------\n";
}
