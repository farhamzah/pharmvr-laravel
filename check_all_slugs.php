<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "--- TRAINING MODULES ---\n";
foreach(\App\Models\TrainingModule::all() as $m) {
    echo "TM ID: {$m->id} | Slug: {$m->slug} | Code: " . ($m->module_identifier ?? 'N/A') . "\n";
}

echo "--- EDUCATION CONTENT ---\n";
foreach(\App\Models\EducationContent::all() as $c) {
    echo "EC ID: {$c->id} | TM_ID: {$c->training_module_id} | Slug: {$c->slug} | Code: {$c->code}\n";
}
