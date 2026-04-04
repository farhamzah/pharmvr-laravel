<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\TrainingModule;
use Illuminate\Support\Facades\Storage;

echo "--- FILE EXISTENCE CHECK ---\n";
foreach (TrainingModule::all() as $m) {
    $rawPath = $m->getRawOriginal('cover_image_path');
    echo "ID: {$m->id} | Title: {$m->title}\n";
    echo "  DB Path: " . ($rawPath ?? 'NULL') . "\n";
    
    if ($rawPath) {
        // Remove storage/ if it's there
        $cleanPath = str_replace('storage/', '', $rawPath);
        $exists = Storage::disk('public')->exists($cleanPath);
        echo "  Disk Public Path: public/{$cleanPath}\n";
        echo "  Exists on Disk: " . ($exists ? "YES" : "NO") . "\n";
        echo "  Full URL: " . $m->cover_image_url . "\n";
        
        // Also check if it's an external URL (YouTube etc)
        if (filter_var($rawPath, FILTER_VALIDATE_URL)) {
            echo "  Type: EXTERNAL URL\n";
        }
    }
    echo "------------------------\n";
}
