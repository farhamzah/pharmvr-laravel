<?php

namespace App\Http\Controllers\Api\V1\Vr;

use App\Http\Controllers\Controller;
use App\Models\Scene;
use App\Models\VrSceneLayout;
use Illuminate\Http\JsonResponse;

class VrSceneLayoutController extends Controller
{
    public function show(string $sceneSlug): JsonResponse
    {
        $canonicalSlug = Scene::resolveCanonicalSlug($sceneSlug);
        $layout = VrSceneLayout::query()
            ->forScene($canonicalSlug)
            ->published()
            ->latestVersion()
            ->first();

        if (!$layout) {
            return response()->json([
                'success' => false,
                'message' => 'Published VR scene layout not found.',
                'data' => null,
                'meta' => [
                    'scene_slug' => $canonicalSlug,
                ],
                'errors' => null,
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'VR scene layout retrieved.',
            'data' => $this->layoutResource($layout),
            'meta' => [
                'scene_slug' => $canonicalSlug,
            ],
            'errors' => null,
        ]);
    }

    private function layoutResource(VrSceneLayout $layout): array
    {
        return [
            'id' => $layout->id,
            'scene_slug' => $layout->scene_slug,
            'title' => $layout->title,
            'template_key' => $layout->template_key,
            'version' => $layout->version,
            'status' => $layout->status,
            'layout_json' => $layout->layout_json ?? [],
            'metadata_json' => $layout->metadata_json ?? (object) [],
            'published_at' => $layout->published_at?->toISOString(),
            'updated_at' => $layout->updated_at?->toISOString(),
        ];
    }
}
