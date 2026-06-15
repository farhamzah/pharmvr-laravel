<?php

namespace App\Http\Controllers\Admin\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\VrSceneContent;
use App\Services\AdminAuditLogService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class AdminVrSceneContentController extends Controller
{
    private const CONTENT_TYPES = ['grid_panel', 'text_panel', 'checklist', 'hotspot_copy'];

    public function index(Request $request): JsonResponse
    {
        $validator = Validator::make($request->query(), [
            'search' => ['nullable', 'string', 'max:120'],
            'scene_slug' => ['nullable', 'string', 'max:120'],
            'content_key' => ['nullable', 'string', 'max:120'],
            'locale' => ['nullable', 'string', 'max:12'],
            'status' => ['nullable', Rule::in(VrSceneContent::STATUSES)],
            'page' => ['nullable', 'integer', 'min:1'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:50'],
        ]);

        if ($validator->fails()) {
            return $this->validationError($validator->errors()->toArray());
        }

        $filters = $validator->validated();
        $perPage = (int) ($filters['per_page'] ?? 15);

        $contents = VrSceneContent::query()
            ->when($filters['search'] ?? null, function ($query, string $search) {
                $query->where(function ($nested) use ($search) {
                    $nested->where('title', 'like', "%{$search}%")
                        ->orWhere('scene_slug', 'like', "%{$search}%")
                        ->orWhere('content_key', 'like', "%{$search}%")
                        ->orWhere('body', 'like', "%{$search}%");
                });
            })
            ->when($filters['scene_slug'] ?? null, fn ($query, string $value) => $query->where('scene_slug', $value))
            ->when($filters['content_key'] ?? null, fn ($query, string $value) => $query->where('content_key', $value))
            ->when($filters['locale'] ?? null, fn ($query, string $value) => $query->where('locale', $value))
            ->when($filters['status'] ?? null, fn ($query, string $value) => $query->where('status', $value))
            ->ordered()
            ->paginate($perPage);

        return response()->json([
            'success' => true,
            'message' => 'VR scene contents retrieved.',
            'data' => $contents->getCollection()
                ->map(fn (VrSceneContent $content) => $this->contentResource($content))
                ->values(),
            'meta' => [
                'current_page' => $contents->currentPage(),
                'per_page' => $contents->perPage(),
                'total' => $contents->total(),
                'last_page' => $contents->lastPage(),
            ],
            'errors' => null,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        if (!$this->canManage($request)) {
            return $this->forbidden('Only super_admin and admin users can manage VR scene content.');
        }

        $validator = Validator::make($request->all(), $this->rules());

        if ($validator->fails()) {
            return $this->validationError($validator->errors()->toArray());
        }

        $data = $this->validatedPayload($validator->validated(), $request->user());
        $content = VrSceneContent::create($data);

        app(AdminAuditLogService::class)->record(
            $request,
            $request->user(),
            'vr_scene_content.created',
            'vr_scene_content',
            $content->id,
            $content->title,
            null,
            $this->auditSnapshot($content)
        );

        return response()->json([
            'success' => true,
            'message' => 'VR scene content created.',
            'data' => $this->contentResource($content),
            'meta' => (object) [],
            'errors' => null,
        ], 201);
    }

    public function show(VrSceneContent $content): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => 'VR scene content retrieved.',
            'data' => $this->contentResource($content, true),
            'meta' => (object) [],
            'errors' => null,
        ]);
    }

    public function update(Request $request, VrSceneContent $content): JsonResponse
    {
        if (!$this->canManage($request)) {
            return $this->forbidden('Only super_admin and admin users can manage VR scene content.');
        }

        $validator = Validator::make($request->all(), $this->rules(true));

        if ($validator->fails()) {
            return $this->validationError($validator->errors()->toArray());
        }

        $before = $this->auditSnapshot($content);
        $content->fill($this->validatedPayload($validator->validated(), $request->user(), true));
        $content->save();

        app(AdminAuditLogService::class)->record(
            $request,
            $request->user(),
            'vr_scene_content.updated',
            'vr_scene_content',
            $content->id,
            $content->title,
            $before,
            $this->auditSnapshot($content)
        );

        return response()->json([
            'success' => true,
            'message' => 'VR scene content updated.',
            'data' => $this->contentResource($content, true),
            'meta' => (object) [],
            'errors' => null,
        ]);
    }

    private function rules(bool $partial = false): array
    {
        $required = $partial ? 'sometimes' : 'required';

        return [
            'scene_slug' => [$required, 'string', 'max:120'],
            'content_key' => [$required, 'string', 'max:120'],
            'content_type' => [$required, 'string', 'max:80', Rule::in(self::CONTENT_TYPES)],
            'locale' => [$required, 'string', 'max:12'],
            'title' => [$required, 'string', 'max:255'],
            'subtitle' => ['sometimes', 'nullable', 'string', 'max:255'],
            'body' => ['sometimes', 'nullable', 'string'],
            'items_json' => ['sometimes', 'nullable', 'array'],
            'metadata_json' => ['sometimes', 'nullable', 'array'],
            'sort_order' => ['sometimes', 'integer', 'min:0', 'max:10000'],
            'is_active' => ['sometimes', 'boolean'],
            'status' => ['sometimes', Rule::in(VrSceneContent::STATUSES)],
            'version' => ['sometimes', 'integer', 'min:1', 'max:100000'],
        ];
    }

    private function validatedPayload(array $data, ?User $user, bool $updating = false): array
    {
        if (!$updating && $user) {
            $data['created_by'] = $user->id;
        }

        if ($user) {
            $data['updated_by'] = $user->id;
        }

        return $data;
    }

    private function contentResource(VrSceneContent $content, bool $includeDetail = false): array
    {
        $resource = [
            'id' => $content->id,
            'scene_slug' => $content->scene_slug,
            'content_key' => $content->content_key,
            'content_type' => $content->content_type,
            'locale' => $content->locale,
            'title' => $content->title,
            'subtitle' => $content->subtitle,
            'sort_order' => $content->sort_order,
            'is_active' => $content->is_active,
            'status' => $content->status,
            'version' => $content->version,
            'created_at' => $content->created_at?->toISOString(),
            'updated_at' => $content->updated_at?->toISOString(),
        ];

        if ($includeDetail) {
            $resource['body'] = $content->body;
            $resource['items_json'] = $content->items_json ?? [];
            $resource['metadata_json'] = $content->metadata_json ?? [];
        }

        return $resource;
    }

    private function auditSnapshot(VrSceneContent $content): array
    {
        return [
            'scene_slug' => $content->scene_slug,
            'content_key' => $content->content_key,
            'content_type' => $content->content_type,
            'locale' => $content->locale,
            'title' => $content->title,
            'status' => $content->status,
            'is_active' => $content->is_active,
            'version' => $content->version,
        ];
    }

    private function canManage(Request $request): bool
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
