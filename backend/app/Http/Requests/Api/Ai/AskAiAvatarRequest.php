<?php

namespace App\Http\Requests\Api\Ai;

use Illuminate\Foundation\Http\FormRequest;

class AskAiAvatarRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'question' => 'required|string|min:2|max:500',
            'avatar_slug' => 'required|exists:ai_avatar_profiles,slug',
            'scene_key' => 'nullable|string|max:100',
            'object_key' => 'nullable|string|max:100',
            'interaction_mode' => 'nullable|in:greeting,ask,explain,hint,vr_concise',
        ];
    }
}
