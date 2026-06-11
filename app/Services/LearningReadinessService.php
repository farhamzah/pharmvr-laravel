<?php

namespace App\Services;

use App\Enums\AssessmentType;
use App\Models\Assessment;
use App\Models\AssessmentAttempt;
use App\Models\Scene;
use App\Models\TrainingModule;
use App\Models\User;
use App\Models\UserTrainingProgress;
use App\Models\VrDevice;
use App\Models\VrSession;

class LearningReadinessService
{
    public function __construct(private readonly ProductionPathService $productionPath)
    {
    }

    public function forModule(User $user, TrainingModule $module): array
    {
        $moduleSlug = $module->slug;
        $scene = Scene::where('slug', Scene::resolveCanonicalSlug($moduleSlug))->first();
        $sceneSlug = $scene?->canonicalSlug() ?? $moduleSlug;
        $sceneConfig = $this->productionPath->scene($sceneSlug);
        $previousSceneSlug = $sceneConfig['previous_slug'] ?? $this->previousSceneSlugFromModel($scene);
        $nextSceneSlug = $sceneConfig['next_slug'] ?? null;
        $instructorMode = $this->canUseInstructorMode($user);

        $pretest = $this->assessmentFor($module, AssessmentType::PRETEST);
        $posttest = $this->assessmentFor($module, AssessmentType::POSTTEST);
        $pretestCompleted = $pretest ? (bool) $this->latestCompletedAttempt($user->id, $pretest) : false;
        $pretestPassed = $pretest ? $this->hasPassedAttempt($user->id, $pretest) : false;
        $posttestCompleted = $posttest ? (bool) $this->latestCompletedAttempt($user->id, $posttest) : false;
        $posttestPassed = $posttest ? $this->hasPassedAttempt($user->id, $posttest) : false;

        $sceneUnlocked = $instructorMode || $this->isSceneUnlocked($user, $sceneSlug, $previousSceneSlug);
        $activeSession = $this->activeSession($user, $module, $scene);
        $vrStatus = $this->vrStatus($user, $module, $scene);
        $vrCompleted = $vrStatus === 'completed';
        $canLaunchVr = $sceneUnlocked
            && ($pretestCompleted || (bool) $user->can_bypass_prerequisites || $instructorMode)
            && !$vrCompleted
            && !$activeSession;
        $canTakePosttest = $vrCompleted && !$posttestPassed && (bool) $posttest;
        $isCompleted = $posttest ? $posttestPassed : $vrCompleted;
        $lockedReason = $this->lockedReason($sceneUnlocked, $previousSceneSlug, $pretestCompleted, $vrCompleted);
        $nextAction = $this->nextAction(
            $sceneUnlocked,
            $pretestCompleted,
            (bool) $activeSession,
            $vrCompleted,
            $posttestCompleted,
            $posttestPassed,
            $nextSceneSlug
        );

        [$quest3Paired, $quest3Connected] = $this->quest3State($user);

        return [
            'module_slug' => $moduleSlug,
            'scene_slug' => $sceneSlug,
            'pretest_completed' => $pretestCompleted,
            'pretest_passed' => $pretestPassed,
            'vr_status' => $vrStatus,
            'vr_completed' => $vrCompleted,
            'posttest_completed' => $posttestCompleted,
            'posttest_passed' => $posttestPassed,
            'can_launch_vr' => $canLaunchVr,
            'eligible_to_launch' => $canLaunchVr,
            'can_take_posttest' => $canTakePosttest,
            'is_completed' => $isCompleted,
            'is_unlocked' => $sceneUnlocked,
            'next_action' => $nextAction,
            'locked_reason' => $lockedReason,

            // Compatibility fields for existing Flutter/WebXR clients.
            'scene_unlocked' => $sceneUnlocked,
            'access_mode' => $instructorMode ? 'instructor' : 'student',
            'user_role' => $user->role,
            'previous_scene_slug' => $previousSceneSlug,
            'next_scene_slug' => $nextSceneSlug,
            'module' => [
                'id' => $module->id,
                'slug' => $moduleSlug,
                'title' => $module->title,
            ],
            'pre_test_completed' => $pretestCompleted,
            'pre_test_passed' => $pretestPassed,
            'post_test_completed' => $posttestCompleted,
            'post_test_passed' => $posttestPassed,
            'post_test_status' => $this->legacyPosttestStatus($posttestCompleted, $posttestPassed, $vrCompleted),
            'posttest_passed' => $posttestPassed,
            'legacy_next_action' => $moduleSlug === 'hygiene' && $nextSceneSlug === 'gowning' && $posttestPassed
                ? 'gowning_unlocked'
                : null,
            'quest3_paired' => $quest3Paired,
            'quest3_connected' => $quest3Connected,
            'checklist' => [
                ['label' => 'Modul tersedia', 'status' => true],
                ['label' => 'Scene terbuka', 'status' => $sceneUnlocked],
                ['label' => 'Pretest selesai', 'status' => $pretestCompleted],
                ['label' => 'Simulasi VR selesai', 'status' => $vrCompleted],
                ['label' => 'Posttest lulus', 'status' => $posttestPassed],
            ],
            'blocking_reasons' => $lockedReason ? [$lockedReason] : [],
            'recommended_next_action' => $this->recommendedActionText($nextAction),
            'recommended_next_route' => $this->recommendedRoute($moduleSlug, $nextAction),
        ];
    }

