<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\TrainingModule;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

echo "Columns: " . implode(', ', Schema::getColumnListing('training_modules')) . "\n";

$modules = DB::table('training_modules')->get();
foreach ($modules as $m) {
    echo "ID: {$m->id} | Title: {$m->title}\n";
    $urlCol = property_exists($m, 'cover_image_url') ? $m->cover_image_url : 'N/A';
    $pathCol = property_exists($m, 'cover_image_path') ? $m->cover_image_path : 'N/A';
    echo "  DB URL: $urlCol\n";
    echo "  DB Path: $pathCol\n";
    
    if ($pathCol === 'N/A' || empty($pathCol)) {
        if ($urlCol !== 'N/A' && !empty($urlCol)) {
            echo "  Updating Path from URL...\n";
            DB::table('training_modules')->where('id', $m->id)->update(['cover_image_path' => $urlCol]);
        }
    }
}
echo "Done\n";
