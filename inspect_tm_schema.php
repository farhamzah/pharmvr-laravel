<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== training_modules schema ===\n";
$columns = DB::select('DESCRIBE training_modules');
foreach ($columns as $col) {
    echo "{$col->Field}: {$col->Type} | Null: {$col->Null} | Default: {$col->Default}\n";
}
