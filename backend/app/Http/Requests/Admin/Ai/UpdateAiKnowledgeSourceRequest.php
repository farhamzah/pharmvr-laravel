<?php

namespace App\Http\Requests\Admin\Ai;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;
use App\Enums\SourceType;
use App\Enums\TrustLevel;

class UpdateAiKnowledgeSourceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => 'sometimes|string|max:255',
            'module_id' => 'sometimes|nullable|exists:training_modules,id',
            'source_type' => ['sometimes', new Enum(SourceType::class)],
            'trust_level' => ['sometimes', new Enum(TrustLevel::class)],
            'description' => 'sometimes|nullable|string',
            'category' => 'sometimes|nullable|string',
            'topic' => 'sometimes|nullable|string',
            'author' => 'sometimes|nullable|string',
            'publisher' => 'sometimes|nullable|string',
            'publication_year' => 'sometimes|nullable|integer',
            'language' => 'sometimes|nullable|string|max:10',
            'url' => 'sometimes|nullable|url',
            'content' => 'sometimes|nullable|string',
            'is_active' => 'sometimes|boolean',
            'file' => 'sometimes|file|max:51200',
        ];
    }
}
