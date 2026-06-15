<?php

namespace App\Http\Controllers\Admin\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\VrSceneLayout;
use App\Services\VrSceneLayoutPublicationService;
use App\Services\VrSceneLayoutValidator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class AdminVrSceneLayoutController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $validator = Validator::make($request->query(), [
            'search' => ['nullable', 'string', 'max:120'],
            'scene_slug' => ['nullable', 'string', 'max:120'],
            'status' => ['nullable', Rule::in(VrSceneLayout::STATUSES)],
            'page' => ['nullable', 'integer', 'min:1'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:50'],
        ]);

        if ($validator->fails()) {
            return $this->validationError($validator->errors()->toArray());
        }

        $filters = $validator->validated();
        $perPage = (int) ($filters['per_page'] ?? 15);
        $layouts = VrSceneLayout::query()
            ->when($filters['search'] ?? null, function ($query, string $search) {
                $query->where(function ($nested) use ($search) {
                    $nested->where('scene_slug', 'like', "%{$search}%")
                        ->orWhere('title', 'like', "%{$search}%")
                        ->orWhere('template_key', 'like', "%{$search}%");
                });
            })
            ->when($filters['scene_slug'] ?? null, fn ($query, string $value) => $query->where('scene_slug', $value))
            ->when($filters['status'] ?? null, fn ($query, string $value) => $query->where('status', $value))
            ->latestVersion()
            ->paginate($perPage);

        return response()->json([
            'success' => true,
            'message' => 'VR scene layouts retrieved.',
            'data' => $layouts->getCollection()
                ->map(fn (VrSceneLayout $layout) => $this->layoutResource($layout))
                ->values(),
            'meta' => [
                'current_page' => $layouts->currentPage(),
                'per_page' => $layouts->perPage(),
                'total' => $layouts->total(),
                'last_page' => $layouts->lastPage(),
            ],
            'errors' => null,
        ]);
    }

    public function store(
        Request $request,
        VrSceneLayoutValidator $layoutValidator,
        VrSceneLayoutPublicationService $publicationService
    ): JsonResponse
    {
        if (!$this->canManage($request)) {
            return $this->forbidden('Only super_admin and admin users can manage VR scene layouts.');
        }

        $validator = Validator::make($request->all(), $this->rules());
        if ($validator->fails()) {
            return $this->validationError($validator->errors()->toArray());
        }

        $data = $validator->validated();
        $validation = $layoutValidator->validate($data['scene_slug'], $data['layout_json']);
        if (!$validation['valid']) {
            return $this->validationError($validation['errors']);
        }

        $layout = DB::transaction(function () use ($data, $request, $validation, $publicationService) {
            $layout = VrSceneLayout::create($this->payload($data, $request->user(), $validation['warnings']));

            return $publicationService->enforcePublishedState($layout, $request->user());
        });

        return response()->json([
            'success' => true,
            'message' => 'VR scene layout created.',
            'data' => $this->layoutResource($layout, true),
            'meta' => (object) [],
            'errors' => null,
        ], 201);
    }

    public function show(VrSceneLayout $vrSceneLayout): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => 'VR scene layout retrieved.',
            'data' => $this->layoutResource($vrSceneLayout, true),
            'meta' => (object) [],
            'errors' => null,
        ]);
    }

    public function update(
        Request $request,
        VrSceneLayout $vrSceneLayout,
        VrSceneLayoutValidator $layoutValidator,
        VrSceneLayoutPublicationService $publicationService
    ): JsonResponse
    {
        if (!$this->canManage($request)) {
            return $this->forbidden('Only super_admin and admin users can manage VR scene layouts.');
        }

        $validator = Validator::make($request->all(), $this->rules(true));
        if ($validator->fails()) {
            return $this->validationError($validator->errors()->toArray());
        }

        $data = array_merge([
            'scene_slug' => $vrSceneLayout->scene_slug,
            'layout_json' => $vrSceneLayout->layout_json ?? [],
        ], $validator->validated());

        $validation = $layoutValidator->validate($data['scene_slug'], $data['layout_json']);
        if (!$validation['valid']) {
            return $this->validationError($validation['errors']);
        }

        $vrSceneLayout = DB::transaction(function () use ($data, $request, $validation, $vrSceneLayout, $publicationService) {
            $vrSceneLayout->fill($this->payload($data, $request->user(), $validation['warnings'], true));
            $vrSceneLayout->save();

            return $publicationService->enforcePublishedState($vrSceneLayout, $request->user());
        });

        return response()->json([
            'success' => true,
            'message' => 'VR scene layout updated.',
            'data' => $this->layoutResource($vrSceneLayout, true),
            'meta' => (object) [],
            'errors' => null,
        ]);
    }

    public function publish(
        Request $request,
        VrSceneLayout $vrSceneLayout,
        VrSceneLayoutValidator $layoutValidator,
        VrSceneLayoutPublicationService $publicationService
    ): JsonResponse
    {
        if (!$this->canManage($request)) {
            return $this->forbidden('Only super_admin and admin users can publish VR scene layouts.');
        }

        $validation = $layoutValidator->validate($vrSceneLayout->scene_slug, $vrSceneLayout->layout_json ?? []);
        if (!$validation['valid']) {
            return $this->validationError($validation['errors']);
        }

        $layout = $publicationService->publish($vrSceneLayout, $request->user(), $validation['warnings']);

        return response()->json([
            'success' => true,
            'message' => 'VR scene layout published.',
            'data' => $this->layoutResource($layout, true),
            'meta' => (object) [],
            'errors' => null,
        ]);
    }

    public function archive(
        Request $request,
        VrSceneLayout $vrSceneLayout,
        VrSceneLayoutPublicationService $publicationService
    ): JsonResponse
    {
        if (!$this->canManage($request)) {
            return $this->forbidden('Only super_admin and admin users can archive VR scene layouts.');
        }

        $vrSceneLayout = $publicationService->archive($vrSceneLayout, $request->user());

        return response()->json([
            'success' => true,
            'message' => 'VR scene layout archived.',
            'data' => $this->layoutResource($vrSceneLayout, true),
            'meta' => (object) [],
            'errors' => null,
        ]);
    }

    private function rules(bool $partial = false): array
    {
        $required = $partial ? 'sometimes' : 'required';

        return [
            'scene_slug' => [$required, 'string', 'max:120'],
            'title' => ['sometimes', 'nullable', 'string', 'max:255'],
            'template_key' => ['sometimes', 'nullable', 'string', 'max:120'],
            'version' => ['sometimes', 'integer', 'min:1', 'max:100000'],
            'status' => ['sometimes', Rule::in(VrSceneLayout::STATUSES)],
            'layout_json' => [$required, 'array'],
            'metadata_json' => ['sometimes', 'nullable', 'array'],
        ];
    }

    private function payload(array $data, ?User $user, array $warnings, bool $updating = false): array
    {
        $payload = $data;
        $payload['validation_warnings_json'] = $warnings;

        if (!$updating && $user) {
            $payload['created_by'] = $user->id;
        }

        if ($user) {
            $payload['updated_by'] = $user->id;
        }

        return $payload;
    }

    private function layoutResource(VrSceneLayout $layout, bool $includeDetail = false): array
    {
        $resource = [
            'id' => $layout->id,
            'scene_slug' => $layout->scene_slug,
            'title' => $layout->title,
            'template_key' => $layout->template_key,
            'version' => $layout->version,
            'status' => $layout->status,
            'published_at' => $layout->published_at?->toISOString(),
            'created_at' => $layout->created_at?->toISOString(),
            'updated_at' => $layout->updated_at?->toISOString(),
        ];

        if ($includeDetail) {
            $resource['layout_json'] = $layout->layout_json ?? [];
            $resource['metadata_json'] = $layout->metadata_json ?? [];
            $resource['validation_warnings_json'] = $layout->validation_warnings_json ?? [];
        }

        return $resource;
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
