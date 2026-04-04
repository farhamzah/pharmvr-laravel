<?php

namespace App\Http\Controllers\Admin\Ai;

use App\Http\Controllers\Controller;
use App\Models\AiAvatarProfile;
use App\Models\AiAvatarScenePrompt;
use Illuminate\Http\Request;

class AiScenePromptWebController extends Controller
{
    public function index()
    {
        $prompts = AiAvatarScenePrompt::with('avatar')->latest()->get();
        $avatars = AiAvatarProfile::active()->get();
        return view('admin.ai.scene-prompts.index', compact('prompts', 'avatars'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'ai_avatar_profile_id' => 'required|exists:ai_avatar_profiles,id',
            'scene_key' => 'required|string',
            'object_key' => 'nullable|string',
            'prompt_title' => 'required|string',
            'prompt_text' => 'required|string',
            'suggested_questions_json' => 'nullable|array',
        ]);

        $data['is_active'] = $request->has('is_active');

        AiAvatarScenePrompt::create($data);

        return back()->with('success', 'Scene prompt created.');
    }

    public function update(Request $request, AiAvatarScenePrompt $prompt)
    {
        $data = $request->validate([
            'ai_avatar_profile_id' => 'required|exists:ai_avatar_profiles,id',
            'scene_key' => 'required|string',
            'object_key' => 'nullable|string',
            'prompt_title' => 'required|string',
            'prompt_text' => 'required|string',
            'suggested_questions_json' => 'nullable|array',
        ]);

        $data['is_active'] = $request->has('is_active');

        $prompt->update($data);

        return back()->with('success', 'Scene prompt updated.');
    }

    public function destroy(AiAvatarScenePrompt $prompt)
    {
        $prompt->delete();
        return back()->with('success', 'Scene prompt deleted.');
    }
}
