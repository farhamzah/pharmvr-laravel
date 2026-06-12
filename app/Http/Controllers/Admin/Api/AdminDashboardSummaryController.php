<?php

namespace App\Http\Controllers\Admin\Api;

use App\Enums\AssessmentType;
use App\Http\Controllers\Controller;
use App\Models\AssessmentAttempt;
use App\Models\Certificate;
use App\Models\User;
use App\Models\UserTrainingProgress;
use App\Models\VrSession;
use App\Services\InstructorScopeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminDashboardSummaryController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $actor = $request->user();

        return response()->json([
            'success' => true,
            'message' => 'Admin dashboard summary retrieved.',
            'data' => [
                'metrics' => $this->metrics($actor),
                'latest_vr_sessions' => $this->latestVrSessions($actor),
                'latest_assessment_attempts' => $this->latestAssessmentAttempts($actor),
                'system_health' => $this->systemHealth(),
            ],
            'meta' => (object) [],
            'errors' => null,
        ]);
    }

    private function metrics(User $actor): array
    {
        $progressQuery = UserTrainingProgress::query();
        app(InstructorScopeService::class)->scopeStudentDataForActor($progressQuery, $actor);

        $totalProgress = (clone $progressQuery)->count();
        $averageCompletion = $totalProgress > 0
            ? (float) (clone $progressQuery)->avg('completion_percentage')
            : 0;

        $activeUsersQuery = User::where('status', User::STATUS_ACTIVE);
        app(InstructorScopeService::class)->scopeUsersForActor($activeUsersQuery, $actor);

        $activeVrSessionsQuery = VrSession::whereIn('session_status', ['starting', 'playing', 'in_progress'])
            ->where(function ($query) {
                $query->whereNull('last_activity_at')
                    ->orWhere('last_activity_at', '>=', now()->subMinutes(15));
            });
        app(InstructorScopeService::class)->scopeStudentDataForActor($activeVrSessionsQuery, $actor);

        $certificatesQuery = Certificate::where('status', 'issued');
        app(InstructorScopeService::class)->scopeStudentDataForActor($certificatesQuery, $actor);

        return [
            'active_users' => $activeUsersQuery->count(),
            'active_vr_sessions' => $activeVrSessionsQuery->count(),
            'completion_rate' => round($averageCompletion, 1),
            'average_pretest_score' => $this->averageAssessmentScore(AssessmentType::PRETEST->value, $actor),
            'average_posttest_score' => $this->averageAssessmentScore(AssessmentType::POSTTEST->value, $actor),
            'certificate_eligible' => $certificatesQuery->count(),
        ];
    }

    private function averageAssessmentScore(string $type, User $actor): float
    {
        $query = AssessmentAttempt::query()
            ->whereNotNull('score')
            ->whereHas('assessment', fn ($query) => $query->where('type', $type));

        app(InstructorScopeService::class)->scopeStudentDataForActor($query, $actor);

        $score = $query->avg('score');

        return round((float) ($score ?? 0), 1);
    }

    private function latestVrSessions(User $actor): array
    {
        $query = VrSession::with(['user:id,name,email', 'scene:id,slug,title', 'trainingModule:id,title,slug'])
            ->latest();

        app(InstructorScopeService::class)->scopeStudentDataForActor($query, $actor);

        return $query
            ->limit(5)
            ->get()
            ->map(fn (VrSession $session) => [
                'id' => $session->id,
                'user' => $session->user ? [
                    'id' => $session->user->id,
                    'name' => $session->user->name,
                    'email' => $session->user->email,
                ] : null,
                'scene' => $session->scene ? [
                    'id' => $session->scene->id,
                    'slug' => $session->scene->slug,
                    'title' => $session->scene->title,
                ] : null,
                'module' => $session->trainingModule ? [
                    'id' => $session->trainingModule->id,
                    'slug' => $session->trainingModule->slug,
                    'title' => $session->trainingModule->title,
                ] : null,
                'status' => $session->session_status,
                'platform' => $session->platform,
                'progress_percentage' => (int) ($session->progress_percentage ?? 0),
                'started_at' => $session->started_at?->toISOString(),
                'completed_at' => $session->completed_at?->toISOString(),
                'duration_seconds' => $session->duration_seconds,
            ])
            ->values()
            ->all();
    }

    private function latestAssessmentAttempts(User $actor): array
    {
        $query = AssessmentAttempt::with(['user:id,name,email', 'assessment:id,title,type,module_id'])
            ->latest();

        app(InstructorScopeService::class)->scopeStudentDataForActor($query, $actor);

        return $query
            ->limit(5)
            ->get()
            ->map(fn (AssessmentAttempt $attempt) => [
                'id' => $attempt->id,
                'user' => $attempt->user ? [
                    'id' => $attempt->user->id,
                    'name' => $attempt->user->name,
                    'email' => $attempt->user->email,
                ] : null,
                'assessment' => $attempt->assessment ? [
                    'id' => $attempt->assessment->id,
                    'title' => $attempt->assessment->title,
                    'type' => $attempt->assessment->type?->value ?? (string) $attempt->assessment->type,
                    'module_id' => $attempt->assessment->module_id,
                ] : null,
                'score' => $attempt->score,
                'passed' => (bool) $attempt->passed,
                'status' => $attempt->status,
                'started_at' => $attempt->started_at?->toISOString(),
                'completed_at' => $attempt->completed_at?->toISOString(),
            ])
            ->values()
            ->all();
    }

    private function systemHealth(): array
    {
        $database = 'ok';

        try {
            DB::connection()->getPdo();
        } catch (\Throwable) {
            $database = 'failed';
        }

        return [
            'api' => 'ok',
            'database' => $database,
            'queue' => 'unknown',
        ];
    }
}
