<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$module = \App\Models\TrainingModule::where('title', 'LIKE', '%steril%')->first();
if (!$module) { echo "Module not found\n"; exit; }
echo "Module ID: " . $module->id . "\n";

$qbCount = \App\Models\QuestionBankItem::where('module_id', $module->id)->count();
echo "QuestionBankItems count: " . $qbCount . "\n";

$assessments = \App\Models\Assessment::where('module_id', $module->id)->get();
foreach ($assessments as $ast) {
    echo "Assessment ID: {$ast->id}, Type: {$ast->type->value}\n";
    $qCount = $ast->questions()->count();
    echo " -> Questions count via questions() relation: " . $qCount . "\n";
}
