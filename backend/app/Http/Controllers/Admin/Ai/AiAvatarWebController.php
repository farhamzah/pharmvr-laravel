<?php

namespace App\Http\Controllers\Admin\Ai;

use App\Http\Controllers\Controller;
use App\Models\AiAvatarProfile;
use App\Models\TrainingModule;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AiAvatarWebController extends Controller
{
    public function index()
    {
        $avatars = AiAvatarProfile::with('defaultModule')
            ->withCount('scenePrompts')
            ->latest()
            ->get();
        return view('admin.ai.avatars.index', compact('avatars'));
    }

    public function create()
    {
        $modules = TrainingModule::active()->get();
        return view('admin.ai.avatars.create', compact('modules'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'role_title' => 'required|string|max:255',
            'persona_text' => 'required|string',
            'greeting_text' => 'required|string',
            'default_module_id' => 'nullable|exists:training_modules,id',
            'allowed_topics' => 'nullable|array',
            'voice_style' => 'nullable|string',
            'avatar_model_path' => 'nullable|string',
        ]);

        $data['slug'] = Str::slug($data['name']);
        $data['is_active'] = $request->has('is_active');

        AiAvatarProfile::create($data);

        return redirect()->route('admin.ai.avatars.index')
            ->with('success', 'Avatar profile created.');
    }

    public function edit(AiAvatarProfile $avatar)
    {
        $modules = TrainingModule::active()->get();
        return view('admin.ai.avatars.edit', compact('avatar', 'modules'));
    }

    public function update(Request $request, AiAvatarProfile $avatar)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'role_title' => 'required|string|max:255',
            'persona_text' => 'required|string',
            'greeting_text' => 'required|string',
            'default_module_id' => 'nullable|exists:training_modules,id',
            'allowed_topics' => 'nullable|array',
            'voice_style' => 'nullable|string',
            'avatar_model_path' => 'nullable|string',
        ]);

        $data['is_active'] = $request->has('is_active');

        $avatar->update($data);

        return redirect()->route('admin.ai.avatars.index')
            ->with('success', 'Avatar profile updated.');
    }

    public function toggleActive(AiAvatarProfile $avatar)
    {
        $avatar->update(['is_active' => !$avatar->is_active]);
        return back()->with('success', 'Avatar status updated.');
    }

    public function destroy(AiAvatarProfile $avatar)
    {
        $avatar->delete();
        return redirect()->route('admin.ai.avatars.index')
            ->with('success', 'Avatar profile deleted.');
    }
}
