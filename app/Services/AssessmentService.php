<?php

namespace App\Services;

use App\Models\Assessment;
use App\Models\QuestionBankItem;
use Illuminate\Support\Collection;

class AssessmentService
{
    /**
     * Generate a localized neural assessment payload for the student.
     * 
     * This method implements the randomization algorithm for both questions 
     * and options to prevent deterministic repetition in subsequent sessions.
     * 
     * @param Assessment $assessment The protocol node to evaluate.
     * @return array The neural payload for the student interface.
     */
    public function generateAssessment(Assessment $assessment): array
    {
        // 1. Fetch eligible active questions from the module's shared repository.
        // We filter by usage_scope (pretest, posttest, or both) and is_active status.
        $query = $assessment->trainingModule->questionBankItems()
            ->with(['options'])
            ->where('is_active', true)
            ->where(function($q) use ($assessment) {
                $q->where('usage_scope', $assessment->type->value)
                  ->orWhere('usage_scope', 'both');
            });

        $questions = $query->get();

        /*
         * 2. Question Randomization Logic:
         * If enabled in the assessment matrix, we use the Fisher-Yates shuffle algorithm 
         * (via Laravel Collection's shuffle) to provide an unpredictable question order.
         */
        if ($assessment->randomize_questions) {
            $questions = $questions->shuffle();
        }

        // 3. Constrain the payload to the configured question count.
        $questions = $questions->take($assessment->number_of_questions_to_take);

        // 4. Transform assets into a secure neural feed.
        $payload = $questions->map(function ($item) use ($assessment) {
            $options = $item->options;

            /*
             * 5. Option Shuffling Logic:
             * This ensures that even if a student recognizes a repeating question, 
             * the multiple-choice positions are randomized (A, B, C, D maps to different text).
             */
            if ($assessment->randomize_options) {
                $options = $options->shuffle();
            }

            return [
                'id' => $item->id,
                'question_text' => $item->question_text,
                'difficulty' => $item->difficulty,
                // Strategic explanation is suppressed during the active evaluation phase for security.
                'explanation' => null, 
                'options' => $options->map(function ($opt) {
                    return [
                        'id' => $opt->id,
                        'option_text' => $opt->option_text,
                        /* 
                         * SECURITY POLICY: NEVER expose the is_correct flag or correct_answer 
                         * in the student API feed to prevent client-side inspection vulnerabilities.
                         */
                    ];
                }),
            ];
        });

        return [
            'assessment_id' => $assessment->id,
            'title' => $assessment->title,
            'description' => $assessment->description,
            'type' => $assessment->type->value,
            'time_limit_minutes' => $assessment->time_limit_minutes,
            'passing_score' => $assessment->passing_score,
            'questions_count' => $payload->count(),
            'questions' => $payload->values()->toArray(),
        ];
    }

    /**
     * Start a new assessment attempt for a user.
     */
    public function startAttempt($userId, Assessment $assessment): \App\Models\AssessmentAttempt
    {
        return \App\Models\AssessmentAttempt::create([
            'user_id' => $userId,
            'assessment_id' => $assessment->id,
            'status' => 'in_progress',
            'started_at' => now(),
        ]);
    }

    /**
     * Submit an assessment attempt and calculate score.
     */
    public function submitAttempt(\App\Models\AssessmentAttempt $attempt, array $answers): \App\Models\AssessmentAttempt
    {
        $assessment = $attempt->assessment;
        $totalQuestions = $assessment->number_of_questions_to_take;
        $correctCount = 0;

        foreach ($answers as $answer) {
            $questionId = $answer['question_id'];
            $optionId   = $answer['option_id'];

            $option = \App\Models\QuestionBankOption::find($optionId);
            if ($option && $option->is_correct) {
                $correctCount++;
            }

            \App\Models\UserAnswer::create([
                'assessment_attempt_id' => $attempt->id,
                'question_id'           => $questionId,
                'option_id'             => $optionId,
            ]);
        }

        $score = ($totalQuestions > 0) ? (int) round(($correctCount / $totalQuestions) * 100) : 0;

        // Same grading formula for both Pre-Test and Post-Test.
        // Pre-Test: status reflects actual score, but progression is NOT blocked.
        $passed = $score >= $assessment->passing_score;

        $attempt->update([
            'score' => $score,
            'status' => $passed ? 'completed' : 'failed',
            'passed' => $passed,
            'completed_at' => now(),
        ]);

        // Auto-sync UserTrainingProgress to unlock the next journey step.
        $this->syncTrainingProgress($attempt, $assessment, $passed);

        return $attempt;
    }

    /**
     * Sync the user's training journey progress after an assessment submission.
     */
    protected function syncTrainingProgress(\App\Models\AssessmentAttempt $attempt, Assessment $assessment, bool $passed): void
    {
        $progress = \App\Models\UserTrainingProgress::firstOrCreate(
            [
                'user_id' => $attempt->user_id,
                'training_module_id' => $assessment->module_id,
            ],
            [
                'pre_test_status' => 'available',
                'vr_status' => 'locked',
                'post_test_status' => 'locked',
                'status' => 'in_progress',
                'completion_percentage' => 0,
            ]
        );

        $isPreTest = $assessment->type === \App\Enums\AssessmentType::PRETEST;

        if ($isPreTest) {
            // Pre-Test: status reflects actual score, but always unlocks VR Sim.
            // User can proceed regardless of score — Pre-Test is non-binding.
            $progress->update([
                'pre_test_status' => $passed ? 'passed' : 'failed',
                'vr_status' => 'available',
                'last_active_step' => 'vr_sim',
                'last_accessed_at' => now(),
            ]);
        } else {
            // Post-Test: update based on actual pass/fail result.
            $progress->update([
                'post_test_status' => $passed ? 'passed' : 'failed',
                'last_active_step' => 'post_test',
                'last_accessed_at' => now(),
                'completion_percentage' => $passed ? 100 : $progress->completion_percentage,
                'status' => $passed ? 'completed' : $progress->status,
            ]);
        }
    }
}
