<?php

namespace App\Http\Requests\Api\V1\Assessment;

use Illuminate\Foundation\Http\FormRequest;

class SubmitAssessmentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'answers'               => 'required|array',
            'answers.*.question_id' => 'required|exists:question_bank_items,id',
            'answers.*.option_id'   => 'required|exists:question_bank_options,id',
        ];
    }
}
