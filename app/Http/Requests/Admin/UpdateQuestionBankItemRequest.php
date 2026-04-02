<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use App\Enums\QuestionUsageScope;
use Illuminate\Validation\Rules\Enum;

class UpdateQuestionBankItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'question_text' => 'sometimes|required|string',
            'usage_scope' => ['sometimes', 'required', new Enum(QuestionUsageScope::class)],
            'difficulty' => 'nullable|string',
            'explanation' => 'nullable|string',
            'is_active' => 'boolean',
            'options' => 'sometimes|required|array|size:4',
            'options.*' => 'required|string',
            'correct_option' => 'sometimes|required|string|in:A,B,C,D',
        ];
    }
}
