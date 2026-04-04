<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use App\Enums\AssessmentStatus;
use App\Enums\AssessmentType;
use Illuminate\Validation\Rules\Enum;
use App\Models\QuestionBankItem;
use App\Enums\QuestionUsageScope;

class StoreAssessmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'module_id' => 'required|exists:training_modules,id',
            'type' => ['required', new Enum(AssessmentType::class)],
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'status' => ['required', new Enum(AssessmentStatus::class)],
            'number_of_questions_to_take' => 'required|integer|min:1',
            'randomize_questions' => 'boolean',
            'randomize_options' => 'boolean',
            'passing_score' => 'required|integer|min:0|max:100',
            'time_limit_minutes' => 'nullable|integer|min:1',
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            if ($this->status === AssessmentStatus::ACTIVE->value) {
                $count = QuestionBankItem::where('module_id', $this->module_id)
                    ->where('is_active', true)
                    ->where(function($q) {
                        $q->where('usage_scope', $this->type)
                          ->orWhere('usage_scope', QuestionUsageScope::BOTH->value);
                    })
                    ->count();

                if ($count < $this->number_of_questions_to_take) {
                    $validator->errors()->add('status', "Cannot activate assessment. Only {$count} active eligible questions found, but {$this->number_of_questions_to_take} required.");
                }
            }
        });
    }
}
