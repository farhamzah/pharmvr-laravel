<?php

namespace App\Http\Controllers\Api\V1\Assessment;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Assessment\StartAttemptRequest;
use App\Http\Requests\Api\V1\Assessment\SubmitAssessmentRequest;
use App\Http\Resources\Api\V1\Assessment\AssessmentResource;
use App\Http\Resources\Api\V1\Assessment\AttemptResource;
use App\Http\Resources\Api\V1\Assessment\QuestionResource;
use App\Models\Assessment;
use App\Models\AssessmentAttempt;
use App\Services\AssessmentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AssessmentController extends Controller
{
    protected $service;

    public function __construct(AssessmentService $service)
    {
        $this->service = $service;
    }

    /**
     * Get assessment intro info.
     */
    public function intro($moduleSlug, $type)
    {
        $type = str_replace('_', '', $type); // Normalize pre_test to pretest
        $module = \App\Models\TrainingModule::where('slug', $moduleSlug)->firstOrFail();

        $assessment = Assessment::where('type', $type)
            ->where('module_id', $module->id)
            ->where('status', \App\Enums\AssessmentStatus::ACTIVE->value)
            ->with(['trainingModule'])
            ->withCount('questions')
            ->firstOrFail();

        return new AssessmentResource($assessment);
    }

    /**
     * Start a new attempt.
     */
    public function start($assessmentId)
    {
        $assessment = Assessment::withCount('questions')->findOrFail($assessmentId);

        // [NEW] Sequential validation
        $progress = \App\Models\UserTrainingProgress::where('user_id', Auth::id())
            ->where('training_module_id', $assessment->module_id)
            ->first();

        if ($assessment->type === 'post_test') {
            if (!$progress || $progress->vr_status !== 'completed') {
                return response()->json([
                    'success' => false,
                    'message' => 'Anda harus menyelesaikan Simulasi VR terlebih dahulu sebelum dapat mengambil Post-Test.',
                ], 403);
            }
        }

        try {
            $attempt = $this->service->startAttempt(Auth::id(), $assessment);
            
            return response()->json([
                'success' => true,
                'message' => 'Assessment attempt started.',
                'data'    => [
                    'id'            => $attempt->id,
                    'assessment_id' => $assessment->id,
                    'title'         => $assessment->title,
                    'type'          => $assessment->type,
                    'total_questions' => $assessment->questions_count,
                    'started_at'    => $attempt->started_at->toIso8601String(),
                    'status'        => $attempt->status,
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], $e->getCode() ?: 400);
        }
    }

    /**
     * Get questions for an attempt.
     */
    public function questions($attemptId)
    {
        $attempt = AssessmentAttempt::with(['assessment.questions.options', 'answers'])
            ->where('id', $attemptId)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        return response()->json([
            'success' => true,
            'data'    => [
                'attempt_summary' => [
                    'id'              => $attempt->id,
                    'status'          => $attempt->status,
                    'total_questions' => $attempt->assessment->questions->count(),
                    'answered_count'  => $attempt->answers->count(),
                ],
                'questions' => QuestionResource::collection($attempt->assessment->questions)
            ]
        ]);
    }

    /**
     * Submit assessment.
     */
    public function submit(SubmitAssessmentRequest $request, $attemptId)
    {
        $attempt = AssessmentAttempt::where('id', $attemptId)
            ->where('user_id', Auth::id())
            ->where('status', 'in_progress')
            ->firstOrFail();

        $result = $this->service->submitAttempt($attempt, $request->input('answers', []));

        return new AttemptResource($result);
    }

    /**
     * Get results for an attempt.
     */
    public function results($attemptId)
    {
        $attempt = AssessmentAttempt::where('id', $attemptId)
            ->where('user_id', Auth::id())
            ->with('assessment')
            ->firstOrFail();

        return new AttemptResource($attempt);
    }
}
