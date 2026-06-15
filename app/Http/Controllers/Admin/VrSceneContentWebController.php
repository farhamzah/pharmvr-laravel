<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\VrSceneContent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class VrSceneContentWebController extends Controller
{
    private const CONTENT_TYPES = ['grid_panel', 'text_panel', 'checklist', 'hotspot_copy'];

    public function index(Request $request)
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:120'],
            'scene_slug' => ['nullable', 'string', 'max:120'],
            'locale' => ['nullable', 'string', 'max:12'],
            'status' => ['nullable', Rule::in(VrSceneContent::STATUSES)],
            'is_active' => ['nullable', Rule::in(['0', '1'])],
        ]);

        $contents = VrSceneContent::query()
            ->when($filters['search'] ?? null, function ($query, string $search) {
                $query->where(function ($nested) use ($search) {
                    $nested->where('scene_slug', 'like', "%{$search}%")
                        ->orWhere('content_key', 'like', "%{$search}%")
                        ->orWhere('title', 'like', "%{$search}%");
                });
            })
            ->when($filters['scene_slug'] ?? null, fn ($query, string $value) => $query->where('scene_slug', $value))
            ->when($filters['locale'] ?? null, fn ($query, string $value) => $query->where('locale', $value))
            ->when($filters['status'] ?? null, fn ($query, string $value) => $query->where('status', $value))
            ->when(array_key_exists('is_active', $filters) && $filters['is_active'] !== null, fn ($query) => $query->where('is_active', (bool) $filters['is_active']))
            ->ordered()
            ->paginate(15)
            ->withQueryString();

        $sceneSlugs = VrSceneContent::query()
            ->select('scene_slug')
            ->distinct()
            ->orderBy('scene_slug')
            ->pluck('scene_slug');

        $locales = VrSceneContent::query()
            ->select('locale')
            ->distinct()
            ->orderBy('locale')
            ->pluck('locale');

        return view('admin.vr-scene-contents.index', compact('contents', 'sceneSlugs', 'locales', 'filters'));
    }

    public function create()
    {
        $content = new VrSceneContent([
            'scene_slug' => 'gmp_standard_room',
            'content_key' => 'cpob_12_aspects',
            'content_type' => 'grid_panel',
            'locale' => 'id',
            'sort_order' => 0,
            'is_active' => true,
            'status' => VrSceneContent::STATUS_PUBLISHED,
            'version' => 1,
            'items_json' => [],
            'metadata_json' => [],
        ]);

        return view('admin.vr-scene-contents.create', compact('content'));
    }

    public function store(Request $request)
    {
        $content = VrSceneContent::create($this->validatedPayload($request, $request->user()?->id));

        return redirect()
            ->route('admin.vr-scene-contents.edit', $content)
            ->with('success', 'VR scene content created successfully.');
    }

    public function edit(VrSceneContent $vrSceneContent)
    {
        return view('admin.vr-scene-contents.edit', ['content' => $vrSceneContent]);
    }

    public function update(Request $request, VrSceneContent $vrSceneContent)
    {
        $vrSceneContent->update($this->validatedPayload($request, $request->user()?->id, true));

        return redirect()
            ->route('admin.vr-scene-contents.edit', $vrSceneContent)
            ->with('success', 'VR scene content updated successfully.');
    }

    public function destroy(VrSceneContent $vrSceneContent)
    {
        $vrSceneContent->update([
            'is_active' => false,
            'status' => VrSceneContent::STATUS_ARCHIVED,
            'updated_by' => auth()->id(),
        ]);

        return redirect()
            ->route('admin.vr-scene-contents.index')
            ->with('success', 'VR scene content archived successfully.');
    }

    private function validatedPayload(Request $request, ?int $userId, bool $updating = false): array
    {
        $validator = Validator::make($request->all(), [
            'scene_slug' => ['required', 'string', 'max:120'],
            'content_key' => ['required', 'string', 'max:120'],
            'content_type' => ['required', 'string', 'max:80', Rule::in(self::CONTENT_TYPES)],
            'locale' => ['required', 'string', 'max:12'],
            'title' => ['required', 'string', 'max:255'],
            'subtitle' => ['nullable', 'string', 'max:255'],
            'body' => ['nullable', 'string'],
            'items_json' => ['nullable', 'string'],
            'metadata_json' => ['nullable', 'string'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:10000'],
            'is_active' => ['nullable', 'boolean'],
            'status' => ['required', Rule::in(VrSceneContent::STATUSES)],
            'version' => ['required', 'integer', 'min:1', 'max:100000'],
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        $data = $validator->validated();
        $data['items_json'] = $this->decodeJsonField($data['items_json'] ?? null, 'items_json', []);
        $data['metadata_json'] = $this->decodeJsonField($data['metadata_json'] ?? null, 'metadata_json', []);
        $data['is_active'] = $request->boolean('is_active');
        $data['sort_order'] = (int) ($data['sort_order'] ?? 0);
        $data['version'] = (int) $data['version'];

        if (!$updating && $userId) {
            $data['created_by'] = $userId;
        }

        if ($userId) {
            $data['updated_by'] = $userId;
        }

        return $data;
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
}
