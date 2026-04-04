<?php

namespace App\Services\Analytics;

use App\Models\VrSession;
use App\Models\SessionAnalytics;

class SessionSummaryService
{
    /**
     * Summarize a VR session after completion.
     */
    public function summarize(VrSession $session): SessionAnalytics
    {
        $session->load(['events', 'stageResults']);

        $events = $session->events;
        $stageResults = $session->stageResults;

        // 1. Calculate Breach Count
        $breachCount = $events->where('event_type', 'sterile_breach')->count();

        // 2. Calculate Duration
        $durationSeconds = 0;
        if ($session->started_at && $session->completed_at) {
            $durationSeconds = $session->started_at->diffInSeconds($session->completed_at);
        }

        // 3. Calculate Scores (Simplified logic)
        // Accuracy: Baseline 100, -10 per breach (min 0)
        $accuracyScore = max(0, 100 - ($breachCount * 10));
        
        // Speed: Baseline 100, -1 per 30s over 5 mins (min 0)
        $speedScore = max(0, 100 - floor(max(0, $durationSeconds - 300) / 30));

        // Total Score: Average (weighted)
        $totalScore = floor(($accuracyScore * 0.7) + ($speedScore * 0.3));

        // 4. Persistence
        return SessionAnalytics::updateOrCreate(
            ['vr_session_id' => $session->id],
            [
                'total_score' => $totalScore,
                'accuracy_score' => $accuracyScore,
                'speed_score' => $speedScore,
                'breach_count' => $breachCount,
                'duration_seconds' => $durationSeconds,
                'completed_steps' => $session->current_step,
                'total_steps' => $session->trainingModule->total_steps ?? 10, // Mock fallback
                'metrics_json' => [
                    'stage_breakdown' => $stageResults->toArray(),
                ]
            ]
        );
    }
}
