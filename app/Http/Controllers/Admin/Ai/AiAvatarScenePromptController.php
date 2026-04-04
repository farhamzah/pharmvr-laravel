<?php

namespace App\Http\Controllers\Admin\Ai;

use App\Http\Controllers\Controller;
use App\Models\AiAvatarScenePrompt;
use App\Http\Requests\Admin\Ai\StoreAiAvatarScenePromptRequest;
use Illuminate\Http\Request;

class AiAvatarScenePromptController extends Controller
{
    public function index(Request $request)
    {
        $query = AiAvatarScenePrompt::with('avatarProfile');
        
        if ($request->has('avatar_id')) {
            $query->where('avatar_profile_id', $request->avatar_id);
        }
        
        return response()->json($query->latest()->get());
    }

    public function store(StoreAiAvatarScenePromptRequest $request)
    {
        $prompt = AiAvatarScenePrompt::create($request->validated());
        
        return response()->json([
            'message' => 'Scene prompt created.',
            'prompt' => $prompt
        ], 201);
    }

    public function show(AiAvatarScenePrompt $prompt)
    {
        $prompt->load('avatarProfile');
        return response()->json($prompt);
    }

    public function update(Request $request, AiAvatarScenePrompt $prompt)
    {
        $prompt->update($request->all());
        
        return response()->json([
            'message' => 'Scene prompt updated.',
            'prompt' => $prompt
        ]);
    }

    public function destroy(AiAvatarScenePrompt $prompt)
    {
        $prompt->delete();
        return response()->json(['message' => 'Scene prompt deleted.']);
    }
}
