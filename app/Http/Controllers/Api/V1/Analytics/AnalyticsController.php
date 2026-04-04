<?php

namespace App\Http\Controllers\Api\V1\Analytics;

use App\Http\Controllers\Controller;
use App\Models\SessionAnalytics;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;

class AnalyticsController extends Controller
{
    use ApiResponse;

    /**
     * Get analytics overview for the current user.
     */
    public function overview(Request $request)
    {
        $user = $request->user();
        
        $sessionsCount = $user->vrSessions()->where('session_status', 'completed')->count();
        
        // Sum duration from SessionAnalytics
        $totalSeconds = SessionAnalytics::whereHas('vrSession', function ($q) use ($user) {
            $q->where('user_id', $user->id);
        })->sum('duration_seconds');
        
        $totalMinutes = floor($totalSeconds / 60);
        
        $averageScore = floor(SessionAnalytics::whereHas('vrSession', function ($q) use ($user) {
            $q->where('user_id', $user->id);
        })->avg('total_score') ?? 0);

        $recentAnalytics = SessionAnalytics::with(['vrSession.trainingModule'])
            ->whereHas('vrSession', function ($q) use ($user) {
                $q->where('user_id', $user->id);
            })
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        return $this->successResponse([
            'summary' => [
                'total_sessions' => $sessionsCount,
                'total_learning_minutes' => $totalMinutes,
                'average_score' => $averageScore,
                'achievements_count' => $user->achievements()->count(),
            ],
            'recent_sessions' => $recentAnalytics,
        ], 'Analytics overview retrieved.');
    }

    /**
     * Show detailed analytics for a specific session.
     */
    public function sessionDetail(Request $request, SessionAnalytics $analytics)
    {
        $this->authorize('view', $analytics->vrSession);

        $analytics->load(['vrSession.trainingModule']);

        return $this->successResponse($analytics, 'Session analytics detail retrieved.');
    }

    /**
     * List user achievements.
     */
    public function achievements(Request $request)
    {
        $achievements = $request->user()->achievements()
            ->orderBy('earned_at', 'desc')
            ->get();

        return $this->successResponse($achievements, 'User achievements retrieved.');
    }
}
