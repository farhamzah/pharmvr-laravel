<?php

namespace App\Services\Analytics;

use App\Models\User;
use App\Models\SessionAnalytics;
use App\Models\TrainingModule;
use Illuminate\Support\Facades\DB;

class LeaderboardService
{
    /**
     * Get global leaderboard (highest total scores).
     */
    public function getGlobalLeaderboard(int $limit = 10)
    {
        return DB::table('session_analytics')
            ->join('vr_sessions', 'session_analytics.vr_session_id', '=', 'vr_sessions.id')
            ->join('users', 'vr_sessions.user_id', '=', 'users.id')
            ->select(
                'users.id',
                'users.name',
                DB::raw('MAX(session_analytics.total_score) as top_score'),
                DB::raw('SUM(session_analytics.total_score) as cumulative_score')
            )
            ->groupBy('users.id', 'users.name')
            ->orderBy('top_score', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get leaderboard for a specific training module.
     */
    public function getModuleLeaderboard(TrainingModule $module, int $limit = 10)
    {
        return DB::table('session_analytics')
            ->join('vr_sessions', 'session_analytics.vr_session_id', '=', 'vr_sessions.id')
            ->join('users', 'vr_sessions.user_id', '=', 'users.id')
            ->where('vr_sessions.training_module_id', $module->id)
            ->select(
                'users.id',
                'users.name',
                DB::raw('MAX(session_analytics.total_score) as top_score'),
                DB::raw('MIN(session_analytics.duration_seconds) as best_time')
            )
            ->groupBy('users.id', 'users.name')
            ->orderBy('top_score', 'desc')
            ->orderBy('best_time', 'asc')
            ->limit($limit)
            ->get();
    }
}
