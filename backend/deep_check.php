<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\TrainingModule;
use Illuminate\Support\Facades\Storage;

echo "--- STORAGE LINK CHECK ---\n";
$publicStoragePath = base_path('public/storage');
if (file_exists($publicStoragePath)) {
    echo "public/storage exists.\n";
    if (is_link($publicStoragePath)) {
        echo "It is a SYMLINK to: " . readlink($publicStoragePath) . "\n";
    } else if (is_dir($publicStoragePath)) {
        echo "It is a DIRECTORY (Warning: on Windows this might happen if artisan storage:link fails).\n";
    }
} else {
    echo "public/storage does NOT exist!\n";
}

echo "\n--- DB CHECK ---\n";
foreach (TrainingModule::all() as $m) {
    echo "ID: {$m->id} | Title: {$m->title}\n";
    echo "  Raw Path: " . ($m->getRawOriginal('cover_image_path') ?? 'NULL') . "\n";
    echo "  Clean URL: " . ($m->cover_image_url ?? 'NULL') . "\n";
    
    // Test resolution
    $testPath = str_replace('storage/', '', $m->getRawOriginal('cover_image_path') ?? '');
    if ($testPath && !filter_var($testPath, FILTER_VALIDATE_URL)) {
        echo "  Check File Exist (app/public/{$testPath}): " . (file_exists(storage_path('app/public/' . $testPath)) ? "YES" : "NO") . "\n";
    }
    echo "------------------------\n";
}
