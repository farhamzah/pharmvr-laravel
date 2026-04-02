<?php

namespace App\Http\Controllers\Api\V1\Vr;

use App\Http\Controllers\Controller;
use App\Models\TrainingModule;
use App\Models\User;
use App\Models\VrDevice;
use App\Models\VrPairing;
use App\Models\VrSession;
use App\Models\VrSessionEvent;
use App\Models\VrSessionHint;
use App\Models\VrSessionStageResult;
use App\Traits\ApiResponse;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Services\Ai\AiChatService;
use App\Services\Analytics\SessionSummaryService;
use App\Services\Analytics\AchievementService;

class VrSessionController extends Controller
{
    use ApiResponse;

    protected $summaryService;
    protected $achievementService;
    protected $aiChatService;

    public function __construct(
        SessionSummaryService $summaryService, 
        AchievementService $achievementService,
        AiChatService $aiChatService
    ) {
        $this->summaryService = $summaryService;
        $this->achievementService = $achievementService;
        $this->aiChatService = $aiChatService;
    }

    /**
     * Helper to validate device token.
     */
    private function validateDevice(Request $request)
    {
        $headsetToken = $request->header('X-VR-Device-Token') ?? $request->device_access_token;
        $headsetId = $request->header('X-VR-Headset-ID');

        if (!$headsetToken) {
            return null;
        }

        // Optimization (Phase 4.1): If Headset ID is provided, use indexed lookup
        if ($headsetId) {
            $device = VrDevice::where('headset_identifier', $headsetId)
                ->where('status', 'active')
                ->first();
            
            if ($device && Hash::check($headsetToken, $device->device_token_hash)) {
                return $device;
            }
            return null;
        }

        // Fallback for legacy calls or if header is missing (High Cost)
        $devices = VrDevice::where('status', 'active')->get();
        return $devices->first(function ($d) use ($headsetToken) {
            return Hash::check($headsetToken, $d->device_token_hash);
        });
    }

    /**
     * Get the current or most recent session for the authenticated mobile user.
     */
    public function current(Request $request)
    {
        $user = $request->user();
        
        $session = VrSession::where('user_id', $user->id)
            ->whereIn('session_status', ['starting', 'playing', 'interrupted'])
            ->orderBy('last_activity_at', 'desc')
            ->first();

        if (!$session) {
            // Check for most recently completed session as a fallback
            $session = VrSession::where('user_id', $user->id)
                ->where('session_status', 'completed')
                ->orderBy('completed_at', 'desc')
                ->first();
        }

        if (!$session) {
            return $this->successResponse(null, 'No active or recent VR sessions found.');
        }

        return $this->successResponse($this->formatSessionResponse($session), 'Current session retrieved.');
    }

    /**
     * Get a list of VR sessions for the authenticated mobile user.
     */
    public function index(Request $request)
    {
        /** @var \Illuminate\Database\Eloquent\Builder $query */
        $query = VrSession::with(['user', 'module']);
    }

    /**
     * Show a specific VR session.
     */
    public function show(Request $request, $id)
    {
        $session = VrSession::where('user_id', $request->user()->id)
            ->findOrFail($id);

        return $this->successResponse($this->formatSessionResponse($session), 'Session details retrieved.');
    }

    /**
     * Start a VR training session (Headset Side).
     */
    public function headsetStart(Request $request)
    {
        $request->validate([
            'training_module_id' => 'required|exists:training_modules,id',
            'device_access_token' => 'required|string',
        ]);

        $device = $this->validateDevice($request);

        if (!$device) {
            return $this->errorResponse('Invalid device access token or device deactivated.', 401);
        }

        // Interrupt any previous active sessions for this user
        VrSession::where('user_id', $device->user_id)
            ->whereIn('session_status', ['starting', 'playing'])
            ->update([
                'session_status' => 'interrupted',
                'interrupted_at' => Carbon::now()
            ]);

        $session = VrSession::create([
            'user_id' => $device->user_id,
            'device_id' => $device->id,
            'training_module_id' => $request->training_module_id,
            'pairing_id' => $device->current_pairing_id,
            'session_status' => 'starting',
            'progress_percentage' => 0,
            'started_at' => Carbon::now(),
            'last_activity_at' => Carbon::now(),
        ]);

        return $this->successResponse([
            'session_id' => $session->id,
            'session_status' => $session->session_status,
        ], 'VR session started.');
    }

