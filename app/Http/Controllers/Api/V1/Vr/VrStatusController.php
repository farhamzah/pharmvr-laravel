<?php

namespace App\Http\Controllers\Api\V1\Vr;

use App\Http\Controllers\Controller;
use App\Models\VrDevice;
use App\Models\VrSession;
use App\Models\TrainingModule;
use App\Models\UserTrainingProgress;
use App\Models\Scene;
use App\Services\ProductionPathService;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;

class VrStatusController extends Controller
{
    use ApiResponse;

    public function __construct(private readonly ProductionPathService $productionPath)
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
        $user = $request->user();
        $module = TrainingModule::where('slug', $moduleSlug)->firstOrFail();
        $sceneConfig = $this->productionPath->scene($moduleSlug);
        $previousSceneSlug = $sceneConfig['previous_slug'] ?? null;
        $nextSceneSlug = $sceneConfig['next_slug'] ?? null;
        $sceneUnlocked = !$previousSceneSlug || $this->hasPassedPosttestForSlug($user->id, $previousSceneSlug);

        // 1. Phase 3: Pre-test Readiness
        $preTest = $module->assessments()->where('type', 'pretest')->first();
        $preTestCompletedAttempt = null;
        $preTestPassedAttempt = null;
        if ($preTest) {
            $preTestCompletedAttempt = \App\Models\AssessmentAttempt::where('user_id', $user->id)
                ->where('assessment_id', $preTest->id)
                ->whereNotNull('completed_at')
                ->orderBy('completed_at', 'desc')
                ->first();

            $preTestPassedAttempt = \App\Models\AssessmentAttempt::where('user_id', $user->id)
                ->where('assessment_id', $preTest->id)
                ->where('passed', true)
                ->orderBy('score', 'desc')
                ->first();
        }

        $preTestCompleted = (bool) $preTestCompletedAttempt;
        $preTestPassed = $preTestPassedAttempt && $preTestPassedAttempt->score >= ($preTest->passing_score ?? 0);

        // 2. Phase 4: Quest 3 Connectivity
        $device = VrDevice::where('user_id', $user->id)
            ->where('status', 'active')
            ->orderBy('last_seen_at', 'desc')
            ->first();

        $connectionStatus = 'offline';
        if ($device && $device->last_seen_at) {
            $diffMinutes = $device->last_seen_at->diffInMinutes(now());
            if ($diffMinutes <= 2) {
                $connectionStatus = 'connected';
            } elseif ($diffMinutes <= 10) {
                $connectionStatus = 'standby';
            }
        }

        $quest3Paired = (bool) $device;
        $quest3Connected = ($connectionStatus === 'connected' || $connectionStatus === 'standby');

        // 3. Journey & Session State
        $progress = UserTrainingProgress::where('user_id', $user->id)
            ->where('training_module_id', $module->id)
            ->first();
        $scene = Scene::where('slug', $moduleSlug)->first();
        $hasCompletedVrSession = VrSession::where('user_id', $user->id)
            ->where('session_status', 'completed')
            ->when($scene, fn ($query) => $query->where('scene_id', $scene->id))
            ->when(!$scene, fn ($query) => $query->where('training_module_id', $module->id))
            ->exists();

        $vrStatus = $progress?->vr_status ?? ($preTestCompleted ? 'available' : 'locked');
        if ($hasCompletedVrSession) {
            $vrStatus = 'completed';
        } elseif (!$sceneUnlocked) {
            $vrStatus = 'locked';
        }
        $postTestStatus = $progress?->post_test_status ?? 'locked';
        $postTestPassed = $this->hasPassedPosttestForSlug($user->id, $module->slug);
        if ($postTestPassed) {
            $postTestStatus = 'passed';
        } elseif ($vrStatus === 'completed' && $postTestStatus === 'locked') {
            $postTestStatus = 'available';
        }

        $activeSession = VrSession::where('user_id', $user->id)
            ->whereIn('session_status', ['starting', 'playing'])
            ->first();

        $canLaunchVr = $sceneUnlocked
            && ($preTestCompleted || $user->can_bypass_prerequisites)
            && $vrStatus !== 'completed';
        $eligibleToLaunch = $canLaunchVr && $quest3Paired && $quest3Connected && !$activeSession;

