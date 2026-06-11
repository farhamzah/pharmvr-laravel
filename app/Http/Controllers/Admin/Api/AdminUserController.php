<?php

namespace App\Http\Controllers\Admin\Api;

use App\Enums\AssessmentType;
use App\Http\Controllers\Controller;
use App\Models\AssessmentAttempt;
use App\Models\User;
use App\Models\UserTrainingProgress;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class AdminUserController extends Controller
{
    private const ROLES = [
        User::ROLE_STUDENT,
        User::ROLE_INSTRUCTOR,
        User::ROLE_ADMIN,
        User::ROLE_SUPER_ADMIN,
    ];

    private const STATUSES = [
        User::STATUS_ACTIVE,
        User::STATUS_PENDING,
        User::STATUS_SUSPENDED,
        User::STATUS_INACTIVE,
    ];

    public function index(Request $request): JsonResponse
    {
        $validator = Validator::make($request->query(), [
            'search' => ['nullable', 'string', 'max:120'],
            'role' => ['nullable', Rule::in(self::ROLES)],
            'status' => ['nullable', Rule::in(self::STATUSES)],
            'page' => ['nullable', 'integer', 'min:1'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:50'],
        ]);

        if ($validator->fails()) {
            return $this->validationError($validator->errors()->toArray());
        }

        $filters = $validator->validated();
        $perPage = (int) ($filters['per_page'] ?? 15);

        $users = User::query()
            ->select(['id', 'name', 'email', 'role', 'status', 'created_at', 'updated_at'])
            ->when($filters['search'] ?? null, function ($query, string $search) {
                $query->where(function ($nested) use ($search) {
                    $nested->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->when($filters['role'] ?? null, fn ($query, string $role) => $query->where('role', $role))
            ->when($filters['status'] ?? null, fn ($query, string $status) => $query->where('status', $status))
            ->orderByDesc('created_at')
            ->orderBy('name')
            ->paginate($perPage);

        return response()->json([
            'success' => true,
            'message' => 'Users retrieved.',
            'data' => $users->getCollection()
                ->map(fn (User $user) => $this->userListResource($user))
                ->values(),
            'meta' => [
                'current_page' => $users->currentPage(),
                'per_page' => $users->perPage(),
                'total' => $users->total(),
                'last_page' => $users->lastPage(),
            ],
            'errors' => null,
        ]);
    }

    public function show(User $user): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => 'User retrieved.',
            'data' => array_merge($this->userDetailResource($user), [
                'progress_summary' => $this->progressSummary($user),
            ]),
            'meta' => (object) [],
            'errors' => null,
        ]);
    }

    public function updateRole(Request $request, User $user): JsonResponse
    {
        $actor = $request->user();

        if (!$actor || !in_array($actor->role, [User::ROLE_SUPER_ADMIN, User::ROLE_ADMIN], true)) {
            return $this->forbidden('Only super_admin and admin users can update roles.');
        }

        if ($actor->role === User::ROLE_ADMIN && $user->role === User::ROLE_SUPER_ADMIN) {
            return $this->forbidden('Admin users cannot change a super_admin role.');
        }

        $validator = Validator::make($request->all(), [
            'role' => ['required', Rule::in(self::ROLES)],
        ]);

        if ($validator->fails()) {
            return $this->validationError($validator->errors()->toArray());
        }

        $newRole = $validator->validated()['role'];

        if ($actor->role === User::ROLE_ADMIN && $newRole === User::ROLE_SUPER_ADMIN) {
            return $this->forbidden('Only super_admin users can assign the super_admin role.');
        }

        if ($user->role === User::ROLE_SUPER_ADMIN && $newRole !== User::ROLE_SUPER_ADMIN) {
            $superAdminCount = User::where('role', User::ROLE_SUPER_ADMIN)->count();

            if ($superAdminCount <= 1) {
                return $this->validationError([
                    'role' => ['Cannot demote the only super_admin user.'],
                ]);
            }
        }

        $user->forceFill(['role' => $newRole])->save();

        return response()->json([
            'success' => true,
            'message' => 'User role updated.',
            'data' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
            ],
            'meta' => (object) [],
            'errors' => null,
        ]);
    }

    private function userListResource(User $user): array
    {
        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->role,
            'status' => $user->status,
            'created_at' => $user->created_at?->toISOString(),
            'last_login_at' => null,
        ];
    }

    private function userDetailResource(User $user): array
    {
        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->role,
            'status' => $user->status,
            'created_at' => $user->created_at?->toISOString(),
            'updated_at' => $user->updated_at?->toISOString(),
            'last_login_at' => null,
        ];
    }

    private function progressSummary(User $user): array
    {
        $completedModules = UserTrainingProgress::query()
            ->where('user_id', $user->id)
            ->where(function ($query) {
                $query->where('status', 'completed')
                    ->orWhere('completion_percentage', '>=', 100);
            })
            ->count();

        $completedScenes = UserTrainingProgress::query()
            ->where('user_id', $user->id)
            ->where('vr_status', 'completed')
            ->count();

        $averagePosttestScore = AssessmentAttempt::query()
            ->where('user_id', $user->id)
            ->whereNotNull('score')
            ->whereHas('assessment', fn ($query) => $query->where('type', AssessmentType::POSTTEST->value))
            ->avg('score');

        return [
            'completed_modules' => $completedModules,
            'completed_scenes' => $completedScenes,
            'average_posttest_score' => round((float) ($averagePosttestScore ?? 0), 1),
        ];
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