    /**
     * Update session progress from the headset.
     */
    public function updateProgress(Request $request, VrSession $session)
    {
        $request->validate([
            'device_access_token' => 'required|string',
            'current_step' => 'nullable|string|max:100',
            'progress_percentage' => 'nullable|integer|min:0|max:100',
            'status' => 'nullable|string|in:starting,playing',
            'summary_payload' => 'nullable|array',
        ]);

        // 1. Validate Device
        $device = $this->validateDevice($request);
        if (!$device || $session->device_id !== $device->id) {
            return $this->errorResponse('Unauthorized or invalid device token.', 401);
        }

        // 2. Security: Ownership
        if ($session->user_id !== $device->user_id) {
            return $this->errorResponse('Session mismatch for this device.', 403);
        }

        // 3. Status Transition Logic
        if (!in_array($session->session_status, ['starting', 'playing'])) {
            return $this->errorResponse("Cannot update progress for a session in status: {$session->session_status}", 422);
        }

        $session->update([
            'current_step' => $request->current_step ?? $session->current_step,
            'progress_percentage' => $request->progress_percentage ?? $session->progress_percentage,
            'session_status' => $request->status ?? 'playing',
            'summary_json' => $request->summary_payload ?? $session->summary_json,
            'last_activity_at' => Carbon::now(),
        ]);

        // Update device heartbeat
        $device->update(['last_seen_at' => Carbon::now()]);

        return $this->successResponse([
            'session_id' => $session->id,
            'session_status' => $session->session_status,
            'progress_percentage' => $session->progress_percentage,
            'current_step' => $session->current_step,
        ], 'Session progress updated.');
    }

    /**
     * Start a VR session from the authenticated mobile side.
     */
    public function mobileStart(Request $request)
    {
        $request->validate([
            'module_slug' => 'required|string|exists:training_modules,slug',
            'pairing_id' => 'nullable|integer|exists:vr_pairings,id',
            'device_id' => 'nullable|integer|exists:vr_devices,id',
        ]);

        $user = $request->user();
        $module = TrainingModule::where('slug', $request->module_slug)->firstOrFail();

        // 1. Find Device (Latest active paired device)
        $device = VrDevice::where('user_id', $user->id)
            ->where('status', 'active')
            ->orderBy('last_seen_at', 'desc')
            ->first();

        if (!$device) {
            return $this->errorResponse('No active paired Quest 3 device found. Please pair your device first.', 422);
        }

        // 2. Checklist / Readiness validation (Simplified version of launchReadiness)
        $preTest = $module->assessments()->where('type', \App\Enums\AssessmentType::PRETEST->value)->first();
        $preTestPassed = true;
        
        if ($preTest) {
            $preTestAttempt = \App\Models\AssessmentAttempt::where('user_id', $user->id)
                ->where('assessment_id', $preTest->id)
                ->where('status', 'passed')
                ->first();
            
            if (!$preTestAttempt || $preTestAttempt->score < ($preTest->min_score ?? 80)) {
                $preTestPassed = false;
            }
        }
        
        $canLaunch = $preTestPassed || $user->can_bypass_prerequisites;

        if (!$canLaunch) {
            return $this->errorResponse('User has not passed the required pre-test for this module.', 422);
        }

        // 3. Connection Check
        $diffMinutes = $device->last_seen_at ? $device->last_seen_at->diffInMinutes(now()) : 999;
        if ($diffMinutes > 10) {
            return $this->errorResponse('Quest 3 device is offline or standby. Please turn on your headset.', 422);
        }

        // 4. Interrupt previous sessions
        VrSession::where('user_id', $user->id)
            ->whereIn('session_status', ['starting', 'playing'])
            ->update([
                'session_status' => 'interrupted',
                'interrupted_at' => Carbon::now()
            ]);

        // 5. Create Session
        $session = VrSession::create([
            'user_id' => $user->id,
            'device_id' => $device->id,
            'training_module_id' => $module->id,
            'pairing_id' => $device->current_pairing_id,
            'session_status' => 'starting',
            'progress_percentage' => 0,
            'started_at' => Carbon::now(),
            'last_activity_at' => Carbon::now(),
        ]);

        return $this->successResponse([
            'session_id' => $session->id,
            'session_status' => $session->session_status,
            'module' => [
                'id' => $module->id,
                'title' => $module->title,
                'slug' => $module->slug,
            ],
            'device' => [
                'id' => $device->id,
                'name' => $device->device_name,
            ],
            'started_at' => $session->started_at->toDateTimeString(),
            'current_step' => null,
            'progress_percentage' => 0,
            'recommended_frontend_state' => 'vr_session_active'
        ], 'VR session initiated from mobile.');
    }

