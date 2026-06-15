<?php

namespace App\Http\Controllers\Api\V1\Vr;

use App\Http\Controllers\Controller;
use App\Models\Scene;
use App\Models\VrSceneContent;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class VrSceneContentController extends Controller
{
    public function index(Request $request, string $sceneSlug): JsonResponse
    {
        $canonicalSlug = Scene::resolveCanonicalSlug($sceneSlug);
        $locale = (string) $request->query('locale', 'id');
        $contentKey = $request->query('content_key');

        $contents = VrSceneContent::query()
            ->active()
            ->published()
            ->where('scene_slug', $canonicalSlug)
            ->where('locale', $locale)
            ->when($contentKey, fn ($query, string $key) => $query->where('content_key', $key))
            ->ordered()
            ->get()
            ->map(fn (VrSceneContent $content) => $this->contentResource($content))
            ->values();

        return response()->json([
            'success' => true,
            'message' => 'VR scene content retrieved.',
            'data' => $contents,
            'meta' => [
                'scene_slug' => $canonicalSlug,
                'locale' => $locale,
                'content_key' => $contentKey,
                'total' => $contents->count(),
            ],
            'errors' => null,
        ]);
    }

    private function contentResource(VrSceneContent $content): array
    {
        return [
            'id' => $content->id,
            'scene_slug' => $content->scene_slug,
            'content_key' => $content->content_key,
            'content_type' => $content->content_type,
            'locale' => $content->locale,
            'title' => $content->title,
            'subtitle' => $content->subtitle,
            'body' => $content->body,
            'items' => $this->activeItems($content->items_json ?? []),
            'metadata' => $content->metadata_json ?? (object) [],
            'sort_order' => $content->sort_order,
            'status' => $content->status,
            'version' => $content->version,
            'updated_at' => $content->updated_at?->toISOString(),
        ];
    }

    private function activeItems(array $items): array
    {
        return Collection::make($items)
            ->filter(fn ($item) => is_array($item) && ($item['is_active'] ?? true))
            ->sortBy(fn ($item) => (int) ($item['sort_order'] ?? $item['number'] ?? 0))
            ->values()
            ->all();
    }
}
