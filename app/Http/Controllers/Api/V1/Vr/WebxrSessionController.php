<?php

namespace App\Http\Controllers\Api\V1\Vr;

use App\Http\Controllers\Controller;
use App\Models\Scene;
use App\Models\VrSession;
use App\Models\VrStepCompletion;
use App\Traits\ApiResponse;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * WebxrSessionController - Handles WebXR-specific session lifecycle.
 * WebXR sessions use Sanctum bearer token (user auth), no device token needed.
 */
class WebxrSessionController extends Controller
{
    use ApiResponse;

    /**
     * Start a WebXR session for a scene.
     * POST /api/v1/vr/webxr/sessions/start
     */
    public function start(Request $request)
    {
        $request->validate([
            'scene_slug' => 'required|string|exists:scenes,slug',
            'device_info' => 'nullable|array',
        ]);

        $user = $request->user();
        $scene = Scene::where('slug', $request->scene_slug)->active()->firstOrFail();

        // Check scene unlock
        if (!$scene->isUnlockedFor($user)) {
            return $this->errorResponse('Scene ini masih terkunci. Selesaikan scene sebelumnya terlebih dahulu.', 403);
        }

        // Interrupt any previous active sessions
        VrSession::where('user_id', $user->id)
            ->whereIn('session_status', ['starting', 'playing'])
            ->update([
                'session_status' => 'interrupted',
                'interrupted_at' => Carbon::now(),
            ]);

        // Create new session
        $session = VrSession::create([
            'user_id' => $user->id,
            'device_id' => null, // WebXR has no device record
            'training_module_id' => $scene->training_module_id,
            'scene_id' => $scene->id,
            'pairing_id' => null,
            'session_status' => 'playing',
            'platform' => 'webxr',
            'progress_percentage' => 0,
            'started_at' => Carbon::now(),
            'last_activity_at' => Carbon::now(),
            'summary_json' => [
                'device_info' => $request->device_info,
                'scene_slug' => $scene->slug,
            ],
        ]);

        // Load steps for the scene
        $steps = $scene->steps->map(fn($s) => [
            'id' => $s->id,
            'slug' => $s->slug,
            'title' => $s->title,
            'order' => $s->order_index,
            'is_required' => $s->is_required,
            'max_score' => $s->max_score,
        ]);

        return $this->successResponse([
            'session_id' => $session->id,
            'session_status' => $session->session_status,
            'scene' => [
                'slug' => $scene->slug,
                'title' => $scene->title,
            ],
            'steps' => $steps,
            'started_at' => $session->started_at->toDateTimeString(),
        ], 'WebXR session started.', 201);
    }

    /**
     * Record a telemetry event.
     * POST /api/v1/vr/webxr/sessions/{session}/events
     */
    public function storeEvent(Request $request, VrSession $session)
    {
        $request->validate([
            'event_type' => 'required|string|max:100',
            'event_category' => 'nullable|string|in:telemetry,step_complete,mistake,score,system',
            'timestamp' => 'required|date',
            'payload' => 'nullable|array',
        ]);

        // Verify ownership
        if ($session->user_id !== $request->user()->id) {
            return $this->errorResponse('Unauthorized.', 403);
        }

        if (!in_array($session->session_status, ['starting', 'playing'])) {
            return $this->errorResponse('Session is not active.', 422);
        }

        $event = $session->events()->create([
            'user_id' => $session->user_id,
            'training_module_id' => $session->training_module_id,
            'device_id' => null,
            'event_type' => $request->event_type,
            'event_category' => $request->event_category ?? 'telemetry',
            'event_timestamp' => $request->timestamp,
            'event_payload' => $request->payload,
        ]);

        // Update last activity
        $session->update(['last_activity_at' => Carbon::now()]);

        return $this->successResponse([
            'event_id' => $event->id,
            'event_type' => $event->event_type,
        ], 'Event recorded.', 201);
    }

