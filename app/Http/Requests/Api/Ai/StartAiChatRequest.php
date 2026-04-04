<?php

namespace App\Http\Requests\Api\Ai;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;
use App\Enums\ChatPlatform;

class StartAiChatRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'module_id' => 'nullable|exists:training_modules,id',
            'platform' => ['required', new Enum(ChatPlatform::class)],
            'session_title' => 'nullable|string|max:255',
            'assistant_mode' => 'nullable|string|max:100',
        ];
    }
}
