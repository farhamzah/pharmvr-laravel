<?php

namespace App\Http\Resources\Api\V1\Assessment;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AttemptResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $totalQuestions = $this->answers()->count() ?: ($this->assessment->number_of_questions_to_take ?? $this->assessment->questions()->count());
        $correctCount = $this->score ? round(($this->score / 100) * $totalQuestions) : 0;
        $incorrectCount = $totalQuestions - $correctCount;

        // Calculate time taken from timestamps
        $timeTakenSeconds = 0;
        if ($this->started_at && $this->completed_at) {
            $timeTakenSeconds = $this->completed_at->diffInSeconds($this->started_at);
        }

        // Calculate attempt number for this specific assessment
        $attemptNumber = \App\Models\AssessmentAttempt::where('user_id', $this->user_id)
            ->where('assessment_id', $this->assessment_id)
            ->where('id', '<=', $this->id)
            ->count();

        return [
            'id'            => $this->id,
            'assessment_id' => $this->assessment_id,
            'assessment_type' => $this->assessment->type,
            'module_summary' => [
                'id'    => $this->assessment->trainingModule->id,
                'slug'  => $this->assessment->trainingModule->slug,
                'title' => $this->assessment->trainingModule->title,
            ],
            'score'         => $this->score,
            'percentage'    => $this->score . '%',
            'passed'        => (bool) $this->passed,
            'status'        => $this->status,
            'attempt_number' => $attemptNumber,
            'started_at'    => $this->started_at?->toIso8601String(),
            'completed_at'  => $this->completed_at?->toIso8601String(),
            'summary'       => [
                'total_questions' => $totalQuestions,
                'correct_count'   => (int) $correctCount,
                'incorrect_count' => (int) $incorrectCount,
                'passing_score'   => $this->assessment->passing_score,
                'time_taken_seconds' => $timeTakenSeconds,
            ],
            'recommendation' => [
                'action' => $this->passed 
                    ? ($this->assessment->type === \App\Enums\AssessmentType::PRETEST ? 'Lanjut Simulasi VR' : 'Selesaikan Pelatihan')
                    : 'Ulangi Assessment',
                'route'  => $this->passed
                    ? ($this->assessment->type === \App\Enums\AssessmentType::PRETEST ? '/vr-launch' : '/home')
                    : '/assessment-intro',
            ],
            'journey_relevance' => $this->assessment->type === \App\Enums\AssessmentType::PRETEST 
                ? 'Pre-test ini diperlukan untuk memverifikasi kesiapan teknis Anda sebelum memasuki simulasi VR.'
                : 'Post-test ini memvalidasi pemahaman Anda setelah menyelesaikan sesi praktis di ruang steril.',
        ];
    }
}
