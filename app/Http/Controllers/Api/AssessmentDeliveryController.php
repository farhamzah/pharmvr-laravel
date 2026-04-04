<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\TrainingModule;
use App\Models\Assessment;
use App\Services\AssessmentService;
use Illuminate\Http\Request;
use App\Enums\AssessmentType;

class AssessmentDeliveryController extends Controller
{
    protected $selectorService;

    public function __construct(AssessmentService $selectorService)
    {
        $this->selectorService = $selectorService;
    }

    /**
     * Start an assessment session.
     * 
     * @param TrainingModule $module
     * @param string $type
     * @return \Illuminate\Http\JsonResponse
     */
    public function start(TrainingModule $module, string $type)
    {
        $type = str_replace('_', '', $type); // Normalize pre_test to pretest
        // Validate type manually or via route pattern
        try {
            $enumType = AssessmentType::from($type);
        } catch (\ValueError $e) {
            return response()->json(['message' => 'Invalid assessment type.'], 400);
        }

        $assessment = $module->assessments()
            ->where('type', $enumType->value)
            ->first();

        if (!$assessment) {
            return response()->json(['message' => 'Assessment not found for this module.'], 404);
        }

        try {
            $data = $this->selectorService->generateAssessment($assessment);
            return response()->json([
                'status' => 'success',
                'data' => $data
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 400);
        }
    }
}
