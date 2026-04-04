<?php

namespace App\Http\Requests\Api\Ai;

use Illuminate\Foundation\Http\FormRequest;

class AskAiChatRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'question' => 'required|string|min:3|max:2000',
            'session_id' => 'required|exists:ai_chat_sessions,id',
            'assistant_mode' => 'nullable|string|in:gmp_expert,training_support,lab_procedures',
            'platform' => 'nullable|string',
            'module_id' => 'nullable|exists:training_modules,id',
            'scene_context' => 'nullable|string',
            'object_context' => 'nullable|string',
        ];
    }
}