    /**
     * Complete a VR session from the headset.
     */
    public function completeSession(Request $request, VrSession $session)
    {
        $request->validate([
            'device_access_token' => 'required|string',
            'final_step' => 'nullable|string|max:100',
            'final_progress' => 'nullable|integer|min:0|max:100',
            'completion_summary' => 'nullable|array',
        ]);

        $device = $this->validateDevice($request);
        if (!$device || $session->device_id !== $device->id) {
            return $this->errorResponse('Unauthorized or invalid device token.', 401);
        }

        if ($session->user_id !== $device->user_id) {
            return $this->errorResponse('Session mismatch.', 403);
        }

        if (!in_array($session->session_status, ['starting', 'playing'])) {
            return $this->errorResponse("Session is already {$session->session_status}", 422);
        }

        $session->update([
            'session_status' => 'completed',
            'progress_percentage' => $request->final_progress ?? 100,
            'current_step' => $request->final_step ?? $session->current_step,
            'summary_json' => $request->completion_summary ?? $session->summary_json,
            'completed_at' => Carbon::now(),
            'last_activity_at' => Carbon::now(),
        ]);

        // [NEW] Update Journey Progress
        $this->updateVrJourneyProgress($session->user_id, $session->training_module_id);

        // Phase 6: Sync Analytics and Achievements
        $analytics = $this->summaryService->summarize($session);
        $this->achievementService->evaluateAfterSession($session, $analytics);
        
        // Phase 6.1: Trigger AI Post-Session Evaluation
        $this->aiChatService->generateEvaluation($analytics);

        return $this->successResponse([
            'session_id' => $session->id,
            'session_status' => $session->session_status,
            'analytics_summary' => [
                'total_score' => $analytics->total_score,
                'accuracy_score' => $analytics->accuracy_score,
                'breach_count' => $analytics->breach_count,
            ]
        ], 'Session marked as completed. Performance summary generated.');
    }

    /**
     * Interrupt a VR session from the headset.
     */
    public function interruptSession(Request $request, VrSession $session)
    {
        $request->validate([
            'device_access_token' => 'required|string',
            'reason' => 'required|string|max:255',
            'error_code' => 'nullable|string|max:100',
            'reconnect_recommended' => 'nullable|boolean',
        ]);

        $device = $this->validateDevice($request);
        if (!$device || $session->device_id !== $device->id) {
            return $this->errorResponse('Unauthorized or invalid device token.', 401);
        }

        if (!in_array($session->session_status, ['starting', 'playing'])) {
            return $this->errorResponse("Session is already in finale state: {$session->session_status}", 422);
        }

        // Store reason/error in summary_json or current_step
        $summary = $session->summary_json ?? [];
        $summary['interrupt'] = [
            'reason' => $request->reason,
            'error_code' => $request->error_code,
            'reconnect_recommended' => $request->reconnect_recommended,
        ];

        $session->update([
            'session_status' => 'interrupted',
            'interrupted_at' => Carbon::now(),
            'summary_json' => $summary,
            'last_activity_at' => Carbon::now(),
        ]);

        return $this->successResponse([
            'session_id' => $session->id,
            'session_status' => $session->session_status,
        ], 'Session interrupted.');
    }

    /**
     * Store a VR session event (Telemetry).
     */
    public function storeEvent(Request $request, VrSession $session)
    {
        $request->validate([
            'device_access_token' => 'required|string',
            'event_type' => 'required|string|max:100',
            'event_timestamp' => 'required|date',
            'event_payload' => 'nullable|array',
        ]);

        // 1. Validate Device
        $device = $this->validateDevice($request);
        if (!$device || $session->device_id !== $device->id) {
            return $this->errorResponse('Unauthorized or invalid device token.', 401);
        }

        // 2. Security: Ensure session belongs to device/user and is active
        if ($session->user_id !== $device->user_id || $session->session_status !== 'playing') {
             return $this->errorResponse('Session mismatch or session is not active.', 403);
        }

        // 3. Create Event
        $event = $session->events()->create([
            'user_id' => $session->user_id,
            'training_module_id' => $session->training_module_id,
            'device_id' => $device->id,
            'event_type' => $request->event_type,
            'event_timestamp' => $request->event_timestamp,
            'event_payload' => $request->event_payload,
        ]);

        return $this->successResponse([
            'event_id' => $event?->id,
            'event_type' => $event?->event_type,
        ], 'Session event stored successfully.');
    }

