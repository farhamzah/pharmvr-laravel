<?php

namespace App\Http\Resources\Api\V1\Assessment;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class QuestionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        // Try to get selected option for this question from the attempt_id context
        $selectedOptionId = null;
        if ($attemptId = $request->route('attempt')) {
            $selectedOptionId = \App\Models\UserAnswer::where('assessment_attempt_id', $attemptId)
                ->where('question_id', $this->id)
                ->value('option_id');
        }

        return [
            'id'                 => $this->id,
            'question_number'    => $this->order ?? $this->id,
            'question_text'      => $this->question_text,
            'image_url'          => $this->image_url,
            'explanation'        => $this->explanation, // This can be null until results are shown
            'selected_option_id' => $selectedOptionId,
            'options'            => OptionResource::collection($this->options),
        ];
    }
}