    /**
     * Record a step completion.
     * POST /api/v1/vr/webxr/sessions/{session}/step-complete
     */
    public function stepComplete(Request $request, VrSession $session)
    {
        $request->validate([
            'step_slug' => 'required|string|max:100',
            'score' => 'required|numeric|min:0|max:100',
            'time_seconds' => 'required|integer|min:0',
            'mistakes_count' => 'nullable|integer|min:0',
            'metadata' => 'nullable|array',
        ]);

        if ($session->user_id !== $request->user()->id) {
            return $this->errorResponse('Unauthorized.', 403);
        }

        if (!in_array($session->session_status, ['starting', 'playing'])) {
            return $this->errorResponse('Session is not active.', 422);
        }

        // Find the step
        $scene = $session->scene;
        if (!$scene) {
            return $this->errorResponse('Session has no associated scene.', 422);
        }

        $step = $scene->steps()->where('slug', $request->step_slug)->first();
        if (!$step) {
            return $this->errorResponse("Step '{$request->step_slug}' not found in scene.", 404);
        }

        // Record completion
        $completion = VrStepCompletion::create([
            'vr_session_id' => $session->id,
            'scene_step_id' => $step->id,
            'score' => $request->score,
            'time_seconds' => $request->time_seconds,
            'mistakes_count' => $request->mistakes_count ?? 0,
            'completed_at' => Carbon::now(),
            'metadata' => $request->metadata,
        ]);

        // Also log as event
        $session->events()->create([
            'user_id' => $session->user_id,
            'training_module_id' => $session->training_module_id,
            'device_id' => null,
            'event_type' => 'step_completed',
            'event_category' => 'step_complete',
            'event_timestamp' => Carbon::now(),
            'event_payload' => [
                'step_slug' => $request->step_slug,
                'score' => $request->score,
                'time_seconds' => $request->time_seconds,
            ],
        ]);

        // Calculate running progress
        $totalSteps = $scene->steps()->where('is_required', true)->count();
        $completedSteps = VrStepCompletion::where('vr_session_id', $session->id)
            ->whereHas('step', fn($q) => $q->where('is_required', true))
            ->count();
        $progress = $totalSteps > 0 ? round(($completedSteps / $totalSteps) * 100) : 0;

        // Running total score
        $runningScore = VrStepCompletion::where('vr_session_id', $session->id)->sum('score');

        $session->update([
            'progress_percentage' => $progress,
            'current_step' => $request->step_slug,
            'last_activity_at' => Carbon::now(),
        ]);

        return $this->successResponse([
            'completion_id' => $completion->id,
            'step_slug' => $request->step_slug,
            'score' => $request->score,
            'running_total' => round($runningScore, 2),
            'progress_percentage' => $progress,
            'steps_remaining' => max(0, $totalSteps - $completedSteps),
        ], 'Step completed.', 201);
    }

    /**
     * Record a mistake event.
     * POST /api/v1/vr/webxr/sessions/{session}/mistakes
     */
    public function storeMistake(Request $request, VrSession $session)
    {
        $request->validate([
            'mistake_type' => 'required|string|max:100',
            'step_slug' => 'nullable|string|max:100',
            'timestamp' => 'required|date',
            'description' => 'nullable|string|max:500',
            'severity' => 'nullable|string|in:info,warning,critical',
            'penalty_points' => 'nullable|integer|min:0',
        ]);

        if ($session->user_id !== $request->user()->id) {
            return $this->errorResponse('Unauthorized.', 403);
        }

        $event = $session->events()->create([
            'user_id' => $session->user_id,
            'training_module_id' => $session->training_module_id,
            'device_id' => null,
            'event_type' => $request->mistake_type,
            'event_category' => 'mistake',
            'event_timestamp' => $request->timestamp,
            'event_payload' => [
                'step_slug' => $request->step_slug,
                'description' => $request->description,
                'severity' => $request->severity ?? 'warning',
                'penalty_points' => $request->penalty_points ?? 0,
            ],
        ]);

        // Count total mistakes in this session
        $totalMistakes = $session->events()
            ->where('event_category', 'mistake')
            ->count();

        $totalPenalty = $session->events()
            ->where('event_category', 'mistake')
            ->get()
            ->sum(fn($e) => $e->event_payload['penalty_points'] ?? 0);

        $session->update([
            'total_mistakes' => $totalMistakes,
            'last_activity_at' => Carbon::now(),
        ]);

        return $this->successResponse([
            'event_id' => $event->id,
            'mistake_type' => $request->mistake_type,
            'total_mistakes' => $totalMistakes,
            'total_penalty' => $totalPenalty,
        ], 'Mistake recorded.', 201);
    }

