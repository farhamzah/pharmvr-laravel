<?php

namespace App\Http\Requests\Admin\Ai;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;
use App\Enums\SourceType;
use App\Enums\TrustLevel;

class StoreAiKnowledgeSourceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Use policy or admin check here
    }

    public function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'module_id' => 'nullable|exists:training_modules,id',
            'source_type' => ['required', new Enum(SourceType::class)],
            'trust_level' => ['nullable', new Enum(TrustLevel::class)],
            'description' => 'nullable|string',
            'category' => 'nullable|string',
            'topic' => 'nullable|string',
            'author' => 'nullable|string',
            'publisher' => 'nullable|string',
            'publication_year' => 'nullable|integer',
            'language' => 'nullable|string|max:10',
            'url' => 'required_if:source_type,web|nullable|url',
            'content' => 'required_if:source_type,manual|nullable|string',
            'file' => 'required_if:source_type,pdf,docx,txt,md|file', // Capacity freed for high-volume ingestion
        ];
    }
}
