<?php

namespace App\Http\Controllers\Admin\Api;

use App\Http\Controllers\Controller;
use App\Models\Cohort;
use App\Models\User;
use App\Services\AdminAuditLogService;
use App\Services\InstructorScopeService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class AdminCohortController extends Controller
{
    private const STATUSES = [
        Cohort::STATUS_ACTIVE,
        Cohort::STATUS_INACTIVE,
    ];

    private const MEMBER_ROLES = [
        Cohort::MEMBER_ROLE_STUDENT,
        Cohort::MEMBER_ROLE_INSTRUCTOR,
    ];

    public function __construct(private readonly InstructorScopeService $scope)
    {
    }

    public function index(Request $request): JsonResponse
    {
        $validator = Validator::make($request->query(), [
            'search' => ['nullable', 'string', 'max:120'],
            'status' => ['nullable', Rule::in(self::STATUSES)],
            'page' => ['nullable', 'integer', 'min:1'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:50'],
        ]);

        if ($validator->fails()) {
            return $this->validationError($validator->errors()->toArray());
        }

        $actor = $request->user();
        $filters = $validator->validated();
        $perPage = (int) ($filters['per_page'] ?? 15);

        $cohorts = Cohort::query()
            ->withCount(['students', 'instructors'])
            ->when(! $this->scope->canSeeGlobal($actor), function (Builder $query) use ($actor) {
                $cohortIds = $this->scope->assignedCohortIds($actor);

                $cohortIds->isEmpty()
                    ? $query->whereRaw('1 = 0')
                    : $query->whereIn('id', $cohortIds);
            })
            ->when($filters['search'] ?? null, function (Builder $query, string $search) {
                $query->where(function (Builder $nested) use ($search) {
                    $nested->where('name', 'like', "%{$search}%")
                        ->orWhere('code', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%");
                });
            })
            ->when($filters['status'] ?? null, fn (Builder $query, string $status) => $query->where('status', $status))
            ->orderBy('name')
            ->orderBy('id')
            ->paginate($perPage);

        return response()->json([
            'success' => true,
            'message' => 'Cohorts retrieved.',
            'data' => $cohorts->getCollection()
                ->map(fn (Cohort $cohort) => $this->cohortResource($cohort))
                ->values(),
            'meta' => [
                'current_page' => $cohorts->currentPage(),
                'per_page' => $cohorts->perPage(),
                'total' => $cohorts->total(),
                'last_page' => $cohorts->lastPage(),
            ],
            'errors' => null,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $actor = $request->user();

        if (! $this->canWrite($actor)) {
            return $this->forbidden('Only super_admin and admin users can manage cohorts.');
        }

        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:160'],
            'code' => ['nullable', 'string', 'max:80', 'unique:cohorts,code'],
            'description' => ['nullable', 'string', 'max:2000'],
            'status' => ['nullable', Rule::in(self::STATUSES)],
        ]);

        if ($validator->fails()) {
            return $this->validationError($validator->errors()->toArray());
        }

        $cohort = Cohort::create(array_merge($validator->validated(), [
            'status' => $validator->validated()['status'] ?? Cohort::STATUS_ACTIVE,
        ]));

        app(AdminAuditLogService::class)->record(
            $request,
            $actor,
            'cohort.created',
            'cohort',
            $cohort->id,
            $cohort->name,
            null,
            $cohort->only(['name', 'code', 'description', 'status'])
        );

        return response()->json([
            'success' => true,
            'message' => 'Cohort created.',
            'data' => $this->cohortResource($cohort->loadCount(['students', 'instructors'])),
            'meta' => (object) [],
            'errors' => null,
        ], 201);
    }

    public function show(Request $request, Cohort $cohort): JsonResponse
    {
        if (! $this->scope->canAccessCohort($request->user(), $cohort)) {
            return $this->forbidden('You cannot access this cohort.');
        }

        return response()->json([
            'success' => true,
            'message' => 'Cohort retrieved.',
            'data' => $this->cohortDetailResource($cohort),
            'meta' => (object) [],
            'errors' => null,
        ]);
    }

    public function update(Request $request, Cohort $cohort): JsonResponse
    {
        $actor = $request->user();

        if (! $this->canWrite($actor)) {
            return $this->forbidden('Only super_admin and admin users can manage cohorts.');
        }

        $validator = Validator::make($request->all(), [
            'name' => ['sometimes', 'required', 'string', 'max:160'],
            'code' => ['nullable', 'string', 'max:80', Rule::unique('cohorts', 'code')->ignore($cohort->id)],
            'description' => ['nullable', 'string', 'max:2000'],
            'status' => ['sometimes', 'required', Rule::in(self::STATUSES)],
        ]);

        if ($validator->fails()) {
            return $this->validationError($validator->errors()->toArray());
        }

        $before = $cohort->only(['name', 'code', 'description', 'status']);
        $cohort->fill($validator->validated())->save();

        app(AdminAuditLogService::class)->record(
            $request,
            $actor,
            'cohort.updated',
            'cohort',
            $cohort->id,
            $cohort->name,
            $before,
            $cohort->only(['name', 'code', 'description', 'status'])
        );

        return response()->json([
            'success' => true,
            'message' => 'Cohort updated.',
            'data' => $this->cohortDetailResource($cohort->fresh()),
            'meta' => (object) [],
            'errors' => null,
        ]);
    }

    public function members(Request $request, Cohort $cohort): JsonResponse
    {
        if (! $this->scope->canAccessCohort($request->user(), $cohort)) {
            return $this->forbidden('You cannot access this cohort.');
        }

        return response()->json([
            'success' => true,
            'message' => 'Cohort members retrieved.',
            'data' => $this->memberRows($cohort),
            'meta' => (object) [],
            'errors' => null,
        ]);
    }

    public function addMember(Request $request, Cohort $cohort): JsonResponse
    {
        $actor = $request->user();

        if (! $this->canWrite($actor)) {
            return $this->forbidden('Only super_admin and admin users can manage cohort members.');
        }

        $validator = Validator::make($request->all(), [
            'user_id' => ['required', 'integer', 'exists:users,id'],
            'role_in_cohort' => ['required', Rule::in(self::MEMBER_ROLES)],
        ]);

        if ($validator->fails()) {
            return $this->validationError($validator->errors()->toArray());
        }

        $data = $validator->validated();
        $user = User::findOrFail($data['user_id']);

        if ($data['role_in_cohort'] !== $user->role) {
            return $this->validationError([
                'role_in_cohort' => ['The cohort role must match the user account role.'],
            ]);
        }

        $cohort->members()->syncWithoutDetaching([
            $user->id => ['role_in_cohort' => $data['role_in_cohort']],
        ]);

        app(AdminAuditLogService::class)->record(
            $request,
            $actor,
            'cohort.member.added',
            'cohort',
            $cohort->id,
            $cohort->name,
            null,
            [
                'cohort_id' => $cohort->id,
                'user_id' => $user->id,
                'user_email' => $user->email,
                'role_in_cohort' => $data['role_in_cohort'],
            ]
        );

        return response()->json([
            'success' => true,
            'message' => 'Cohort member added.',
            'data' => $this->memberRows($cohort),
            'meta' => (object) [],
            'errors' => null,
        ]);
    }

    public function removeMember(Request $request, Cohort $cohort, User $user): JsonResponse
    {
        $actor = $request->user();

        if (! $this->canWrite($actor)) {
            return $this->forbidden('Only super_admin and admin users can manage cohort members.');
        }

        $cohort->members()->detach($user->id);

        app(AdminAuditLogService::class)->record(
            $request,
            $actor,
            'cohort.member.removed',
            'cohort',
            $cohort->id,
            $cohort->name,
            [
                'cohort_id' => $cohort->id,
                'user_id' => $user->id,
                'user_email' => $user->email,
            ],
            null
        );

        return response()->json([
            'success' => true,
            'message' => 'Cohort member removed.',
            'data' => $this->memberRows($cohort),
            'meta' => (object) [],
            'errors' => null,
        ]);
    }

    private function cohortResource(Cohort $cohort): array
    {
        return [
            'id' => $cohort->id,
            'name' => $cohort->name,
            'code' => $cohort->code,
            'description' => $cohort->description,
            'status' => $cohort->status,
            'student_count' => (int) ($cohort->students_count ?? $cohort->students()->count()),
            'instructor_count' => (int) ($cohort->instructors_count ?? $cohort->instructors()->count()),
            'created_at' => $cohort->created_at?->toISOString(),
            'updated_at' => $cohort->updated_at?->toISOString(),
        ];
    }

    private function cohortDetailResource(Cohort $cohort): array
    {
        return array_merge(
            $this->cohortResource($cohort->loadCount(['students', 'instructors'])),
            ['members' => $this->memberRows($cohort)]
        );
    }

    private function memberRows(Cohort $cohort): array
    {
        return $cohort->members()
            ->select(['users.id', 'users.name', 'users.email', 'users.role', 'users.status'])
            ->orderBy('users.role')
            ->orderBy('users.name')
            ->get()
            ->map(fn (User $user) => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
                'status' => $user->status,
                'role_in_cohort' => $user->pivot->role_in_cohort,
            ])
            ->values()
            ->all();
    }

    private function canWrite(?User $actor): bool
    {
        return $actor && in_array($actor->role, [User::ROLE_SUPER_ADMIN, User::ROLE_ADMIN], true);
    }

    private function forbidden(string $message): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $message,
            'data' => null,
            'meta' => null,
            'errors' => null,
        ], 403);
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
