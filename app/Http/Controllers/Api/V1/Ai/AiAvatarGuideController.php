<?php

namespace App\Http\Controllers\Api\V1\Ai;

use App\Http\Controllers\Controller;
use App\Models\AiAvatarProfile;
use App\Models\AiAvatarScenePrompt;
use App\Http\Requests\Api\Ai\AskAiAvatarRequest;
use App\Services\Ai\AiAvatarGuideService;
use App\Models\TrainingModule;
use Illuminate\Http\Request;

class AiAvatarGuideController extends Controller
{
    protected $guideService;

    public function __construct(AiAvatarGuideService $guideService)
    {
        $this->guideService = $guideService;
    }

    public function profiles()
    {
        $profiles = AiAvatarProfile::where('is_active', true)->get();
        return response()->json($profiles);
    }

    /**
     * Get guidance for a specific scene context.
     */
    public function guide(Request $request)
    {
        $request->validate([
            'avatar_slug' => 'required|exists:ai_avatar_profiles,slug',
            'scene_key' => 'required|string',
            'object_key' => 'nullable|string',
            'interaction_mode' => 'nullable|in:greeting,explain,hint',
        ]);

        $avatar = AiAvatarProfile::where('slug', $request->avatar_slug)->firstOrFail();
        
        $guidance = $this->guideService->getGuidance(
            $avatar, 
            $request->scene_key, 
            $request->object_key,
            $request->interaction_mode ?? 'greeting'
        );

        return response()->json($guidance);
    }

    /**
     * Ask a specific question to the Avatar guide.
     */
    public function ask(AskAiAvatarRequest $request)
    {
        $avatar = AiAvatarProfile::where('slug', $request->avatar_slug)->firstOrFail();
        
        $module = null;
        if ($request->has('module_id')) {
            $module = TrainingModule::find($request->module_id);
        }

        $response = $this->guideService->askAvatar(
            $avatar, 
            $request->question, 
            $module,
            $request->interaction_mode ?? 'ask'
        );
        
        return response()->json($response);
    }

    /**
     * Retrieve all prompts for a specific scene key.
     */
    public function scenePrompts(string $sceneKey)
    {
        $prompts = AiAvatarScenePrompt::where('scene_key', $sceneKey)
            ->where('is_active', true)
            ->get();
            
        return response()->json($prompts);
    }
}
