<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Assessment;
use App\Services\AssessmentService;

$assessment = Assessment::where('type', 'pretest')->where('is_active', true)->first();
if (!$assessment) {
    echo "No active pretest found.\n";
    exit(1);
}

$service = new AssessmentService();
$data = $service->generateAssessment($assessment);

echo "Assessment Title: " . $data['title'] . "\n";
echo "Questions Count: " . $data['questions_count'] . "\n";

foreach ($data['questions'] as $q) {
    echo "- Q: " . $q['question_text'] . "\n";
    foreach ($q['options'] as $o) {
        echo "  - " . $o['option_text'] . (isset($o['is_correct']) ? " (WRONG: is_correct EXPOSED!)" : "") . "\n";
    }
}