    /**
     * Store a specialized VR quiz event.
     */
    public function storeQuizEvent(Request $request, VrSession $session)
    {
        $request->validate([
            'device_access_token' => 'required|string',
            'event_type' => 'required|string|in:quiz_started,quiz_question_shown,quiz_answer_selected,quiz_submitted,quiz_completed',
            'event_timestamp' => 'required|date',
            'event_payload' => 'required|array',
            'event_payload.quiz_id' => 'required|string',
        ]);

        // 1. Validate Device
        $device = $this->validateDevice($request);
        if (!$device || $session->device_id !== $device->id) {
            return $this->errorResponse('Unauthorized or invalid device token.', 401);
        }

        // 2. Security: Ensure session belongs to device/user and is active
        if ($session->user_id !== $device->user_id || $session->session_status !== 'playing') {
             return $this->errorResponse('Session mismatch or session is not active.', 403);
        }

        // 3. Create Quiz Event using the base telemetry model
        $event = $session->events()->create([
            'user_id' => $session->user_id,
            'training_module_id' => $session->training_module_id,
            'device_id' => $device->id,
            'event_type' => $request->event_type,
            'event_timestamp' => $request->event_timestamp,
            'event_payload' => $request->event_payload,
        ]);

        return $this->successResponse([
            'event_id' => $event?->id,
            'event_type' => $event?->event_type,
            'quiz_id' => $request->event_payload['quiz_id'],
        ], 'In-VR quiz event stored successfully.');
    }

    /**
     * Store a mid-session evaluation stage result (Staged Post-Test Foundation).
     */
    public function storeStageResult(Request $request, VrSession $session)
    {
        $request->validate([
            'device_access_token' => 'required|string',
            'stage_name' => 'required|string|max:100',
            'stage_score' => 'nullable|numeric|min:0|max:100',
            'passed' => 'required|boolean',
            'submitted_at' => 'required|date',
            'metadata' => 'nullable|array',
        ]);

        // 1. Validate Device
        $device = $this->validateDevice($request);
        if (!$device || $session->device_id !== $device->id) {
            return $this->errorResponse('Unauthorized or invalid device token.', 401);
        }

        // 2. Security: Ensure session belongs to device/user and is active
        if ($session->user_id !== $device->user_id || $session->session_status !== 'playing') {
             return $this->errorResponse('Session mismatch or session is not active.', 403);
        }

        // 3. Create Stage Result
        $result = $session->stageResults()->create([
            'user_id' => $session->user_id,
            'training_module_id' => $session->training_module_id,
            'stage_name' => $request->stage_name,
            'stage_score' => $request->stage_score,
            'passed' => $request->passed,
            'submitted_at' => $request->submitted_at,
            'metadata' => $request->metadata,
        ]);

        return $this->successResponse([
            'result_id' => $result?->id,
            'stage_name' => $result?->stage_name,
            'passed' => $result?->passed,
        ], 'Stage evaluation result stored successfully.');
    }

    /**
     * Store an AI reminder/hint log.
     */
    public function storeHintLog(Request $request, VrSession $session)
    {
        $request->validate([
            'device_access_token' => 'required|string',
            'hint_type' => 'required|string|in:reminder,warning,guide,explain',
            'trigger_reason' => 'required|string|max:100',
            'related_step' => 'nullable|string|max:100',
            'displayed_text' => 'required|string',
            'displayed_at' => 'required|date',
        ]);

        // 1. Validate Device
        $device = $this->validateDevice($request);
        if (!$device || $session->device_id !== $device->id) {
            return $this->errorResponse('Unauthorized or invalid device token.', 401);
        }

        // 2. Security: Ensure session belongs to device/user and is active
        if ($session->user_id !== $device->user_id || $session->session_status !== 'playing') {
             return $this->errorResponse('Session mismatch or session is not active.', 403);
        }

        // 3. Create Hint Log
        $hint = $session->hints()->create([
            'user_id' => $session->user_id,
            'training_module_id' => $session->training_module_id,
            'hint_type' => $request->hint_type,
            'trigger_reason' => $request->trigger_reason,
            'related_step' => $request->related_step,
            'displayed_text' => $request->displayed_text,
            'displayed_at' => $request->displayed_at,
        ]);

        return $this->successResponse([
            'hint_id' => $hint?->id,
            'hint_type' => $hint?->hint_type,
        ], 'AI hint log stored successfully.');
    }

