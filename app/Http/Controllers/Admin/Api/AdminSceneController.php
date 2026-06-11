<?php

namespace App\Http\Controllers\Admin\Api;

use App\Http\Controllers\Controller;
use App\Models\Scene;
use App\Models\TrainingModule;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class AdminSceneController extends Controller
{
    private const STATUSES = ['active', 'inactive'];
    private const TYPES = ['production', 'support', 'general'];
    private const DIFFICULTIES = ['beginner', 'intermediate', 'advanced'];

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

        $scenes = Scene::query()
            ->with('trainingModule:id,title,slug')
            ->when($filters['search'] ?? null, function ($query, string $search) {
                $query->where(function ($nested) use ($search) {
                    $nested->where('title', 'like', "%{$search}%")
                        ->orWhere('slug', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%");
                });
            })
            ->when($filters['status'] ?? null, fn ($query, string $status) => $query->where('is_active', $status === 'active'))
            ->when($filters['type'] ?? null, fn ($query, string $type) => $this->applyTypeFilter($query, $type))
            ->orderBy('order_index')
            ->orderBy('title')
            ->paginate($perPage);

        return response()->json([
            'success' => true,
            'message' => 'Scenes retrieved.',
            'data' => $scenes->getCollection()
                ->map(fn (Scene $scene) => $this->sceneResource($scene))
                ->values(),
            'meta' => [
                'current_page' => $scenes->currentPage(),
                'per_page' => $scenes->perPage(),
                'total' => $scenes->total(),
                'last_page' => $scenes->lastPage(),
            ],
            'errors' => null,
        ]);
    }

    public function show(Scene $scene): JsonResponse
    {
        $scene->load('trainingModule:id,title,slug');

        return response()->json([
            'success' => true,
            'message' => 'Scene retrieved.',
            'data' => $this->sceneResource($scene, true),
            'meta' => (object) [],
            'errors' => null,
        ]);
    }

    public function update(Request $request, Scene $scene): JsonResponse
    {
        if (!$this->canUpdate($request)) {
            return $this->forbidden('Only super_admin and admin users can update scenes.');
        }

        $validator = Validator::make($request->all(), [
            'name' => ['sometimes', 'string', 'max:255'],
            'title' => ['sometimes', 'string', 'max:255'],
            'description' => ['sometimes', 'nullable', 'string'],
            'status' => ['sometimes', Rule::in(self::STATUSES)],
            'order' => ['sometimes', 'nullable', 'integer', 'min:0', 'max:10000'],
            'difficulty' => ['sometimes', 'nullable', Rule::in(self::DIFFICULTIES)],
            'estimated_duration_minutes' => ['sometimes', 'nullable', 'integer', 'min:0', 'max:10000'],
            'module_id' => ['sometimes', 'nullable', 'integer', 'exists:training_modules,id'],
            'module_slug' => ['sometimes', 'nullable', 'string', 'exists:training_modules,slug'],
            'type' => ['sometimes', 'nullable', Rule::in(self::TYPES)],
        ]);

        if ($validator->fails()) {
            return $this->validationError($validator->errors()->toArray());
        }

        $data = $validator->validated();

        if (array_key_exists('title', $data) || array_key_exists('name', $data)) {
            $scene->title = $data['title'] ?? $data['name'];
        }

        if (array_key_exists('description', $data)) {
            $scene->description = $data['description'];
        }

        if (array_key_exists('status', $data)) {
            $scene->is_active = $data['status'] === 'active';
        }

        if (array_key_exists('order', $data) && $data['order'] !== null) {
            $scene->order_index = $data['order'];
        }

        if (array_key_exists('difficulty', $data) && $data['difficulty'] !== null) {
            $scene->difficulty = $data['difficulty'];
        }

        if (array_key_exists('estimated_duration_minutes', $data) && $data['estimated_duration_minutes'] !== null) {
            $scene->estimated_minutes = $data['estimated_duration_minutes'];
        }

        if (array_key_exists('module_id', $data) && $data['module_id'] !== null) {
            $scene->training_module_id = $data['module_id'];
        }

        if (array_key_exists('module_slug', $data) && $data['module_slug'] !== null) {
            $scene->training_module_id = TrainingModule::where('slug', $data['module_slug'])->value('id');
        }

        $scene->save();
        $scene->refresh()->load('trainingModule:id,title,slug');

        return response()->json([
            'success' => true,
            'message' => 'Scene updated.',
            'data' => $this->sceneResource($scene, true),
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
            $query->whereNotIn('slug', array_merge(self::PRODUCTION_SLUGS, self::SUPPORT_SLUGS));

            return;
        }

        $query->whereIn('slug', $typeSlugs);
    }

    private function sceneResource(Scene $scene, bool $includeDetail = false): array
    {
        $resource = [
            'id' => $scene->id,
            'name' => $scene->title,
            'title' => $scene->title,
            'slug' => $scene->slug,
            'description' => $scene->description,
            'type' => $this->typeForSlug($scene->slug),
            'status' => $scene->is_active ? 'active' : 'inactive',
            'order' => $scene->order_index,
            'module_id' => $scene->training_module_id,
            'module_slug' => $scene->trainingModule?->slug,
            'module_title' => $scene->trainingModule?->title,
            'difficulty' => $scene->difficulty,
            'estimated_duration_minutes' => $scene->estimated_minutes,
            'created_at' => $scene->created_at?->toISOString(),
            'updated_at' => $scene->updated_at?->toISOString(),
        ];

        if ($includeDetail) {
            $resource['learning_objectives'] = $scene->learning_objectives ?? [];
            $resource['priority'] = $scene->priority;
            $resource['environment_asset'] = $scene->environment_asset;
            $resource['required_previous_scene_id'] = $scene->required_previous_scene_id;
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

    private function canUpdate(Request $request): bool
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
