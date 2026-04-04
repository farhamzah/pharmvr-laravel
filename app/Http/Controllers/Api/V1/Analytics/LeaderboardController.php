<?php

namespace App\Http\Controllers\Api\V1\Analytics;

use App\Http\Controllers\Controller;
use App\Models\TrainingModule;
use App\Services\Analytics\LeaderboardService;
use App\Services\Analytics\ProgressService;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;

class LeaderboardController extends Controller
{
    use ApiResponse;

    protected $leaderboardService;
    protected $progressService;

    public function __construct(LeaderboardService $leaderboardService, ProgressService $progressService)
    {
        $this->leaderboardService = $leaderboardService;
        $this->progressService = $progressService;
    }

    /**
     * Get global leaderboard.
     */
    public function global()
    {
        $rankings = $this->leaderboardService->getGlobalLeaderboard();
        return $this->successResponse($rankings, 'Global leaderboard retrieved.');
    }

    /**
     * Get leaderboard for a specific module.
     */
    public function module(TrainingModule $module)
    {
        $rankings = $this->leaderboardService->getModuleLeaderboard($module);
        return $this->successResponse([
            'module' => [
                'id' => $module->id,
                'title' => $module->title,
                'slug' => $module->slug,
            ],
            'rankings' => $rankings
        ], 'Module leaderboard retrieved.');
    }

    /**
     * Get user unified progress summary.
     */
    public function userProgress(Request $request)
    {
        $progress = $this->progressService->getUnifiedProgress($request->user());
        return $this->successResponse($progress, 'User progress summary retrieved.');
    }
}
