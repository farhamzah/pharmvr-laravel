<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

$results = DB::table('training_modules')->get();
foreach ($results as $m) {
    echo "ID: {$m->id} | Title: {$m->title}\n";
    echo "  Path: " . ($m->cover_image_path ?? 'NULL') . "\n";
    if (property_exists($m, 'cover_image_url')) {
        echo "  Old URL Col: " . ($m->cover_image_url ?? 'NULL') . "\n";
    }
    echo "------------------------\n";
}
