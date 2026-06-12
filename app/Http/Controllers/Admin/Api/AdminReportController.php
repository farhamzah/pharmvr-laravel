<?php

namespace App\Http\Controllers\Admin\Api;

use App\Enums\AssessmentType;
use App\Http\Controllers\Controller;
use App\Models\AssessmentAttempt;
use App\Models\Scene;
use App\Services\InstructorScopeService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AdminReportController extends Controller
{
    private const STATUSES = ['in_progress', 'completed', 'failed', 'abandoned'];
    private const TYPES = ['pretest', 'posttest'];
    private const CSV_HEADERS = [
        'user_id',
        'user_name',
        'user_email',
        'module_title',
        'scene_slug',
        'assessment_title',
        'assessment_type',
        'attempt_id',
        'score',
        'passing_score',
        'passed',
        'status',
        'started_at',
        'completed_at',
    ];

    public function learningOutcomes(Request $request): JsonResponse
    {
        $validator = $this->validateFilters($request);

        if ($validator->fails()) {
            return $this->validationError($validator->errors()->toArray());
        }

        $filters = $validator->validated();
        $query = $this->attemptQuery($filters, $request->user());
        $attempts = (clone $query)->get();
        $completedAttempts = $attempts->filter(fn (AssessmentAttempt $attempt) => $this->isCompletedAttempt($attempt));
        $passedAttempts = $completedAttempts->filter(fn (AssessmentAttempt $attempt) => (bool) $attempt->passed);
        $failedAttempts = $completedAttempts->filter(fn (AssessmentAttempt $attempt) => $attempt->status === 'failed' || ! (bool) $attempt->passed);

        return response()->json([
            'success' => true,
            'message' => 'Learning outcomes report retrieved.',
            'data' => [
                'summary' => [
                    'total_students' => $attempts->pluck('user_id')->filter()->unique()->count(),
                    'total_attempts' => $attempts->count(),
                    'completed_attempts' => $completedAttempts->count(),
                    'passed_attempts' => $passedAttempts->count(),
                    'failed_attempts' => $failedAttempts->count(),
                    'average_score' => $this->averageScore($attempts),
                    'average_pretest_score' => $this->averageScore($attempts->filter(fn (AssessmentAttempt $attempt) => $this->assessmentType($attempt) === AssessmentType::PRETEST->value)),
                    'average_posttest_score' => $this->averageScore($attempts->filter(fn (AssessmentAttempt $attempt) => $this->assessmentType($attempt) === AssessmentType::POSTTEST->value)),
                    'completion_rate' => $this->percentage($completedAttempts->count(), $attempts->count()),
                    'pass_rate' => $this->percentage($passedAttempts->count(), $completedAttempts->count()),
                ],
                'by_module' => $this->moduleBreakdown($attempts),
                'by_assessment_type' => $this->assessmentTypeBreakdown($attempts),
            ],
            'meta' => (object) [],
            'errors' => null,
        ]);
    }

    public function studentPerformance(Request $request): JsonResponse
    {
        $validator = $this->validateFilters($request);

        if ($validator->fails()) {
            return $this->validationError($validator->errors()->toArray());
        }

        $filters = $validator->validated();
        $perPage = (int) ($filters['per_page'] ?? 15);
        $attempts = $this->attemptQuery($filters, $request->user())
            ->orderByDesc('completed_at')
            ->orderByDesc('started_at')
            ->orderByDesc('id')
            ->paginate($perPage);

        return response()->json([
            'success' => true,
            'message' => 'Student performance report retrieved.',
            'data' => $attempts->getCollection()
                ->map(fn (AssessmentAttempt $attempt) => $this->studentPerformanceRow($attempt))
                ->values(),
            'meta' => [
                'current_page' => $attempts->currentPage(),
                'per_page' => $attempts->perPage(),
                'total' => $attempts->total(),
                'last_page' => $attempts->lastPage(),
            ],
            'errors' => null,
        ]);
    }

    public function exportStudentPerformance(Request $request): StreamedResponse|JsonResponse
    {
        $validator = $this->validateFilters($request, false);

        if ($validator->fails()) {
            return $this->validationError($validator->errors()->toArray());
        }

        $filters = $validator->validated();
        $filename = 'pharmvr-student-performance-' . now()->format('Ymd-His') . '.csv';

        $actor = $request->user();

        return response()->streamDownload(function () use ($filters, $actor) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, self::CSV_HEADERS);

            $this->attemptQuery($filters, $actor)
                ->orderByDesc('completed_at')
                ->orderByDesc('started_at')
                ->orderByDesc('id')
                ->chunk(200, function ($attempts) use ($handle) {
                    foreach ($attempts as $attempt) {
                        $row = $this->studentPerformanceRow($attempt);
                        fputcsv($handle, [
                            $row['user_id'],
                            $row['user_name'],
                            $row['user_email'],
                            $row['module_title'],
                            $row['scene_slug'],
                            $row['assessment_title'],
                            $row['assessment_type'],
                            $row['attempt_id'],
                            $row['score'],
                            $row['passing_score'],
                            $row['passed'] ? 'true' : 'false',
                            $row['status'],
                            $row['started_at'],
                            $row['completed_at'],
                        ]);
                    }
                });

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Cache-Control' => 'no-store, no-cache',
        ]);
    }

    private function validateFilters(Request $request, bool $withPagination = true)
    {
        $rules = [
            'search' => ['nullable', 'string', 'max:120'],
            'user_id' => ['nullable', 'integer', 'exists:users,id'],
            'module_id' => ['nullable', 'integer', 'exists:training_modules,id'],
            'scene_id' => ['nullable', 'integer', 'exists:scenes,id'],
            'assessment_id' => ['nullable', 'integer', 'exists:assessments,id'],
            'type' => ['nullable', Rule::in(self::TYPES)],
            'status' => ['nullable', Rule::in(self::STATUSES)],
            'passed' => ['nullable', Rule::in(['true', 'false', '1', '0'])],
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date'],
        ];

        if ($withPagination) {
            $rules['page'] = ['nullable', 'integer', 'min:1'];
            $rules['per_page'] = ['nullable', 'integer', 'min:1', 'max:100'];
        }

        return Validator::make($request->query(), $rules);
    }

    private function attemptQuery(array $filters = [], $actor = null): Builder
    {
        $query = AssessmentAttempt::query()
            ->with(['user:id,name,email', 'assessment.trainingModule.scenes'])
            ->when($filters['search'] ?? null, function (Builder $query, string $search) {
                $query->where(function (Builder $nested) use ($search) {
                    $nested->whereHas('user', fn (Builder $userQuery) => $userQuery
                        ->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%"))
                        ->orWhereHas('assessment', fn (Builder $assessmentQuery) => $assessmentQuery
                            ->where('title', 'like', "%{$search}%")
                            ->orWhereHas('trainingModule', fn (Builder $moduleQuery) => $moduleQuery
                                ->where('title', 'like', "%{$search}%")
                                ->orWhere('slug', 'like', "%{$search}%")));
                });
            })
            ->when($filters['user_id'] ?? null, fn (Builder $query, $userId) => $query->where('user_id', $userId))
            ->when($filters['assessment_id'] ?? null, fn (Builder $query, $assessmentId) => $query->where('assessment_id', $assessmentId))
            ->when($filters['module_id'] ?? null, function (Builder $query, $moduleId) {
                $query->whereHas('assessment', fn (Builder $assessmentQuery) => $assessmentQuery->where('module_id', $moduleId));
            })
            ->when($filters['scene_id'] ?? null, function (Builder $query, $sceneId) {
                $moduleId = Scene::whereKey($sceneId)->value('training_module_id');
                $query->whereHas('assessment', fn (Builder $assessmentQuery) => $assessmentQuery->where('module_id', $moduleId));
            })
            ->when($filters['type'] ?? null, function (Builder $query, string $type) {
                $query->whereHas('assessment', fn (Builder $assessmentQuery) => $assessmentQuery->where('type', $type));
            })
            ->when($filters['status'] ?? null, fn (Builder $query, string $status) => $query->where('status', $status))
            ->when(array_key_exists('passed', $filters), function (Builder $query) use ($filters) {
                $query->where('passed', in_array((string) $filters['passed'], ['true', '1'], true));
            })
            ->when($filters['date_from'] ?? null, fn (Builder $query, string $date) => $query->whereDate('started_at', '>=', $date))
            ->when($filters['date_to'] ?? null, fn (Builder $query, string $date) => $query->whereDate('started_at', '<=', $date));

        if ($actor) {
            app(InstructorScopeService::class)->scopeStudentDataForActor($query, $actor);
        }

        return $query;
    }

    private function studentPerformanceRow(AssessmentAttempt $attempt): array
    {
        $assessment = $attempt->assessment;
        $module = $assessment?->trainingModule;
        $scene = $this->sceneForAttempt($attempt);

        return [
            'user_id' => $attempt->user_id,
            'user_name' => $attempt->user?->name,
            'user_email' => $attempt->user?->email,
            'module_id' => $module?->id,
            'module_title' => $module?->title,
            'scene_slug' => $scene?->slug,
            'assessment_title' => $assessment?->title,
            'assessment_type' => $this->assessmentType($attempt),
            'attempt_id' => $attempt->id,
            'score' => $attempt->score,
            'passing_score' => $assessment?->passing_score,
            'passed' => (bool) $attempt->passed,
            'status' => $attempt->status,
            'started_at' => $attempt->started_at?->toISOString(),
            'completed_at' => $attempt->completed_at?->toISOString(),
        ];
    }

    private function moduleBreakdown($attempts): array
    {
        return $attempts
            ->groupBy(fn (AssessmentAttempt $attempt) => $attempt->assessment?->trainingModule?->id ?? 'none')
            ->map(function ($moduleAttempts) {
                /** @var AssessmentAttempt $first */
                $first = $moduleAttempts->first();
                $module = $first?->assessment?->trainingModule;
                $scene = $first ? $this->sceneForAttempt($first) : null;
                $completedAttempts = $moduleAttempts->filter(fn (AssessmentAttempt $attempt) => $this->isCompletedAttempt($attempt));
                $passedAttempts = $completedAttempts->filter(fn (AssessmentAttempt $attempt) => (bool) $attempt->passed);

                return [
                    'module_id' => $module?->id,
                    'module_title' => $module?->title,
                    'scene_slug' => $scene?->slug,
                    'attempts' => $moduleAttempts->count(),
                    'average_score' => $this->averageScore($moduleAttempts),
                    'pass_rate' => $this->percentage($passedAttempts->count(), $completedAttempts->count()),
                ];
            })
            ->values()
            ->all();
    }

    private function assessmentTypeBreakdown($attempts): array
    {
        return $attempts
            ->groupBy(fn (AssessmentAttempt $attempt) => $this->assessmentType($attempt) ?? 'unknown')
            ->map(function ($typeAttempts, string $type) {
                $completedAttempts = $typeAttempts->filter(fn (AssessmentAttempt $attempt) => $this->isCompletedAttempt($attempt));
                $passedAttempts = $completedAttempts->filter(fn (AssessmentAttempt $attempt) => (bool) $attempt->passed);

                return [
                    'type' => $type,
                    'attempts' => $typeAttempts->count(),
                    'average_score' => $this->averageScore($typeAttempts),
                    'pass_rate' => $this->percentage($passedAttempts->count(), $completedAttempts->count()),
                ];
            })
            ->values()
            ->all();
    }

    private function sceneForAttempt(AssessmentAttempt $attempt): ?Scene
    {
        $module = $attempt->assessment?->trainingModule;

        if (! $module) {
            return null;
        }

        return $module->relationLoaded('scenes')
            ? $module->scenes->sortBy('order_index')->first()
            : $module->scenes()->first();
    }

    private function assessmentType(AssessmentAttempt $attempt): ?string
    {
        $type = $attempt->assessment?->type;

        return $type instanceof AssessmentType ? $type->value : ($type ? (string) $type : null);
    }

    private function isCompletedAttempt(AssessmentAttempt $attempt): bool
    {
        return $attempt->completed_at !== null || in_array($attempt->status, ['completed', 'failed'], true);
    }

    private function averageScore($attempts): float
    {
        $scores = $attempts->pluck('score')->filter(fn ($score) => $score !== null);

        if ($scores->isEmpty()) {
            return 0;
        }

        return round((float) $scores->avg(), 1);
    }

    private function percentage(int $numerator, int $denominator): float
    {
        if ($denominator <= 0) {
            return 0;
        }

        return round(($numerator / $denominator) * 100, 1);
    }

    private function validationError(array $errors): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => 'Validation failed.',
            'data' => null,
            'meta' => null,
            'errors' => $errors,
        ], 422);
    }
}
