<?php

namespace App\Http\Controllers\Admin\Api;

use App\Enums\AssessmentType;
use App\Http\Controllers\Controller;
use App\Models\Assessment;
use App\Models\AssessmentAttempt;
use App\Models\Certificate;
use App\Models\Scene;
use App\Models\TrainingModule;
use App\Models\User;
use App\Models\UserTrainingProgress;
use App\Models\VrSession;
use App\Services\LearningReadinessService;
use App\Services\ProductionPathService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class AdminCompletionCertificateController extends Controller
{
    private const CERTIFICATE_TYPE = 'production_path_completed';
    private const COMPLETION_STATUSES = ['completed', 'incomplete'];

    public function __construct(
        private readonly LearningReadinessService $readiness,
        private readonly ProductionPathService $productionPath
    ) {
    }

    public function completions(Request $request): JsonResponse
    {
        $validator = $this->validateFilters($request);

        if ($validator->fails()) {
            return $this->validationError($validator->errors()->toArray());
        }

        $filters = $validator->validated();
        $requiredModules = $this->requiredModules();
        $rows = $this->studentQuery($filters)
            ->get()
            ->map(fn (User $user) => $this->completionSummaryRow($user, $requiredModules));
        $rows = $this->applyComputedFilters($rows, $filters);
        $rows = $this->stripInternalRows($rows);
        $paginator = $this->paginateRows($rows, (int) ($filters['page'] ?? 1), (int) ($filters['per_page'] ?? 15));

        return response()->json([
            'success' => true,
            'message' => 'Completions retrieved.',
            'data' => $paginator->items(),
            'meta' => $this->paginationMeta($paginator),
            'errors' => null,
        ]);
    }

    public function completionDetail(Request $request, User $user): JsonResponse
    {
        if (! $user->isStudent()) {
            return response()->json([
                'success' => false,
                'message' => 'Completion detail is only available for student users.',
                'data' => null,
                'meta' => null,
                'errors' => null,
            ], 404);
        }

        $requiredModules = $this->requiredModules();
        $summary = $this->completionSummaryRow($user, $requiredModules);
        $certificate = $this->certificateFor($user->id);

        return response()->json([
            'success' => true,
            'message' => 'Completion detail retrieved.',
            'data' => [
                'user' => $this->userPayload($user),
                'summary' => [
                    'completed_modules' => $summary['completed_modules'],
                    'required_modules' => $summary['required_modules'],
                    'completed_scenes' => $summary['completed_scenes'],
                    'required_scenes' => $summary['required_scenes'],
                    'completion_rate' => $summary['completion_rate'],
                    'certificate_eligible' => $summary['certificate_eligible'],
                    'certificate_issued' => $summary['certificate_issued'],
                    'last_activity_at' => $summary['last_activity_at'],
                ],
                'modules' => $requiredModules
                    ->map(fn (TrainingModule $module) => $this->moduleCompletionRow($user, $module))
                    ->values()
                    ->all(),
                'certificate' => $this->certificatePayload($certificate),
            ],
            'meta' => (object) [],
            'errors' => null,
        ]);
    }

    public function certificates(Request $request): JsonResponse
    {
        $validator = $this->validateFilters($request);

        if ($validator->fails()) {
            return $this->validationError($validator->errors()->toArray());
        }

        $filters = $validator->validated();
        $perPage = (int) ($filters['per_page'] ?? 15);
        $certificates = Certificate::query()
            ->with('user:id,name,email,role,status')
            ->when($filters['search'] ?? null, function (Builder $query, string $search) {
                $query->where(function (Builder $nested) use ($search) {
                    $nested->where('certificate_id', 'like', "%{$search}%")
                        ->orWhere('title', 'like', "%{$search}%")
                        ->orWhereHas('user', fn (Builder $userQuery) => $userQuery
                            ->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%"));
                });
            })
            ->when($filters['user_id'] ?? null, fn (Builder $query, $userId) => $query->where('user_id', $userId))
            ->when($filters['status'] ?? null, fn (Builder $query, string $status) => $query->where('status', $status))
            ->when(array_key_exists('issued', $filters), function (Builder $query) use ($filters) {
                $isIssued = $this->boolFilter($filters['issued']);
                $isIssued
                    ? $query->where('status', 'issued')->whereNotNull('issued_at')
                    : $query->where(fn (Builder $nested) => $nested->where('status', '!=', 'issued')->orWhereNull('issued_at'));
            })
            ->when($filters['date_from'] ?? null, fn (Builder $query, string $date) => $query->whereDate('issued_at', '>=', $date))
            ->when($filters['date_to'] ?? null, fn (Builder $query, string $date) => $query->whereDate('issued_at', '<=', $date))
            ->orderByDesc('issued_at')
            ->orderByDesc('id')
            ->paginate($perPage);

        return response()->json([
            'success' => true,
            'message' => 'Certificates retrieved.',
            'data' => $certificates->getCollection()
                ->map(fn (Certificate $certificate) => $this->certificateListRow($certificate))
                ->values(),
            'meta' => $this->paginationMeta($certificates),
            'errors' => null,
        ]);
    }

    public function certificateEligibility(Request $request): JsonResponse
    {
        $validator = $this->validateFilters($request);

        if ($validator->fails()) {
            return $this->validationError($validator->errors()->toArray());
        }

        $filters = $validator->validated();
        $requiredModules = $this->requiredModules();
        $rows = $this->studentQuery($filters)
            ->get()
            ->map(fn (User $user) => $this->eligibilityRow($user, $requiredModules));
        $rows = $this->applyComputedFilters($rows, $filters);
        $rows = $this->stripInternalRows($rows);
        $paginator = $this->paginateRows($rows, (int) ($filters['page'] ?? 1), (int) ($filters['per_page'] ?? 15));

        return response()->json([
            'success' => true,
            'message' => 'Certificate eligibility retrieved.',
            'data' => $paginator->items(),
            'meta' => $this->paginationMeta($paginator),
            'errors' => null,
        ]);
    }

    private function validateFilters(Request $request)
    {
        return Validator::make($request->query(), [
            'search' => ['nullable', 'string', 'max:120'],
            'user_id' => ['nullable', 'integer', 'exists:users,id'],
            'module_id' => ['nullable', 'integer', 'exists:training_modules,id'],
            'scene_id' => ['nullable', 'integer', 'exists:scenes,id'],
            'status' => ['nullable', Rule::in(self::COMPLETION_STATUSES)],
            'eligible' => ['nullable', Rule::in(['true', 'false', '1', '0'])],
            'issued' => ['nullable', Rule::in(['true', 'false', '1', '0'])],
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date'],
            'page' => ['nullable', 'integer', 'min:1'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);
    }

    private function studentQuery(array $filters): Builder
    {
        return User::query()
            ->where('role', User::ROLE_STUDENT)
            ->select(['id', 'name', 'email', 'role', 'status', 'created_at', 'updated_at'])
            ->when($filters['search'] ?? null, fn (Builder $query, string $search) => $query
                ->where(fn (Builder $nested) => $nested
                    ->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")))
            ->when($filters['user_id'] ?? null, fn (Builder $query, $userId) => $query->whereKey($userId))
            ->orderBy('name')
            ->orderBy('id');
    }

    private function requiredModules(): Collection
    {
        $slugs = $this->productionPath->slugs();
        $modules = TrainingModule::query()
            ->with(['scenes' => fn ($query) => $query->where('is_active', true)->orderBy('order_index'), 'assessments'])
            ->where('is_active', true)
            ->whereIn('slug', $slugs)
            ->get()
            ->sortBy(fn (TrainingModule $module) => array_search($module->slug, $slugs, true))
            ->values();

        if ($modules->isNotEmpty()) {
            return $modules;
        }

        return TrainingModule::query()
            ->with(['scenes' => fn ($query) => $query->where('is_active', true)->orderBy('order_index'), 'assessments'])
            ->where('is_active', true)
            ->orderBy('id')
            ->get();
    }

    private function completionSummaryRow(User $user, Collection $requiredModules): array
    {
        $moduleRows = $requiredModules->map(fn (TrainingModule $module) => $this->moduleCompletionRow($user, $module));
        $completedModules = $moduleRows->where('is_completed', true)->count();
        $requiredModulesCount = $requiredModules->count();
        $requiredScenes = $moduleRows->filter(fn (array $row) => $row['scene_id'] !== null)->count();
        $completedScenes = $moduleRows
            ->filter(fn (array $row) => $row['scene_id'] !== null && $row['is_completed'])
            ->count();
        $certificate = $this->certificateFor($user->id);
        $eligible = $requiredModulesCount > 0 && $completedModules === $requiredModulesCount;

        return [
            'user_id' => $user->id,
            'user_name' => $user->name,
            'user_email' => $user->email,
            'completed_modules' => $completedModules,
            'required_modules' => $requiredModulesCount,
            'completed_scenes' => $completedScenes,
            'required_scenes' => $requiredScenes,
            'completion_rate' => $this->percentage($completedModules, $requiredModulesCount),
            'certificate_eligible' => $eligible,
            'certificate_issued' => $this->isIssued($certificate),
            'certificate_id' => $certificate?->id,
            'certificate_number' => $certificate?->certificate_id,
            'last_activity_at' => $moduleRows->pluck('last_activity_at')->filter()->sortDesc()->first(),
            '_module_rows' => $moduleRows->values()->all(),
        ];
    }

    private function eligibilityRow(User $user, Collection $requiredModules): array
    {
        $summary = $this->completionSummaryRow($user, $requiredModules);
        $moduleRows = $requiredModules->map(fn (TrainingModule $module) => $this->moduleCompletionRow($user, $module));

        return [
            'user_id' => $summary['user_id'],
            'user_name' => $summary['user_name'],
            'user_email' => $summary['user_email'],
            'eligible' => $summary['certificate_eligible'],
            'completed_required_modules' => $summary['completed_modules'],
            'total_required_modules' => $summary['required_modules'],
            'missing_modules' => $moduleRows
                ->filter(fn (array $row) => ! $row['is_completed'])
                ->map(fn (array $row) => $row['module_slug'])
                ->values()
                ->all(),
            'certificate_issued' => $summary['certificate_issued'],
            'certificate_id' => $summary['certificate_id'],
            'certificate_number' => $summary['certificate_number'],
            'last_activity_at' => $summary['last_activity_at'],
            '_module_rows' => $moduleRows->values()->all(),
        ];
    }

    private function moduleCompletionRow(User $user, TrainingModule $module): array
    {
        $readiness = $this->readiness->forModule($user, $module);
        $scene = $module->relationLoaded('scenes') ? $module->scenes->first() : $module->scenes()->where('is_active', true)->first();
        $pretest = $this->assessmentFor($module, AssessmentType::PRETEST);
        $posttest = $this->assessmentFor($module, AssessmentType::POSTTEST);
        $lastAttempt = $this->latestAttemptForModule($user->id, $module);
        $lastSession = $this->latestSessionForModule($user->id, $module, $scene);
        $progress = UserTrainingProgress::where('user_id', $user->id)
            ->where('training_module_id', $module->id)
            ->first();
        $pretestOk = $pretest ? (bool) $readiness['pretest_passed'] : true;
        $posttestOk = $posttest ? (bool) $readiness['posttest_passed'] : true;
        $vrCompleted = (bool) $readiness['vr_completed'];
        $isCompleted = $pretestOk && $vrCompleted && $posttestOk;
        $lastActivityAt = collect([
            $lastAttempt?->completed_at?->toISOString(),
            $lastAttempt?->started_at?->toISOString(),
            $lastSession?->completed_at?->toISOString(),
            $lastSession?->last_activity_at?->toISOString(),
            $progress?->last_accessed_at?->toISOString(),
            $progress?->updated_at?->toISOString(),
        ])->filter()->sortDesc()->first();

        return [
            'module_id' => $module->id,
            'module_title' => $module->title,
            'module_slug' => $module->slug,
            'scene_id' => $scene?->id,
            'scene_slug' => $scene?->slug,
            'pretest_completed' => (bool) $readiness['pretest_completed'],
            'pretest_passed' => (bool) $readiness['pretest_passed'],
            'vr_status' => $readiness['vr_status'],
            'vr_completed' => $vrCompleted,
            'posttest_completed' => (bool) $readiness['posttest_completed'],
            'posttest_passed' => (bool) $readiness['posttest_passed'],
            'is_completed' => $isCompleted,
            'last_score' => $lastAttempt?->score,
            'last_activity_at' => $lastActivityAt,
        ];
    }

    private function applyComputedFilters(Collection $rows, array $filters): Collection
    {
        return $rows
            ->when($filters['status'] ?? null, fn (Collection $items, string $status) => $items
                ->filter(fn (array $row) => $status === 'completed'
                    ? (bool) ($row['certificate_eligible'] ?? false)
                    : ! (bool) ($row['certificate_eligible'] ?? false)))
            ->when(array_key_exists('eligible', $filters), fn (Collection $items) => $items
                ->filter(fn (array $row) => (bool) ($row['certificate_eligible'] ?? $row['eligible'] ?? false) === $this->boolFilter($filters['eligible'])))
            ->when(array_key_exists('issued', $filters), fn (Collection $items) => $items
                ->filter(fn (array $row) => (bool) ($row['certificate_issued'] ?? false) === $this->boolFilter($filters['issued'])))
            ->when($filters['module_id'] ?? null, function (Collection $items, $moduleId) {
                return $items->filter(fn (array $row) => collect($row['_module_rows'] ?? [])
                    ->contains(fn (array $moduleRow) => (int) $moduleRow['module_id'] === (int) $moduleId));
            })
            ->when($filters['scene_id'] ?? null, function (Collection $items, $sceneId) {
                return $items->filter(fn (array $row) => collect($row['_module_rows'] ?? [])
                    ->contains(fn (array $moduleRow) => (int) ($moduleRow['scene_id'] ?? 0) === (int) $sceneId));
            })
            ->when($filters['date_from'] ?? null, fn (Collection $items, string $date) => $items
                ->filter(fn (array $row) => ($row['last_activity_at'] ?? null) && substr($row['last_activity_at'], 0, 10) >= $date))
            ->when($filters['date_to'] ?? null, fn (Collection $items, string $date) => $items
                ->filter(fn (array $row) => ($row['last_activity_at'] ?? null) && substr($row['last_activity_at'], 0, 10) <= $date))
            ->values();
    }

    private function stripInternalRows(Collection $rows): Collection
    {
        return $rows->map(function (array $row) {
            unset($row['_module_rows']);

            return $row;
        });
    }

    private function paginateRows(Collection $rows, int $page, int $perPage): LengthAwarePaginator
    {
        $page = max(1, $page);
        $perPage = max(1, min(100, $perPage));

        return new LengthAwarePaginator(
            $rows->forPage($page, $perPage)->values()->all(),
            $rows->count(),
            $perPage,
            $page,
            ['path' => request()->url(), 'query' => request()->query()]
        );
    }

    private function paginationMeta(LengthAwarePaginator $paginator): array
    {
        return [
            'current_page' => $paginator->currentPage(),
            'per_page' => $paginator->perPage(),
            'total' => $paginator->total(),
            'last_page' => $paginator->lastPage(),
        ];
    }

    private function assessmentFor(TrainingModule $module, AssessmentType $type): ?Assessment
    {
        $assessment = $module->relationLoaded('assessments')
            ? $module->assessments->first(fn (Assessment $assessment) => $this->assessmentType($assessment) === $type->value && $this->assessmentStatus($assessment) === 'active')
            : null;

        return $assessment ?: $module->assessments()
            ->where('type', $type->value)
            ->where('status', 'active')
            ->first();
    }

    private function latestAttemptForModule(int $userId, TrainingModule $module): ?AssessmentAttempt
    {
        return AssessmentAttempt::query()
            ->where('user_id', $userId)
            ->whereHas('assessment', fn (Builder $query) => $query->where('module_id', $module->id))
            ->orderByDesc('completed_at')
            ->orderByDesc('started_at')
            ->orderByDesc('id')
            ->first();
    }

    private function latestSessionForModule(int $userId, TrainingModule $module, ?Scene $scene): ?VrSession
    {
        return VrSession::query()
            ->where('user_id', $userId)
            ->when($scene, fn (Builder $query) => $query->where('scene_id', $scene->id))
            ->when(! $scene, fn (Builder $query) => $query->where('training_module_id', $module->id))
            ->orderByDesc('last_activity_at')
            ->orderByDesc('completed_at')
            ->orderByDesc('id')
            ->first();
    }

    private function certificateFor(int $userId): ?Certificate
    {
        return Certificate::where('user_id', $userId)
            ->where('certificate_type', self::CERTIFICATE_TYPE)
            ->orderByDesc('issued_at')
            ->orderByDesc('id')
            ->first();
    }

    private function certificatePayload(?Certificate $certificate): array
    {
        return [
            'id' => $certificate?->id,
            'number' => $certificate?->certificate_id,
            'status' => $certificate?->status,
            'issued_at' => $certificate?->issued_at?->toISOString(),
            'download_url' => null,
        ];
    }

    private function certificateListRow(Certificate $certificate): array
    {
        return [
            'id' => $certificate->id,
            'number' => $certificate->certificate_id,
            'title' => $certificate->title,
            'type' => $certificate->certificate_type,
            'status' => $certificate->status,
            'issued_at' => $certificate->issued_at?->toISOString(),
            'user' => $certificate->user ? $this->userPayload($certificate->user) : null,
            'download_url' => null,
        ];
    }

    private function userPayload(User $user): array
    {
        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
        ];
    }

    private function assessmentType(Assessment $assessment): ?string
    {
        $type = $assessment->type;

        return $type instanceof AssessmentType ? $type->value : ($type ? (string) $type : null);
    }

    private function assessmentStatus(Assessment $assessment): ?string
    {
        $status = $assessment->status;

        return $status instanceof \BackedEnum ? $status->value : ($status ? (string) $status : null);
    }

    private function isIssued(?Certificate $certificate): bool
    {
        return $certificate?->status === 'issued' && $certificate->issued_at !== null;
    }

    private function boolFilter(mixed $value): bool
    {
        return in_array((string) $value, ['true', '1'], true);
    }

    private function percentage(int $numerator, int $denominator): float
    {
        if ($denominator <= 0) {
            return 0;
        }

        return round(($numerator / $denominator) * 100, 2);
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
