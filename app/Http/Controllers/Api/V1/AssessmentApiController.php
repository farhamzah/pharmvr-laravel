<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\TrainingModule;
use App\Models\Assessment;
use App\Services\AssessmentService;
use Illuminate\Http\Request;

class AssessmentApiController extends Controller
{
    protected $assessmentService;

    public function __construct(AssessmentService $assessmentService)
    {
        $this->assessmentService = $assessmentService;
    }

    /**
     * Get the assessment for a module.
     */
    public function start(TrainingModule $module, string $type)
    {
        if (!in_array($type, ['pretest', 'posttest'])) {
            return response()->json(['message' => 'Invalid assessment type.'], 400);
        }

        $assessment = $module->assessments()->where('type', $type)->first();

        if (!$assessment || $assessment->status !== \App\Enums\AssessmentStatus::ACTIVE) {
            return response()->json(['message' => 'Assessment protocol is offline or not initialized.'], 404);
        }

        $data = $this->assessmentService->generateAssessment($assessment);

        return response()->json([
            'status' => 'success',
            'data' => $data
        ]);
    }
}
