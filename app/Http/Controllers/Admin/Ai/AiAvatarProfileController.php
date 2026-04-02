<?php

namespace App\Http\Controllers\Admin\Ai;

use App\Http\Controllers\Controller;
use App\Models\AiAvatarProfile;
use App\Http\Requests\Admin\Ai\StoreAiAvatarProfileRequest;
use Illuminate\Http\Request;

class AiAvatarProfileController extends Controller
{
    public function index()
    {
        $profiles = AiAvatarProfile::with('defaultModule')
            ->withCount('scenePrompts')
            ->latest()
            ->get();
            
        return response()->json($profiles);
    }

    public function store(StoreAiAvatarProfileRequest $request)
    {
        $profile = AiAvatarProfile::create($request->validated());
        
        return response()->json([
            'message' => 'Avatar profile created.',
            'profile' => $profile
        ], 201);
    }

    public function show(AiAvatarProfile $profile)
    {
        $profile->load('defaultModule', 'scenePrompts');
        return response()->json($profile);
    }

    public function update(Request $request, AiAvatarProfile $profile)
    {
        // Simple update for now
        $profile->update($request->all());
        
        return response()->json([
            'message' => 'Avatar profile updated.',
            'profile' => $profile
        ]);
    }

    public function toggleActive(AiAvatarProfile $profile)
    {
        $profile->update(['is_active' => !$profile->is_active]);
        
        return response()->json([
            'message' => 'Status toggled.',
            'is_active' => $profile->is_active
        ]);
    }

    public function destroy(AiAvatarProfile $profile)
    {
        $profile->delete();
        return response()->json(['message' => 'Profile deleted.']);
    }
}