    /**
     * Store a unified learning event (Multiplexer).
     */
    public function storeUnifiedEvent(Request $request, VrSession $session)
    {
        $request->validate([
            'occurred_at' => 'required|date',
            'category' => 'required|string|in:session_event,quiz_event,stage_result,reminder_event',
            'type' => 'required|string|max:100',
            'payload' => 'required|array',
        ]);

        // Map unified fields to internal method expectations
        $request->merge([
            'event_timestamp' => $request->occurred_at,
            'event_type' => $request->type,
            'event_payload' => $request->payload,
        ]);

        return match ($request->category) {
            'session_event' => $this->storeEvent($request, $session),
            'quiz_event' => $this->storeQuizEvent($request, $session),
            'stage_result' => $this->dispatchStageResult($request, $session),
            'reminder_event' => $this->dispatchHintLog($request, $session),
        };
    }

    private function dispatchStageResult($request, $session)
    {
        $payload = $request->payload;
        $request->merge([
            'stage_name' => $request->type,
            'stage_score' => $payload['stage_score'] ?? null,
            'passed' => $payload['passed'] ?? false,
            'submitted_at' => $request->occurred_at,
            'metadata' => $payload,
        ]);
        return $this->storeStageResult($request, $session);
    }

    private function dispatchHintLog($request, $session)
    {
        $payload = $request->payload;
        $request->merge([
            'hint_type' => $request->type,
            'trigger_reason' => $payload['trigger_reason'] ?? 'unknown',
            'displayed_text' => $payload['displayed_text'] ?? '',
            'displayed_at' => $request->occurred_at,
        ]);
        return $this->storeHintLog($request, $session);
    }

    /**
     * Update the VR journey progress status.
     */
    private function updateVrJourneyProgress($userId, $trainingModuleId)
    {
        $progress = \App\Models\UserTrainingProgress::where('user_id', $userId)
            ->where('training_module_id', $trainingModuleId)
            ->first();

        if ($progress) {
            $progress->vr_status = 'completed';
            // Unlock Post-Test
            if ($progress->post_test_status === 'locked') {
                $progress->post_test_status = 'available';
            }
            $progress->last_active_step = 'vr_sim';
            $progress->save();
        }
    }

    /**
     * Format VR session for mobile-side consumption.
     */
    private function formatSessionResponse(VrSession $session)
    {
        $nextAction = 'Lanjutkan di Headset';
        $nextRoute = 'vr_session_status';

        switch ($session->session_status) {
            case 'completed':
                $nextAction = 'Lihat Ringkasan';
                $nextRoute = 'vr_session_summary';
                break;
            case 'interrupted':
                $nextAction = 'Mulai Ulang Sesi';
                $nextRoute = 'vr_launch_readiness';
                break;
            case 'starting':
                $nextAction = 'Buka VR Quest 3';
                $nextRoute = 'vr_session_status';
                break;
        }

        return [
            'session_id' => $session->id,
            'session_status' => $session->session_status,
            'module_summary' => [
                'id' => $session->trainingModule->id,
                'title' => $session->trainingModule->title,
                'slug' => $session->trainingModule->slug,
            ],
            'device_summary' => [
                'id' => $session->device->id,
                'name' => $session->device->device_name,
                'type' => $session->device->device_type,
            ],
            'started_at' => $session->started_at ? $session->started_at->toDateTimeString() : null,
            'completed_at' => $session->completed_at ? $session->completed_at->toDateTimeString() : null,
            'interrupted_at' => $session->interrupted_at ? $session->interrupted_at->toDateTimeString() : null,
            'current_step' => $session->current_step,
            'progress_percentage' => $session->progress_percentage,
            'last_activity_at' => $session->last_activity_at ? $session->last_activity_at->toDateTimeString() : null,
            'recommended_next_action' => $nextAction,
            'recommended_next_route' => $nextRoute,
            'completion_summary' => $session->session_status === 'completed' ? ($session->summary_json ?? []) : null,
        ];
    }
}
