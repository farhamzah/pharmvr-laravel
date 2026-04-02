<?php

namespace App\Services\Analytics;

use App\Models\User;
use App\Models\VrSession;
use App\Models\SessionAnalytics;
use App\Models\UserAchievement;

class AchievementService
{
    /**
     * Check and award achievements after session completion.
     */
    public function evaluateAfterSession(VrSession $session, SessionAnalytics $analytics): array
    {
        $user = $session->user;
        $awarded = [];

        // 1. First Session
        if (!$user->achievements()->where('achievement_slug', 'first-session')->exists()) {
            $awarded[] = $this->award($user, 'first-session', ['session_id' => $session->id]);
        }

        // 2. Sterile Pro (No breaches)
        if ($analytics->breach_count === 0) {
            $awarded[] = $this->award($user, 'sterile-pro', ['session_id' => $session->id]);
        }

        // 3. Speed Demon (< 3 mins)
        if ($analytics->duration_seconds < 180) {
            $awarded[] = $this->award($user, 'speed-demon', ['session_id' => $session->id]);
        }

        // 4. Perfect Score (100)
        if ($analytics->total_score === 100) {
            $awarded[] = $this->award($user, 'perfect-score', ['session_id' => $session->id]);
        }

        return array_filter($awarded);
    }

    protected function award(User $user, string $slug, array $meta = [])
    {
        // Don't duplicate unless repeatable (slug-based logic)
        if ($user->achievements()->where('achievement_slug', $slug)->exists()) {
            return null;
        }

        return $user->achievements()->create([
            'achievement_slug' => $slug,
            'metadata' => $meta,
            'earned_at' => now(),
        ]);
    }
}
