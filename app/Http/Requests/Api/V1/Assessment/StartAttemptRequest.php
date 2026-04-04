<?php

namespace App\Http\Requests\Api\V1\Assessment;

use Illuminate\Foundation\Http\FormRequest;

class StartAttemptRequest extends FormRequest
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
            'type'      => 'required|in:pre_test,post_test',
            'module_id' => 'required|exists:training_modules,id',
        ];
    }
}
