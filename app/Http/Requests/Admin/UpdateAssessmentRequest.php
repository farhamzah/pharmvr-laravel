<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use App\Enums\AssessmentStatus;
use App\Enums\AssessmentType;
use Illuminate\Validation\Rules\Enum;
use App\Models\QuestionBankItem;
use App\Enums\QuestionUsageScope;

class UpdateAssessmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'status' => ['sometimes', 'required', new Enum(AssessmentStatus::class)],
            'number_of_questions_to_take' => 'sometimes|required|integer|min:1',
            'randomize_questions' => 'boolean',
            'randomize_options' => 'boolean',
            'passing_score' => 'sometimes|required|integer|min:0|max:100',
            'time_limit_minutes' => 'nullable|integer|min:1',
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $assessment = $this->route('assessment');
            
            $status = $this->status ?? $assessment->status->value;
            $questionsToTake = $this->number_of_questions_to_take ?? $assessment->number_of_questions_to_take;
            $moduleId = $assessment->module_id;
            $type = $assessment->type->value;

            if ($status === AssessmentStatus::ACTIVE->value) {
                $count = QuestionBankItem::where('module_id', $moduleId)
                    ->where('is_active', true)
                    ->where(function($q) use ($type) {
                        $q->where('usage_scope', $type)
                          ->orWhere('usage_scope', QuestionUsageScope::BOTH->value);
                    })
                    ->count();

                if ($count < $questionsToTake) {
                    $validator->errors()->add('status', "Cannot activate assessment. Only {$count} active eligible questions found, but {$questionsToTake} required.");
                }
            }
        });
    }
}
