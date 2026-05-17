<?php

namespace App\Http\Controllers\Api\V1\Vr;

use App\Http\Controllers\Controller;
use App\Models\Scene;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Laravel\Sanctum\PersonalAccessToken;

/**
 * SceneController - WebXR scene registry endpoints.
 * Provides scene listing, detail, and steps for the WebXR client.
 */
class SceneController extends Controller
{
    use ApiResponse;

    /**
     * List all active scenes with user progress.
     * GET /api/v1/vr/scenes
     */
    public function index(Request $request)
    {
        $user = $this->resolveOptionalUser($request);

        $scenes = Scene::active()
            ->canonical()
            ->ordered()
            ->with([
                'trainingModule:id,title,slug',
                'steps:id,scene_id',
                'requiredPreviousScene:id,slug,title',
            ])
            ->get()
            ->map(fn(Scene $scene) => $this->scenePayload($scene, $user));

        return $this->successResponse($scenes, 'Scenes retrieved successfully.');
    }

    /**
     * Get scene detail with learning objectives and user progress.
     * GET /api/v1/vr/scenes/{slug}
     */
    public function show(Request $request, string $slug)
    {
        $user = $this->resolveOptionalUser($request);
        $canonicalSlug = Scene::resolveCanonicalSlug($slug);
        $scene = Scene::where('slug', $canonicalSlug)
            ->with(['trainingModule:id,title,slug', 'requiredPreviousScene:id,slug,title'])
            ->firstOrFail();

        return $this->successResponse(
            array_merge($this->scenePayload($scene, $user, Scene::isLegacySlug($slug)), [
                'learning_objectives' => $scene->learning_objectives,
            ]),
            'Scene detail retrieved.'
        );
    }

    /**
     * Get ordered steps for a scene.
     * GET /api/v1/vr/scenes/{slug}/steps
     */
    public function steps(Request $request, string $slug)
    {
        $scene = Scene::where('slug', Scene::resolveCanonicalSlug($slug))->firstOrFail();

        $steps = $scene->steps->map(function ($step) {
            return [
                'id' => $step->id,
                'slug' => $step->slug,
                'title' => $step->title,
                'description' => $step->description,
                'order' => $step->order_index,
                'is_required' => $step->is_required,
                'scoring_weight' => $step->scoring_weight,
                'max_score' => $step->max_score,
                'mistake_penalty' => $step->mistake_penalty,
                'interaction_type' => $step->interaction_type,
            ];
        });

        return $this->successResponse($steps, 'Scene steps retrieved.');
    }

    private function resolveOptionalUser(Request $request): ?object
    {
        if ($request->user()) {
            return $request->user();
        }

        $token = $request->bearerToken();
        if (!$token) {
            return null;
        }

        $accessToken = PersonalAccessToken::findToken($token);

        return $accessToken?->tokenable;
    }

    private function scenePayload(Scene $scene, ?object $user, bool $isLegacyRequest = false): array
    {
        $userId = $user?->id;
        $progress = $userId ? $scene->progressFor($userId) : null;

        return [
            'id' => $scene->id,
            'slug' => $scene->slug,
            'canonical_slug' => $scene->canonicalSlug(),
            'is_legacy' => $isLegacyRequest || Scene::isLegacySlug($scene->slug),
            'title' => $scene->title,
            'description' => $scene->description,
            'order' => $scene->order_index,
            'priority' => $scene->priority,
            'difficulty' => $scene->difficulty,
            'estimated_minutes' => $scene->estimated_minutes,
            'steps_count' => $scene->relationLoaded('steps') ? $scene->steps->count() : $scene->steps()->count(),
            'environment_asset' => $scene->environment_asset,
            'required_previous_scene_id' => $scene->required_previous_scene_id,
            'required_previous_scene_slug' => $scene->requiredPreviousScene?->slug,
            'required_previous_scene_title' => $scene->requiredPreviousScene?->title,
            'is_locked' => $user ? !$scene->isUnlockedFor($user) : false,
            'best_score' => $progress['best_score'] ?? null,
            'attempts' => $progress['attempts'] ?? 0,
            'progress' => $progress,
            'user_progress' => $progress,
            'module' => $scene->trainingModule ? [
                'id' => $scene->trainingModule->id,
                'title' => $scene->trainingModule->title,
                'slug' => $scene->trainingModule->slug,
            ] : null,
        ];
    }
}
