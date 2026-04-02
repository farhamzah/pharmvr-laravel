<?php

namespace App\Http\Requests\Admin\Ai;

use Illuminate\Foundation\Http\FormRequest;

class StoreAiAvatarScenePromptRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'avatar_profile_id' => 'required|exists:ai_avatar_profiles,id',
            'scene_key' => 'required|string|max:100',
            'object_key' => 'nullable|string|max:100',
            'prompt_title' => 'nullable|string|max:255',
            'prompt_text' => 'required|string',
            'suggested_questions' => 'nullable|array',
        ];
    }
}
