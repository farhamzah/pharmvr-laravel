<?php

namespace App\Http\Requests\Admin\Ai;

use Illuminate\Foundation\Http\FormRequest;

class StoreAiAvatarProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'slug' => 'required|string|unique:ai_avatar_profiles,slug|max:100',
            'role_title' => 'nullable|string|max:255',
            'persona_text' => 'nullable|string',
            'greeting_text' => 'nullable|string',
            'default_module_id' => 'nullable|exists:training_modules,id',
            'allowed_topics' => 'nullable|array',
            'avatar_model_path' => 'nullable|string',
            'voice_style' => 'nullable|string',
        ];
    }
}
