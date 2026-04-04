<?php

namespace App\Http\Resources\Api\V1\Assessment;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AssessmentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $user = $request->user();
        $latestAttempt = $this->attempts()->where('user_id', $user->id)->latest()->first();
        $highScore = $this->attempts()->where('user_id', $user->id)->max('score') ?? 0;
        $hasPassed = $this->attempts()->where('user_id', $user->id)->where('status', 'passed')->exists();

        // VR Completion Gating for Post-Test
        $isSimulationCompleted = \App\Models\UserTrainingProgress::where('user_id', $user->id)
            ->where('training_module_id', $this->module_id)
            ->where('status', 'simulation_completed')
            ->exists();

        $isEligible = true;
        $eligibilityMessage = null;

        if ($this->type === \App\Enums\AssessmentType::POSTTEST && !$isSimulationCompleted) {
            $isEligible = false;
            $eligibilityMessage = 'Anda harus menyelesaikan Simulasi VR terlebih dahulu sebelum mengambil Post-Test.';
        }

        return [
            'id'                 => $this->id,
            'type'               => $this->type,
            'title'              => $this->title,
            'description'        => $this->description,
            'total_questions'    => $this->questions_count ?? $this->questions()->count(),
            'estimated_duration' => $this->time_limit_minutes . ' menit',
            'passing_score'      => $this->min_score,
            'module_summary'     => [
                'slug'  => $this->trainingModule->slug,
                'title' => $this->trainingModule->title,
            ],
            'attempt_info'       => [
                'has_previous_attempt' => (bool)$latestAttempt,
                'latest_score'         => $latestAttempt?->score,
                'highest_score'        => $highScore,
                'status'               => $latestAttempt?->status ?? 'not_started',
                'has_passed'           => $hasPassed,
                'vr_completed'         => $isSimulationCompleted,
            ],
            'is_eligible'        => $isEligible,
            'eligibility_message'=> $eligibilityMessage,
            'can_start'          => $isEligible && (!$hasPassed || $this->type === 'post_test'),
            'recommended_action' => $this->getRecommendedAction($hasPassed, $latestAttempt, $isEligible),
        ];
    }

    private function getRecommendedAction($hasPassed, $latestAttempt, $isEligible)
    {
        if (!$isEligible) {
            return 'Selesaikan Simulasi VR';
        }

        if (!$latestAttempt) {
            return $this->type === \App\Enums\AssessmentType::PRETEST ? 'Mulai Pre-Test' : 'Mulai Post-Test';
        }

        if ($hasPassed) {
            return $this->type === \App\Enums\AssessmentType::PRETEST ? 'Lanjut ke Simulasi' : 'Selesaikan Pelatihan';
        }

        return 'Ulangi Assessment';
    }
}
