<?php

namespace App\Http\Controllers\Api\V1\Vr;

use App\Http\Controllers\Controller;
use App\Models\VrDevice;
use App\Models\VrSession;
use App\Models\TrainingModule;
use App\Models\UserTrainingProgress;
use App\Models\Scene;
use App\Services\LearningReadinessService;
use App\Services\ProductionPathService;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;

class VrStatusController extends Controller
{
    use ApiResponse;

    public function __construct(
        private readonly ProductionPathService $productionPath,
        private readonly LearningReadinessService $learningReadiness
    )
    {
    }

    /**
     * Get current VR connection status for the logged-in user.
     */
    public function status(Request $request)
    {
        $user = $request->user();

        $device = VrDevice::where('user_id', $user->id)
            ->where('status', 'active')
            ->orderBy('last_seen_at', 'desc')
            ->first();

        $activeSession = VrSession::where('user_id', $user->id)
            ->whereIn('session_status', ['starting', 'playing'])
            ->with('trainingModule')
            ->first();

        // Connection State Logic
        $connectionStatus = 'offline';
        if ($device && $device->last_seen_at) {
            $diffMinutes = $device->last_seen_at->diffInMinutes(now());
            if ($diffMinutes <= 2) {
                $connectionStatus = 'connected';
            } elseif ($diffMinutes <= 10) {
                $connectionStatus = 'standby';
            }
        }

        $paired = (bool) $device;
        $ready = $paired && ($connectionStatus === 'connected' || $connectionStatus === 'standby');

        // Recommended Next Action & Route
        $nextAction = 'Hubungkan Meta Quest 3';
        $nextRoute = '/vr/connect';
        
        if (!$paired) {
            $nextAction = 'Mulai Pairing Perangkat';
            $nextRoute = '/vr/pairing/start';
        } elseif ($activeSession) {
            $nextAction = 'Lanjutkan Sesi Pelatihan';
            $nextRoute = '/vr/sessions/' . $activeSession->id;
        } elseif ($ready) {
            $nextAction = 'Pilih Modul Pelatihan';
            $nextRoute = '/training-hub';
        }

        return $this->successResponse([
            'device_type' => $device ? $device->device_type : 'meta_quest_3',
            'connection_status' => $connectionStatus,
            'paired' => $paired,
            'ready' => $ready,
            'headset_name' => $device ? $device->device_name : null,
            'app_version' => $device ? $device->app_version : '1.0.0', // Fallback or placeholder
            'last_seen_at' => $device ? $device->last_seen_at->toDateTimeString() : null,
            'active_session_id' => $activeSession ? $activeSession->id : null,
            'active_module_summary' => $activeSession ? [
                'id' => $activeSession->trainingModule?->id,
                'title' => $activeSession->trainingModule?->title,
                'progress' => $activeSession->progress_percentage,
                'status' => $activeSession->session_status,
            ] : null,
            'recommended_next_action' => $nextAction,
            'recommended_next_route' => $nextRoute,
        ]);
    }

    /**
     * Comprehensive launch readiness check for a specific module.
     */
    public function launchReadiness(Request $request, $moduleSlug)
    {
        $module = TrainingModule::where('slug', $moduleSlug)->firstOrFail();

        return $this->successResponse([
            ...$this->learningReadiness->forModule($request->user(), $module),
        ]);
    }

    public function moduleReadiness(Request $request, $moduleSlug)
    {
        $module = TrainingModule::where('slug', $moduleSlug)->firstOrFail();

        return $this->successResponse($this->learningReadiness->forModule($request->user(), $module));
    }

    public function sceneReadiness(Request $request, $sceneSlug)
    {
        return $this->successResponse($this->learningReadiness->forScene($request->user(), $sceneSlug));
    }

    /**
     * Check if a module is ready to launch.
     */
    public function readiness(Request $request, $moduleId)
    {
        $user = $request->user();
        $module = TrainingModule::findOrFail($moduleId);

        // Logic from Phase 3: Check if prerequisites are met
        $progress = UserTrainingProgress::where('user_id', $user->id)
            ->where('training_module_id', $moduleId)
            ->first();

        $isReady = $progress && in_array($progress->status, ['available', 'in_progress', 'completed']);
        $reason = $isReady ? null : 'Module is locked or prerequisites not met.';

        return $this->successResponse([
            'module_id' => $module->id,
            'module_title' => $module->title,
            'is_ready' => $isReady,
            'reason' => $reason,
            'launch_command' => $isReady ? "LAUNCH_VR_{$module->id}" : null
        ]);
    }

    private function hasPassedPosttest(int $userId, int $moduleId): bool
    {
        $postTest = \App\Models\Assessment::where('module_id', $moduleId)
            ->where('type', \App\Enums\AssessmentType::POSTTEST->value)
            ->first();

        if (!$postTest) {
            return false;
        }

        return \App\Models\AssessmentAttempt::where('user_id', $userId)
            ->where('assessment_id', $postTest->id)
            ->where('passed', true)
            ->exists();
    }

    private function hasPassedPosttestForSlug(int $userId, string $moduleSlug): bool
    {
        $moduleId = TrainingModule::where('slug', $moduleSlug)->value('id');

        if (!$moduleId) {
            return false;
        }

        return $this->hasPassedPosttest($userId, (int) $moduleId);
    }

    private function canUseInstructorMode($user): bool
    {
        $role = strtolower((string) $user->role);

        return in_array($role, ['admin', 'super_admin', 'superadmin', 'instructor', 'dosen', 'lecturer'], true)
            || (bool) $user->can_bypass_prerequisites;
    }
}
