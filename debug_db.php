<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\TrainingModule;
use Illuminate\Support\Facades\Schema;

echo "Columns: " . implode(', ', Schema::getColumnListing('training_modules')) . "\n";

$m = TrainingModule::first();
if ($m) {
    echo "ID: {$m->id}\n";
    echo "Raw Attributes: " . json_encode($m->getAttributes()) . "\n";
} else {
    echo "No modules found.\n";
}
