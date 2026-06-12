<?php

namespace App\Http\Controllers\Admin\Api;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\User;
use App\Services\AdminAuditLogService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;

class AdminAuditLogController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        if (!$this->canRead($request)) {
            return $this->forbidden('Only super_admin and admin users can view audit logs.');
        }

        $validator = Validator::make($request->query(), [
            'search' => ['nullable', 'string', 'max:120'],
            'actor_id' => ['nullable', 'integer', 'exists:users,id'],
            'action' => ['nullable', 'string', 'max:120'],
            'target_type' => ['nullable', 'string', 'max:120'],
            'target_id' => ['nullable', 'integer', 'min:1'],
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date'],
            'page' => ['nullable', 'integer', 'min:1'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:50'],
        ]);

        if ($validator->fails()) {
            return $this->validationError($validator->errors()->toArray());
        }

        $filters = $validator->validated();
        $perPage = (int) ($filters['per_page'] ?? 15);

        $logs = AuditLog::query()
            ->with('user:id,name,email,role')
            ->when($filters['search'] ?? null, function ($query, string $search) {
                $query->where(function ($nested) use ($search) {
                    $nested->where('action', 'like', "%{$search}%")
                        ->orWhere('model_type', 'like', "%{$search}%")
                        ->orWhere('model_id', 'like', "%{$search}%")
                        ->orWhereHas('user', function ($userQuery) use ($search) {
                            $userQuery->where('name', 'like', "%{$search}%")
                                ->orWhere('email', 'like', "%{$search}%");
                        });
                });
            })
            ->when($filters['actor_id'] ?? null, fn ($query, int $actorId) => $query->where('user_id', $actorId))
            ->when($filters['action'] ?? null, fn ($query, string $action) => $query->where('action', $action))
            ->when($filters['target_type'] ?? null, fn ($query, string $targetType) => $query->where('model_type', $targetType))
            ->when($filters['target_id'] ?? null, fn ($query, int $targetId) => $query->where('model_id', $targetId))
            ->when($filters['date_from'] ?? null, fn ($query, string $dateFrom) => $query->whereDate('created_at', '>=', $dateFrom))
            ->when($filters['date_to'] ?? null, fn ($query, string $dateTo) => $query->whereDate('created_at', '<=', $dateTo))
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->paginate($perPage);

        return response()->json([
            'success' => true,
            'message' => 'Audit logs retrieved.',
            'data' => $logs->getCollection()
                ->map(fn (AuditLog $auditLog) => $this->auditLogResource($auditLog))
                ->values(),
            'meta' => [
                'current_page' => $logs->currentPage(),
                'per_page' => $logs->perPage(),
                'total' => $logs->total(),
                'last_page' => $logs->lastPage(),
            ],
            'errors' => null,
        ]);
    }

    public function show(Request $request, AuditLog $auditLog): JsonResponse
    {
        if (!$this->canRead($request)) {
            return $this->forbidden('Only super_admin and admin users can view audit logs.');
        }

        $auditLog->load('user:id,name,email,role');

        return response()->json([
            'success' => true,
            'message' => 'Audit log retrieved.',
            'data' => $this->auditLogResource($auditLog, true),
            'meta' => (object) [],
            'errors' => null,
        ]);
    }

    private function auditLogResource(AuditLog $auditLog, bool $includeChanges = false): array
    {
        $before = AdminAuditLogService::sanitize($auditLog->old_values ?? []);
        $after = AdminAuditLogService::sanitize($auditLog->new_values ?? []);

        $resource = [
            'id' => $auditLog->id,
            'action' => $auditLog->action,
            'actor_id' => $auditLog->user_id,
            'actor' => $auditLog->user ? [
                'id' => $auditLog->user->id,
                'name' => $auditLog->user->name,
                'email' => $auditLog->user->email,
                'role' => $auditLog->user->role,
            ] : null,
            'target_type' => $this->targetType($auditLog->model_type),
            'target_id' => $auditLog->model_id,
            'target_label' => $this->targetLabel($before, $after),
            'ip_address' => $auditLog->ip_address,
            'user_agent' => $auditLog->user_agent,
            'created_at' => $auditLog->created_at?->toISOString(),
        ];

        if ($includeChanges) {
            $resource['before_changes'] = $before;
            $resource['after_changes'] = $after;
        }

        return $resource;
    }

    private function targetType(?string $modelType): ?string
    {
        if (!$modelType) {
            return null;
        }

        if (str_contains($modelType, '\\')) {
            return Str::snake(class_basename($modelType));
        }

        return $modelType;
    }

    private function targetLabel(?array $before, ?array $after): ?string
    {
        $values = array_merge($before ?? [], $after ?? []);

        foreach (['target_label', 'name', 'title', 'email', 'question_text', 'slug'] as $field) {
            $value = $values[$field] ?? null;

            if (is_string($value) && $value !== '') {
                return $value;
            }
        }

        return null;
    }

    private function canRead(Request $request): bool
    {
        $user = $request->user();

        return $user instanceof User && in_array($user->role, [User::ROLE_SUPER_ADMIN, User::ROLE_ADMIN], true);
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