        // 4. Checklist & Reasons
        $checklist = [
            ['label' => 'Modul Tersedia', 'status' => true],
            ['label' => 'Pre-test Selesai', 'status' => $preTestCompleted],
            ['label' => 'Lulus Pre-test', 'status' => $preTestPassed],
            ['label' => 'Simulasi VR Selesai', 'status' => $vrStatus === 'completed'],
            ['label' => 'Meta Quest 3 Terhubung', 'status' => $quest3Paired],
            ['label' => 'Quest 3 Aktif/Online', 'status' => $quest3Connected],
            ['label' => 'Tidak Ada Sesi Aktif', 'status' => !$activeSession],
        ];

        $blockingReasons = [];
        if (!$sceneUnlocked && $previousSceneSlug) $blockingReasons[] = 'Selesaikan Post-test ' . str_replace('_', ' ', $previousSceneSlug) . ' terlebih dahulu.';
        if (!$preTestCompleted) $blockingReasons[] = 'Anda harus lulus Pre-test terlebih dahulu.';
        if (!$quest3Paired) $blockingReasons[] = 'Headset Meta Quest 3 belum dipasangkan (paired).';
        if ($quest3Paired && !$quest3Connected) $blockingReasons[] = 'Headset Meta Quest 3 sedang offline atau standby.';
        if ($activeSession) $blockingReasons[] = 'Terdapat sesi VR lain yang sedang berjalan.';

        // 5. Recommendations
        $nextActionCode = 'launch_vr';
        $nextAction = 'Luncurkan Pelatihan VR';
        $nextRoute = '/vr/launch/' . $module->slug;
        $legacyNextAction = null;

        if (!$sceneUnlocked && $previousSceneSlug) {
            $nextActionCode = 'pretest_required';
            $nextAction = 'Selesaikan tahap sebelumnya';
            $nextRoute = '/assessments/' . $previousSceneSlug . '/post_test';
        } elseif (!$preTestCompleted) {
            $nextActionCode = 'pretest_required';
            $nextAction = 'Kerjakan Pre-test';
            $nextRoute = '/assessments/' . $module->slug . '/pre_test';
        } elseif ($vrStatus === 'completed' && !$postTestPassed) {
            $nextActionCode = 'posttest_required';
            $nextAction = 'Kerjakan Post-test';
            $nextRoute = '/assessments/' . $module->slug . '/post_test';
        } elseif ($postTestPassed) {
            if ($nextSceneSlug) {
                $nextActionCode = 'next_scene_unlocked';
                $nextAction = 'Lanjut ' . str_replace('_', ' ', $nextSceneSlug);
                $nextRoute = '/assessments/' . $nextSceneSlug . '/pre_test';
                if ($module->slug === 'hygiene' && $nextSceneSlug === 'gowning') {
                    $legacyNextAction = 'gowning_unlocked';
                }
            } else {
                $nextActionCode = 'production_path_completed';
                $nextAction = 'Production Path selesai';
                $nextRoute = '/vr/production-path-report';
            }
        } elseif (!$quest3Paired) {
            $nextAction = 'Pasangkan Quest 3';
            $nextRoute = '/vr/pairing/start';
        } elseif (!$quest3Connected) {
            $nextAction = 'Hubungkan Quest 3';
            $nextRoute = '/vr/connect';
        } elseif ($activeSession) {
            $nextAction = 'Lihat Sesi Aktif';
            $nextRoute = '/vr/sessions/' . $activeSession->id;
        }

        return $this->successResponse([
            'scene_slug' => $module->slug,
            'scene_unlocked' => $sceneUnlocked,
            'previous_scene_slug' => $previousSceneSlug,
            'next_scene_slug' => $nextSceneSlug,
            'module' => [
                'id' => $module->id,
                'slug' => $module->slug,
                'title' => $module->title,
            ],
            'pre_test_completed' => $preTestCompleted,
            'pre_test_passed' => $preTestPassed,
            'vr_status' => $vrStatus,
            'post_test_status' => $postTestStatus,
            'post_test_passed' => $postTestPassed,
            'posttest_passed' => $postTestPassed,
            'can_launch_vr' => $canLaunchVr,
            'next_action' => $nextActionCode,
            'legacy_next_action' => $legacyNextAction,
            'quest3_paired' => $quest3Paired,
            'quest3_connected' => $quest3Connected,
            'eligible_to_launch' => $eligibleToLaunch,
            'checklist' => $checklist,
            'blocking_reasons' => $blockingReasons,
            'recommended_next_action' => $nextAction,
            'recommended_next_route' => $nextRoute,
        ]);
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
}