    /**
     * Finish a WebXR session.
     * POST /api/v1/vr/webxr/sessions/{session}/finish
     */
    public function finish(Request $request, VrSession $session)
    {
        $request->validate([
            'total_score' => 'required|integer|min:0',
            'duration_seconds' => 'required|integer|min:0',
            'steps_completed' => 'required|integer|min:0',
            'total_steps' => 'required|integer|min:0',
            'total_mistakes' => 'nullable|integer|min:0',
            'completion_summary' => 'nullable|array',
        ]);

        if ($session->user_id !== $request->user()->id) {
            return $this->errorResponse('Unauthorized.', 403);
        }

        if (!in_array($session->session_status, ['starting', 'playing'])) {
            return $this->errorResponse("Session sudah {$session->session_status}.", 422);
        }

        // Update session
        $existingSummary = $session->summary_json ?? [];
        $session->update([
            'session_status' => 'completed',
            'progress_percentage' => 100,
            'total_score' => $request->total_score,
            'duration_seconds' => $request->duration_seconds,
            'total_mistakes' => $request->total_mistakes ?? 0,
            'completed_at' => Carbon::now(),
            'last_activity_at' => Carbon::now(),
            'summary_json' => array_merge($existingSummary, [
                'steps_completed' => $request->steps_completed,
                'total_steps' => $request->total_steps,
                'completion_summary' => $request->completion_summary,
            ]),
        ]);

        // Update journey progress (unlock post-test if applicable)
        $this->updateJourneyProgress($session);

        // Determine next scene
        $nextScene = null;
        if ($session->scene) {
            $nextScene = Scene::where('training_module_id', $session->training_module_id)
                ->where('order_index', '>', $session->scene->order_index)
                ->active()
                ->ordered()
                ->first();
        }

        // Calculate rank
        $rank = match (true) {
            $request->total_score >= 90 => 'A',
            $request->total_score >= 80 => 'B',
            $request->total_score >= 70 => 'C',
            $request->total_score >= 60 => 'D',
            default => 'E',
        };

        return $this->successResponse([
            'session_id' => $session->id,
            'status' => 'completed',
            'total_score' => $request->total_score,
            'rank' => $rank,
            'duration_seconds' => $request->duration_seconds,
            'total_mistakes' => $request->total_mistakes ?? 0,
            'next_scene' => $nextScene ? [
                'slug' => $nextScene->slug,
                'title' => $nextScene->title,
                'is_locked' => !$nextScene->isUnlockedFor($request->user()),
            ] : null,
        ], 'Session completed.');
    }

    /**
     * Get current active WebXR session.
     * GET /api/v1/vr/webxr/sessions/current
     */
    public function current(Request $request)
    {
        $user = $request->user();

        $session = VrSession::where('user_id', $user->id)
            ->where('platform', 'webxr')
            ->whereIn('session_status', ['starting', 'playing'])
            ->orderBy('last_activity_at', 'desc')
            ->with('scene:id,slug,title')
            ->first();

        if (!$session) {
            return $this->successResponse(null, 'No active WebXR session.');
        }

        return $this->successResponse([
            'session_id' => $session->id,
            'scene' => $session->scene ? [
                'slug' => $session->scene->slug,
                'title' => $session->scene->title,
            ] : null,
            'status' => $session->session_status,
            'progress' => $session->progress_percentage,
            'current_step' => $session->current_step,
            'started_at' => $session->started_at?->toDateTimeString(),
        ], 'Current session retrieved.');
    }

    /**
     * Get session detail by ID.
     * GET /api/v1/vr/webxr/sessions/{session}
     */
    public function show(Request $request, VrSession $session)
    {
        if ($session->user_id !== $request->user()->id) {
            return $this->errorResponse('Unauthorized.', 403);
        }

        $stepCompletions = VrStepCompletion::where('vr_session_id', $session->id)
            ->with('step:id,slug,title,max_score')
            ->get()
            ->map(fn($c) => [
                'step' => $c->step ? $c->step->slug : null,
                'title' => $c->step ? $c->step->title : null,
                'score' => $c->score,
                'time_seconds' => $c->time_seconds,
                'mistakes' => $c->mistakes_count,
            ]);

        return $this->successResponse([
            'session_id' => $session->id,
            'scene' => $session->scene ? $session->scene->slug : null,
            'status' => $session->session_status,
            'total_score' => $session->total_score,
            'duration_seconds' => $session->duration_seconds,
            'total_mistakes' => $session->total_mistakes,
            'progress' => $session->progress_percentage,
            'started_at' => $session->started_at?->toDateTimeString(),
            'completed_at' => $session->completed_at?->toDateTimeString(),
            'step_completions' => $stepCompletions,
            'summary' => $session->summary_json,
        ], 'Session detail retrieved.');
    }

    /**
     * Update user journey progress after session completion.
     */
    private function updateJourneyProgress(VrSession $session): void
    {
        try {
            $progress = \App\Models\UserTrainingProgress::firstOrCreate(
                [
                    'user_id' => $session->user_id,
                    'training_module_id' => $session->training_module_id,
                ],
                [
                    'pre_test_status' => 'available',
                    'vr_status' => 'not_started',
                    'post_test_status' => 'locked',
                ]
            );

            $progress->vr_status = 'completed';
            $progress->last_active_step = 'vr_sim';

            // Check if all scenes in this module are completed
            $totalScenes = Scene::where('training_module_id', $session->training_module_id)
                ->active()->where('priority', 'P0')->count();
            $completedScenes = VrSession::where('user_id', $session->user_id)
                ->where('training_module_id', $session->training_module_id)
                ->where('session_status', 'completed')
                ->distinct('scene_id')
                ->count('scene_id');

            if ($completedScenes >= $totalScenes && $progress->post_test_status === 'locked') {
                $progress->post_test_status = 'available';
            }

            $progress->save();
        } catch (\Exception $e) {
            Log::warning('Failed to update journey progress: ' . $e->getMessage());
        }
    }
}
