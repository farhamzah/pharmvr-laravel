<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

$columns = Schema::getColumnListing('training_modules');
echo "Columns: " . implode(', ', $columns) . "\n";

$data = DB::table('training_modules')->get();
foreach ($data as $m) {
    echo "ID: {$m->id} | Title: {$m->title}\n";
    echo "  cover_image_url: " . ($m->cover_image_url ?? 'NULL') . "\n";
    echo "  cover_image_path: " . ($m->cover_image_path ?? 'NULL') . "\n";
}
