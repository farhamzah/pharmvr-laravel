<?php

namespace App\Services\Analytics;

use App\Models\User;
use App\Models\TrainingModule;
use App\Models\SessionAnalytics;
use App\Models\UserAchievement;

class ProgressService
{
    /**
     * Get unified progress for a user.
     */
    public function getUnifiedProgress(User $user)
    {
        $totalModules = TrainingModule::where('is_active', true)->count();
        
        $completedModulesCount = SessionAnalytics::whereHas('vrSession', function ($q) use ($user) {
            $q->where('user_id', $user->id);
        })
        ->join('vr_sessions', 'session_analytics.vr_session_id', '=', 'vr_sessions.id')
        ->select('vr_sessions.training_module_id')
        ->distinct()
        ->count();

        $achievementsCount = $user->achievements()->count();
        
        $totalSessions = $user->vrSessions()->count();
        
        $averageAccuracy = SessionAnalytics::whereHas('vrSession', function ($q) use ($user) {
            $q->where('user_id', $user->id);
        })->avg('accuracy_score') ?? 0;

        return [
            'completion_stats' => [
                'total_modules' => $totalModules,
                'completed_modules' => $completedModulesCount,
                'progress_percentage' => $totalModules > 0 ? round(($completedModulesCount / $totalModules) * 100, 1) : 0,
            ],
            'activity_summary' => [
                'total_sessions' => $totalSessions,
                'total_achievements' => $achievementsCount,
                'average_accuracy' => round($averageAccuracy, 1),
            ],
            'recent_achievements' => $user->achievements()
                ->orderBy('earned_at', 'desc')
                ->limit(3)
                ->get()
        ];
    }
}
