<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$columns = DB::select('DESCRIBE education_contents');
foreach ($columns as $col) {
    echo "{$col->Field}: {$col->Type}\n";
}

$types = DB::table('education_contents')->select('type')->distinct()->pluck('type');
echo "Distinct Types: " . $types->implode(', ') . "\n";

$levels = DB::table('education_contents')->select('level')->distinct()->pluck('level');
echo "Distinct Levels: " . $levels->implode(', ') . "\n";
