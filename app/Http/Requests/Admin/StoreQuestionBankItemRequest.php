<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use App\Enums\QuestionUsageScope;
use Illuminate\Validation\Rules\Enum;

class StoreQuestionBankItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'question_text' => 'required|string',
            'usage_scope' => ['required', new Enum(QuestionUsageScope::class)],
            'difficulty' => 'nullable|string',
            'explanation' => 'nullable|string',
            'is_active' => 'boolean',
            'options' => 'required|array|size:4',
            'options.*' => 'required|string',
            'correct_option' => 'required|string|in:A,B,C,D',
        ];
    }

    public function messages(): array
    {
        return [
            'options.size' => 'Exactly 4 options are required.',
            'correct_option_index.required' => 'Please select exactly 1 correct answer.',
        ];
    }
}
