<?php

namespace App\Http\Controllers\Admin\Api;

use App\Http\Controllers\Controller;
use App\Models\Scene;
use App\Models\TrainingModule;
use App\Models\User;
use App\Services\AdminAuditLogService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class AdminModuleController extends Controller
{
    private const STATUSES = ['active', 'inactive'];
    private const TYPES = ['production', 'support', 'general'];

    private const PRODUCTION_SLUGS = [
        'hygiene',
        'gowning',
        'airlock',
        'production_corridor',
        'weighing',
        'granulation',
        'final_mixing',
        'tabletting',
        'coating',
        'blistering',
        'secondary_packing',
    ];

    private const SUPPORT_SLUGS = [
        'qc_lab',
        'qa_office',
        'warehouse',
        'ppic',
        'purchasing',
        'engineering',
    ];

    public function index(Request $request): JsonResponse
    {
        $validator = Validator::make($request->query(), [
            'search' => ['nullable', 'string', 'max:120'],
            'status' => ['nullable', Rule::in(self::STATUSES)],
            'type' => ['nullable', Rule::in(self::TYPES)],
            'page' => ['nullable', 'integer', 'min:1'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:50'],
        ]);

        if ($validator->fails()) {
            return $this->validationError($validator->errors()->toArray());
        }

        $filters = $validator->validated();
        $perPage = (int) ($filters['per_page'] ?? 15);

        $modules = TrainingModule::query()
            ->with(['scenes' => fn ($query) => $query->orderBy('order_index')])
            ->when($filters['search'] ?? null, function ($query, string $search) {
                $query->where(function ($nested) use ($search) {
                    $nested->where('title', 'like', "%{$search}%")
                        ->orWhere('slug', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%");
                });
            })
            ->when($filters['status'] ?? null, fn ($query, string $status) => $query->where('is_active', $status === 'active'))
            ->when($filters['type'] ?? null, fn ($query, string $type) => $this->applyTypeFilter($query, $type))
            ->orderBy('title')
            ->paginate($perPage);

        return response()->json([
            'success' => true,
            'message' => 'Modules retrieved.',
            'data' => $modules->getCollection()
                ->map(fn (TrainingModule $module) => $this->moduleResource($module))
                ->values(),
            'meta' => [
                'current_page' => $modules->currentPage(),
                'per_page' => $modules->perPage(),
                'total' => $modules->total(),
                'last_page' => $modules->lastPage(),
            ],
            'errors' => null,
        ]);
    }

    public function show(TrainingModule $module): JsonResponse
    {
        $module->load(['scenes' => fn ($query) => $query->orderBy('order_index')]);

        return response()->json([
            'success' => true,
            'message' => 'Module retrieved.',
            'data' => $this->moduleResource($module, true),
            'meta' => (object) [],
            'errors' => null,
        ]);
    }

    public function update(Request $request, TrainingModule $module): JsonResponse
    {
        if (!$this->canUpdate($request)) {
            return $this->forbidden('Only super_admin and admin users can update modules.');
        }

        $firstScene = $module->scenes()->first();
        $before = $this->auditSnapshot($module, $firstScene);

        $validator = Validator::make($request->all(), [
            'title' => ['sometimes', 'string', 'max:255'],
            'description' => ['sometimes', 'nullable', 'string'],
            'status' => ['sometimes', Rule::in(self::STATUSES)],
            'difficulty' => ['sometimes', 'nullable', 'string', 'max:80'],
            'estimated_duration' => ['sometimes', 'nullable', 'integer', 'min:0', 'max:10000'],
            'order' => ['sometimes', 'nullable', 'integer', 'min:0', 'max:10000'],
            'scene_slug' => [
                'sometimes',
                'nullable',
                'string',
                'max:100',
                Rule::unique('scenes', 'slug')->ignore($firstScene?->id),
            ],
            'type' => ['sometimes', 'nullable', Rule::in(self::TYPES)],
        ]);

        if ($validator->fails()) {
            return $this->validationError($validator->errors()->toArray());
        }

        $data = $validator->validated();

        foreach (['title', 'description', 'difficulty', 'estimated_duration'] as $field) {
            if (array_key_exists($field, $data)) {
                $module->{$field} = $data[$field];
            }
        }

        if (array_key_exists('status', $data)) {
            $module->is_active = $data['status'] === 'active';
        }

        $module->save();

        if ($firstScene) {
            if (array_key_exists('order', $data) && $data['order'] !== null) {
                $firstScene->order_index = $data['order'];
            }

            if (array_key_exists('scene_slug', $data) && $data['scene_slug'] !== null) {
                $firstScene->slug = $data['scene_slug'];
            }

            if ($firstScene->isDirty()) {
                $firstScene->save();
            }
        }

        $module->refresh()->load(['scenes' => fn ($query) => $query->orderBy('order_index')]);
        $updatedFirstScene = $module->scenes->sortBy('order_index')->first();

        app(AdminAuditLogService::class)->record(
            $request,
            $request->user(),
            'module.updated',
            'training_module',
            $module->id,
            $module->title,
            $before,
            $this->auditSnapshot($module, $updatedFirstScene)
        );

        return response()->json([
            'success' => true,
            'message' => 'Module updated.',
            'data' => $this->moduleResource($module, true),
            'meta' => (object) [],
            'errors' => null,
        ]);
    }

    private function applyTypeFilter($query, string $type): void
    {
        $typeSlugs = match ($type) {
            'production' => self::PRODUCTION_SLUGS,
            'support' => self::SUPPORT_SLUGS,
            default => [],
        };

        if ($type === 'general') {
            $knownSlugs = array_merge(self::PRODUCTION_SLUGS, self::SUPPORT_SLUGS);
            $query->whereNotIn('slug', $knownSlugs)
                ->whereDoesntHave('scenes', fn ($sceneQuery) => $sceneQuery->whereIn('slug', $knownSlugs));

            return;
        }

        $query->where(function ($nested) use ($typeSlugs) {
            $nested->whereIn('slug', $typeSlugs)
                ->orWhereHas('scenes', fn ($sceneQuery) => $sceneQuery->whereIn('slug', $typeSlugs));
        });
    }

    private function moduleResource(TrainingModule $module, bool $includeDetail = false): array
    {
        $firstScene = $module->relationLoaded('scenes')
            ? $module->scenes->sortBy('order_index')->first()
            : $module->scenes()->first();
        $classificationSlug = $firstScene?->slug ?? $module->slug;

        $resource = [
            'id' => $module->id,
            'title' => $module->title,
            'slug' => $module->slug,
            'description' => $module->description,
            'type' => $this->typeForSlug($classificationSlug),
            'status' => $module->is_active ? 'active' : 'inactive',
            'order' => $firstScene?->order_index ?? $this->pathOrderForSlug($module->slug),
            'scene_slug' => $firstScene?->slug,
            'difficulty' => $module->difficulty,
            'estimated_duration' => $module->estimated_duration,
            'cover_image_url' => $module->cover_image_url,
            'created_at' => $module->created_at?->toISOString(),
            'updated_at' => $module->updated_at?->toISOString(),
        ];

        if ($includeDetail) {
            $resource['learning_objectives'] = $firstScene?->learning_objectives ?? [];
            $resource['cpob_mapping'] = [];
            $resource['scenes'] = $module->scenes
                ->map(fn (Scene $scene) => [
                    'id' => $scene->id,
                    'title' => $scene->title,
                    'slug' => $scene->slug,
                    'status' => $scene->is_active ? 'active' : 'inactive',
                    'order' => $scene->order_index,
                ])
                ->values();
        }

        return $resource;
    }

    private function typeForSlug(?string $slug): string
    {
        if ($slug && in_array($slug, self::PRODUCTION_SLUGS, true)) {
            return 'production';
        }

        if ($slug && in_array($slug, self::SUPPORT_SLUGS, true)) {
            return 'support';
        }

        return 'general';
    }

    private function pathOrderForSlug(?string $slug): ?int
    {
        $index = array_search($slug, self::PRODUCTION_SLUGS, true);

        return $index === false ? null : $index + 1;
    }

    private function canUpdate(Request $request): bool
    {
        $user = $request->user();

        return $user instanceof User && in_array($user->role, [User::ROLE_SUPER_ADMIN, User::ROLE_ADMIN], true);
    }

    private function auditSnapshot(TrainingModule $module, ?Scene $firstScene): array
    {
        return [
            'title' => $module->title,
            'slug' => $module->slug,
            'description' => $module->description,
            'status' => $module->is_active ? 'active' : 'inactive',
            'difficulty' => $module->difficulty,
            'estimated_duration' => $module->estimated_duration,
            'order' => $firstScene?->order_index,
            'scene_slug' => $firstScene?->slug,
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
