<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\TrainingModule;
use App\Models\EducationContent;

echo "--- DATA CLEANUP: DEACTIVATING ORPHANS ---\n";

// Get valid module IDs from Admin (TrainingModule)
$validModuleIds = TrainingModule::pluck('id')->toArray();
echo "Valid Module IDs in Admin: " . implode(', ', $validModuleIds) . "\n";

// Deactivate all module-type contents that are either:
// 1. Not linked to any training_module_id
// 2. Linked to a training_module_id that doesn't exist anymore
$orphans = EducationContent::whereIn('type', ['module', 'Module'])
    ->where(function($q) use ($validModuleIds) {
        $q->whereNull('training_module_id')
          ->orWhereNotIn('training_module_id', $validModuleIds);
    })->get();

echo "Found " . $orphans->count() . " orphaned content records.\n";

foreach ($orphans as $c) {
    echo "Deactivating [ID: {$c->id}] '{$c->title}'\n";
    $c->is_active = false;
    $c->save();
}

// Special check for "Validasi Area Bersih" - usually it has specific title
echo "\n--- CLEANING SPECIFIC REDUNDANT DATA ---\n";
$redundant = EducationContent::where('title', 'like', '%Validasi Area Bersih%')
    ->where('is_active', true)
    ->whereNotIn('training_module_id', $validModuleIds)
    ->get();

foreach ($redundant as $r) {
    echo "Deactivating redundant: [ID: {$r->id}] '{$r->title}'\n";
    $r->is_active = false;
    $r->save();
}

echo "Cleanup Complete.\n";