    public function forScene(User $user, string $sceneSlug): array
    {
        $canonicalSlug = Scene::resolveCanonicalSlug($sceneSlug);
        $module = TrainingModule::where('slug', $canonicalSlug)->firstOrFail();

        return $this->forModule($user, $module);
    }

    private function assessmentFor(TrainingModule $module, AssessmentType $type): ?Assessment
    {
        return $module->assessments()
            ->where('type', $type->value)
            ->where('status', 'active')
            ->first();
    }

    private function latestCompletedAttempt(int $userId, Assessment $assessment): ?AssessmentAttempt
    {
        return AssessmentAttempt::where('user_id', $userId)
            ->where('assessment_id', $assessment->id)
            ->whereNotNull('completed_at')
            ->orderByDesc('completed_at')
            ->first();
    }

    private function hasPassedAttempt(int $userId, Assessment $assessment): bool
    {
        return AssessmentAttempt::where('user_id', $userId)
            ->where('assessment_id', $assessment->id)
            ->where('passed', true)
            ->where('score', '>=', $assessment->passing_score)
            ->exists();
    }

    private function isSceneUnlocked(User $user, string $sceneSlug, ?string $previousSceneSlug): bool
    {
        if (!$this->productionPath->isProductionPathScene($sceneSlug)) {
            return true;
        }

        if ($sceneSlug === 'hygiene' || !$previousSceneSlug) {
            return true;
        }

        return $this->hasPassedPosttestForSlug($user->id, $previousSceneSlug);
    }

    private function hasPassedPosttestForSlug(int $userId, string $moduleSlug): bool
    {
        $module = TrainingModule::where('slug', $moduleSlug)->first();

        if (!$module) {
            return false;
        }

        $posttest = $this->assessmentFor($module, AssessmentType::POSTTEST);

        return $posttest ? $this->hasPassedAttempt($userId, $posttest) : false;
    }

    private function activeSession(User $user, TrainingModule $module, ?Scene $scene): ?VrSession
    {
        return VrSession::where('user_id', $user->id)
            ->whereIn('session_status', ['starting', 'playing'])
            ->when($scene, fn ($query) => $query->where('scene_id', $scene->id))
            ->when(!$scene, fn ($query) => $query->where('training_module_id', $module->id))
            ->orderByDesc('last_activity_at')
            ->first();
    }

