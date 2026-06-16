<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\VrSceneLayout;
use App\Services\VrSceneLayoutPublicationService;
use App\Services\VrSceneLayoutValidator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class VrSceneLayoutWebController extends Controller
{
    public function index(Request $request)
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:120'],
            'scene_slug' => ['nullable', 'string', 'max:120'],
            'template_key' => ['nullable', 'string', 'max:120'],
            'status' => ['nullable', Rule::in(VrSceneLayout::STATUSES)],
        ]);

        $layouts = VrSceneLayout::query()
            ->when($filters['search'] ?? null, function ($query, string $search) {
                $query->where(function ($nested) use ($search) {
                    $nested->where('scene_slug', 'like', "%{$search}%")
                        ->orWhere('title', 'like', "%{$search}%")
                        ->orWhere('template_key', 'like', "%{$search}%");
                });
            })
            ->when($filters['scene_slug'] ?? null, fn ($query, string $value) => $query->where('scene_slug', $value))
            ->when($filters['template_key'] ?? null, fn ($query, string $value) => $query->where('template_key', $value))
            ->when($filters['status'] ?? null, fn ($query, string $value) => $query->where('status', $value))
            ->latestVersion()
            ->paginate(15)
            ->withQueryString();

        $sceneSlugs = VrSceneLayout::query()
            ->select('scene_slug')
            ->distinct()
            ->orderBy('scene_slug')
            ->pluck('scene_slug');

        $templateKeys = VrSceneLayout::query()
            ->whereNotNull('template_key')
            ->select('template_key')
            ->distinct()
            ->orderBy('template_key')
            ->pluck('template_key');

        return view('admin.vr-scene-layouts.index', compact('layouts', 'sceneSlugs', 'templateKeys', 'filters'));
    }

    public function create()
    {
        $layout = new VrSceneLayout([
            'scene_slug' => 'gmp_standard_room',
            'title' => 'GMP Standard Room',
            'template_key' => 'cleanroom_standard',
            'version' => 1,
            'status' => VrSceneLayout::STATUS_DRAFT,
            'layout_json' => $this->defaultLayoutJson(),
            'metadata_json' => [
                'module_code' => 'PH-CPOB-00',
                'content_key' => 'cpob_12_aspects',
            ],
            'validation_warnings_json' => [],
        ]);

        return view('admin.vr-scene-layouts.create', compact('layout'));
    }

    public function store(
        Request $request,
        VrSceneLayoutValidator $layoutValidator,
        VrSceneLayoutPublicationService $publicationService
    ) {
        if ($response = $this->handleEditorAction($request, $layoutValidator)) {
            return $response;
        }

        $payload = $this->validatedPayload($request, $layoutValidator, $request->user()?->id);

        $layout = DB::transaction(function () use ($payload, $request, $publicationService) {
            $layout = VrSceneLayout::create($payload);

            return $publicationService->enforcePublishedState($layout, $request->user());
        });

        return redirect()
            ->route('admin.vr-scene-layouts.edit', $layout)
            ->with('success', 'VR scene layout created successfully.');
    }

    public function show(VrSceneLayout $vrSceneLayout)
    {
        return view('admin.vr-scene-layouts.show', ['layout' => $vrSceneLayout]);
    }

    public function edit(VrSceneLayout $vrSceneLayout)
    {
        return view('admin.vr-scene-layouts.edit', ['layout' => $vrSceneLayout]);
    }

    public function update(
        Request $request,
        VrSceneLayout $vrSceneLayout,
        VrSceneLayoutValidator $layoutValidator,
        VrSceneLayoutPublicationService $publicationService
    ) {
        if ($response = $this->handleEditorAction($request, $layoutValidator)) {
            return $response;
        }

        $payload = $this->validatedPayload($request, $layoutValidator, $request->user()?->id, true, $vrSceneLayout);

        $layout = DB::transaction(function () use ($vrSceneLayout, $payload, $request, $publicationService) {
            $vrSceneLayout->fill($payload);
            $vrSceneLayout->save();

            return $publicationService->enforcePublishedState($vrSceneLayout, $request->user());
        });

        return redirect()
            ->route('admin.vr-scene-layouts.edit', $layout)
            ->with('success', 'VR scene layout saved successfully.');
    }

    public function publish(
        Request $request,
        VrSceneLayout $vrSceneLayout,
        VrSceneLayoutValidator $layoutValidator,
        VrSceneLayoutPublicationService $publicationService
    ) {
        $validation = $layoutValidator->validate($vrSceneLayout->scene_slug, $vrSceneLayout->layout_json ?? []);
        if (!$validation['valid']) {
            throw ValidationException::withMessages($validation['errors']);
        }

        $publicationService->publish($vrSceneLayout, $request->user(), $validation['warnings']);

        return redirect()
            ->route('admin.vr-scene-layouts.edit', $vrSceneLayout)
            ->with('success', 'VR scene layout published successfully.');
    }

    public function archive(Request $request, VrSceneLayout $vrSceneLayout, VrSceneLayoutPublicationService $publicationService)
    {
        $publicationService->archive($vrSceneLayout, $request->user());

        return redirect()
            ->route('admin.vr-scene-layouts.edit', $vrSceneLayout)
            ->with('success', 'VR scene layout archived successfully.');
    }

    public function duplicate(Request $request, VrSceneLayout $vrSceneLayout)
    {
        $nextVersion = ((int) VrSceneLayout::query()
            ->where('scene_slug', $vrSceneLayout->scene_slug)
            ->max('version')) + 1;

        $copy = $vrSceneLayout->replicate([
            'status',
            'version',
            'created_by',
            'updated_by',
            'published_by',
            'published_at',
            'created_at',
            'updated_at',
        ]);
        $copy->forceFill([
            'title' => trim(($vrSceneLayout->title ?? $vrSceneLayout->scene_slug).' Draft'),
            'version' => $nextVersion,
            'status' => VrSceneLayout::STATUS_DRAFT,
            'created_by' => $request->user()?->id,
            'updated_by' => $request->user()?->id,
            'published_by' => null,
            'published_at' => null,
        ]);
        $copy->save();

        return redirect()
            ->route('admin.vr-scene-layouts.edit', $copy)
            ->with('success', 'VR scene layout duplicated as draft.');
    }

    private function validatedPayload(
        Request $request,
        VrSceneLayoutValidator $layoutValidator,
        ?int $userId,
        bool $updating = false,
        ?VrSceneLayout $existing = null
    ): array {
        $validator = Validator::make($request->all(), [
            'scene_slug' => ['required', 'string', 'max:120'],
            'title' => ['nullable', 'string', 'max:255'],
            'template_key' => ['nullable', 'string', 'max:120'],
            'version' => ['required', 'integer', 'min:1', 'max:100000'],
            'status' => ['required', Rule::in(VrSceneLayout::STATUSES)],
            'layout_json' => ['required', 'string'],
            'metadata_json' => ['nullable', 'string'],
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        $data = $validator->validated();
        $data['layout_json'] = $this->decodeJsonField($data['layout_json'] ?? null, 'layout_json', []);
        $data['metadata_json'] = $this->decodeJsonField($data['metadata_json'] ?? null, 'metadata_json', []);
        $data['version'] = (int) $data['version'];

        $validation = $layoutValidator->validate($data['scene_slug'], $data['layout_json']);
        if (!$validation['valid']) {
            throw ValidationException::withMessages($validation['errors']);
        }

        $data['validation_warnings_json'] = $validation['warnings'];

        if (!$updating && $userId) {
            $data['created_by'] = $userId;
        }

        if ($userId) {
            $data['updated_by'] = $userId;
        }

        if ($existing && $existing->status === VrSceneLayout::STATUS_PUBLISHED && $data['status'] !== VrSceneLayout::STATUS_PUBLISHED) {
            $data['published_by'] = null;
            $data['published_at'] = null;
        }

        return $data;
    }

    private function handleEditorAction(Request $request, VrSceneLayoutValidator $layoutValidator)
    {
        $action = $request->input('editor_action');

        if (!in_array($action, ['format', 'validate'], true)) {
            return null;
        }

        try {
            $layoutJson = $this->decodeJsonField($request->input('layout_json'), 'layout_json', []);
            $metadataJson = $this->decodeJsonField($request->input('metadata_json'), 'metadata_json', []);
        } catch (ValidationException $exception) {
            return redirect()
                ->back()
                ->withErrors($exception->errors())
                ->withInput();
        }

        if ($action === 'format') {
            return redirect()
                ->back()
                ->withInput(array_merge($request->except(['layout_json', 'metadata_json']), [
                    'layout_json' => $this->prettyJson($layoutJson),
                    'metadata_json' => $this->prettyJson($metadataJson),
                ]))
                ->with('success', 'Layout JSON and metadata JSON formatted.');
        }

        $sceneSlug = (string) $request->input('scene_slug', '');
        $validation = $layoutValidator->validate($sceneSlug, $layoutJson);

        return redirect()
            ->back()
            ->withInput()
            ->with('layout_validation', [
                'valid' => $validation['valid'],
                'errors' => $validation['errors'],
                'warnings' => $validation['warnings'],
            ])
            ->with($validation['valid'] ? 'success' : 'error', $validation['valid']
                ? 'Layout JSON is valid.'
                : 'Layout JSON is invalid. Review the validation results below.');
    }

    private function decodeJsonField(?string $value, string $field, array $default): array
    {
        if ($value === null || trim($value) === '') {
            return $default;
        }

        $decoded = json_decode($value, true);

        if (json_last_error() !== JSON_ERROR_NONE || !is_array($decoded)) {
            throw ValidationException::withMessages([
                $field => 'The '.$field.' field must contain valid JSON.',
            ]);
        }

        return $decoded;
    }

    private function prettyJson(array $value): string
    {
        return json_encode($value, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    }

    private function defaultLayoutJson(): array
    {
        return [
            'sceneSlug' => 'gmp_standard_room',
            'contentKey' => 'cpob_12_aspects',
            'components' => [
                [
                    'id' => 'opening-briefing',
                    'type' => 'learning_panel',
                    'title' => 'Tujuan Utama',
                    'transform' => [
                        'position' => [0, 1.8, -3.5],
                        'rotation' => [0, 0, 0],
                        'scale' => [1, 1, 1],
                    ],
                ],
            ],
            'learningFlow' => [
                [
                    'id' => 'inspect-room',
                    'requiredComponentIds' => ['opening-briefing'],
                ],
            ],
        ];
    }
}
