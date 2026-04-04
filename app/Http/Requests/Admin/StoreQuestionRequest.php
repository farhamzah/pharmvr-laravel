<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreQuestionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): bool|array
    {
        return [
            'question_text' => 'required|string',
            'usage_scope' => 'required|in:pretest,posttest,both',
            'difficulty' => 'nullable|string|max:50',
            'explanation' => 'nullable|string',
            'is_active' => 'boolean',
            'options' => 'required|array|size:4',
            'options.*.option_text' => 'required|string',
            'options.*.is_correct' => 'boolean',
            'correct_option_index' => 'required|integer|min:0|max:3',
        ];
    }

    public function messages(): array
    {
        return [
            'options.size' => 'Setiap pertanyaan harus memiliki tepat 4 opsi jawaban.',
            'correct_option_index.required' => 'Mohon pilih satu jawaban yang benar.',
        ];
    }
}