    private function vrStatus(User $user, TrainingModule $module, ?Scene $scene): string
    {
        $latest = VrSession::where('user_id', $user->id)
            ->when($scene, fn ($query) => $query->where('scene_id', $scene->id))
            ->when(!$scene, fn ($query) => $query->where('training_module_id', $module->id))
            ->orderByDesc('last_activity_at')
            ->orderByDesc('id')
            ->first();

        if (!$latest) {
            $progress = UserTrainingProgress::where('user_id', $user->id)
                ->where('training_module_id', $module->id)
                ->first();

            return $progress?->vr_status === 'completed' ? 'completed' : 'not_started';
        }

        return match ($latest->session_status) {
            'starting', 'playing' => 'in_progress',
            'completed' => 'completed',
            'failed', 'interrupted' => 'failed',
            default => 'not_started',
        };
    }

    private function previousSceneSlugFromModel(?Scene $scene): ?string
    {
        if (!$scene?->required_previous_scene_id) {
            return null;
        }

        return Scene::whereKey($scene->required_previous_scene_id)->value('slug');
    }

    private function lockedReason(bool $sceneUnlocked, ?string $previousSceneSlug, bool $pretestCompleted, bool $vrCompleted): ?string
    {
        if (!$sceneUnlocked && $previousSceneSlug) {
            return 'previous_scene_posttest_required';
        }

        if (!$pretestCompleted) {
            return 'pretest_required';
        }

        if ($vrCompleted) {
            return null;
        }

        return null;
    }

    private function nextAction(
        bool $sceneUnlocked,
        bool $pretestCompleted,
        bool $hasActiveSession,
        bool $vrCompleted,
        bool $posttestCompleted,
        bool $posttestPassed,
        ?string $nextSceneSlug
    ): string {
        if (!$sceneUnlocked) {
            return 'locked';
        }

        if (!$pretestCompleted) {
            return 'take_pretest';
        }

        if ($hasActiveSession) {
            return 'continue_vr';
        }

        if (!$vrCompleted) {
            return 'launch_vr';
        }

        if (!$posttestCompleted || !$posttestPassed) {
            return $posttestCompleted ? 'retake_posttest' : 'take_posttest';
        }

        return 'completed';
    }

    private function legacyPosttestStatus(bool $posttestCompleted, bool $posttestPassed, bool $vrCompleted): string
    {
        if ($posttestPassed) {
            return 'passed';
        }

        if ($posttestCompleted) {
            return 'failed';
        }

        return $vrCompleted ? 'available' : 'locked';
    }

    private function recommendedActionText(string $nextAction): string
    {
        return match ($nextAction) {
            'take_pretest' => 'Kerjakan Pretest',
            'continue_vr' => 'Lanjutkan Sesi VR',
            'launch_vr' => 'Luncurkan Pelatihan VR',
            'take_posttest' => 'Kerjakan Posttest',
            'retake_posttest' => 'Ulangi Posttest',
            'completed' => 'Pembelajaran selesai',
            default => 'Scene terkunci',
        };
    }

    private function recommendedRoute(string $moduleSlug, string $nextAction): ?string
    {
        return match ($nextAction) {
            'take_pretest' => '/assessments/' . $moduleSlug . '/pre_test',
            'launch_vr' => '/vr/launch/' . $moduleSlug,
            'continue_vr' => '/vr/sessions/current',
            'take_posttest', 'retake_posttest' => '/assessments/' . $moduleSlug . '/post_test',
            'completed' => '/progress',
            default => null,
        };
    }

    private function quest3State(User $user): array
    {
        $device = VrDevice::where('user_id', $user->id)
            ->where('status', 'active')
            ->orderByDesc('last_seen_at')
            ->first();

        if (!$device || !$device->last_seen_at) {
            return [(bool) $device, false];
        }

        return [true, $device->last_seen_at->diffInMinutes(now()) <= 10];
    }

    private function canUseInstructorMode(User $user): bool
    {
        $role = strtolower((string) $user->role);

        return in_array($role, ['admin', 'super_admin', 'instructor'], true)
            || (bool) $user->can_bypass_prerequisites;
    }
}
