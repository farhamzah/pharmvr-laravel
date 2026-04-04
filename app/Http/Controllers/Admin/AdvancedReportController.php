<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\TrainingModule;
use App\Models\AssessmentAttempt;
use App\Models\UserTrainingProgress;
use App\Models\VrSession;
use App\Models\AiUsageLog;
use Illuminate\Support\Carbon;

class AdvancedReportController extends Controller
{
    /**
     * Reports Hub
     */
    public function hub()
    {
        return view('admin.advanced-reports.hub');
    }

    /**
     * Report 1: Pre-Test vs Post-Test Comparison
     */
    public function pretestPosttest(Request $request)
    {
        $moduleId = $request->get('module_id');
        
        $query = DB::table('assessment_attempts as post')
            ->join('assessments as a_post', 'post.assessment_id', '=', 'a_post.id')
            ->join('training_modules as tm', 'a_post.training_module_id', '=', 'tm.id')
            ->join('users as u', 'post.user_id', '=', 'u.id')
            ->join('assessment_attempts as pre', function($join) {
                $join->on('pre.user_id', '=', 'post.user_id')
                     ->whereIn('pre.assessment_id', function($q) {
                         $q->select('id')->from('assessments')->whereColumn('training_module_id', 'a_post.training_module_id')->where('type', 'pretest');
                     });
            })
            ->where('a_post.type', 'posttest')
            ->where('post.status', 'passed');
            
        if ($moduleId) {
            $query->where('tm.id', $moduleId);
        }

        $results = $query->select(
                'tm.id as module_id',
                'tm.title as module_title',
                'u.name as user_name',
                'u.email as user_email',
                'pre.score as pretest_score',
                'post.score as posttest_score',
                DB::raw('post.score - pre.score as score_gain')
            )
            ->get();

        // Calculate aggregates for charts
        $moduleStats = collect($results)->groupBy('module_id')->map(function($items) {
            $first = $items->first();
            return [
                'module_title' => $first->module_title,
                'avg_pretest' => round($items->avg('pretest_score'), 2),
                'avg_posttest' => round($items->avg('posttest_score'), 2),
                'avg_gain' => round($items->avg('score_gain'), 2)
            ];
        })->values();

        $modules = TrainingModule::pluck('title', 'id');

        return view('admin.advanced-reports.pretest-posttest', compact('results', 'moduleStats', 'modules'));
    }

    /**
     * Report 2: Question Analysis
     */
    public function questionAnalysis(Request $request)
    {
        $moduleId = $request->get('module_id');

        $query = DB::table('user_answers as ua')
            ->join('question_bank_items as qb', 'ua.question_bank_item_id', '=', 'qb.id')
            ->join('training_modules as tm', 'qb.training_module_id', '=', 'tm.id')
            ->select(
                'qb.id as question_id',
                'qb.question_text',
                'tm.title as module_title',
                DB::raw('COUNT(ua.id) as total_answers'),
                DB::raw('SUM(CASE WHEN ua.is_correct = 1 THEN 1 ELSE 0 END) as correct_answers')
            )
            ->groupBy('qb.id', 'qb.question_text', 'tm.title');

        if ($moduleId) {
            $query->where('tm.id', $moduleId);
        }

        $results = $query->get()->map(function($item) {
            $item->correct_rate = $item->total_answers > 0 
                ? round(($item->correct_answers / $item->total_answers) * 100, 2) 
                : 0;
            return $item;
        });

        // Generate chart data for questions
        $chartData = $results->sortBy('correct_rate')->take(20)->values();

        $modules = TrainingModule::pluck('title', 'id');

        return view('admin.advanced-reports.question-analysis', compact('results', 'chartData', 'modules'));
    }

    /**
     * Report 3: Completion Funnel
     */
    public function completionFunnel(Request $request)
    {
        $moduleId = $request->get('module_id');

        $query = UserTrainingProgress::query();
        
        if ($moduleId) {
            $query->where('training_module_id', $moduleId);
        }

        $allData = $query->get();
        $totalUsers = $allData->count();

        $funnelData = [
            'Total Enrolled' => $totalUsers,
            'Passed Pre-test' => $allData->where('pre_test_status', 'passed')->count(),
            'Completed VR Step' => $allData->where('vr_status', 'completed')->count(),
            'Passed Post-test' => $allData->where('post_test_status', 'passed')->count(),
            'Certified/Completed' => $allData->where('progress_percentage', 100)->count(),
        ];

        $modules = TrainingModule::pluck('title', 'id');

        return view('admin.advanced-reports.completion-funnel', compact('funnelData', 'modules', 'allData'));
    }

    /**
     * Report 4: VR Performance Dashboard
     */
    public function vrPerformance(Request $request)
    {
        $moduleId = $request->get('module_id');
        $query = VrSession::with(['analytics', 'trainingModule', 'user'])
            ->where('session_status', 'completed')
            ->has('analytics');

        if ($moduleId) {
            $query->where('training_module_id', $moduleId);
        }

        $sessions = $query->orderBy('completed_at', 'desc')->take(100)->get();

        $chartData = $sessions->groupBy('training_module_id')->map(function($items) {
            $mod = $items->first()->trainingModule;
            return [
                'module_title' => $mod ? $mod->title : 'Unknown',
                'avg_total_score' => round($items->avg('analytics.total_score'), 2),
                'avg_accuracy_score' => round($items->avg('analytics.accuracy_score'), 2),
                'avg_speed_score' => round($items->avg('analytics.speed_score'), 2),
                'avg_breaches' => round($items->avg('analytics.breach_count'), 2)
            ];
        })->values();

        $modules = TrainingModule::pluck('title', 'id');

        return view('admin.advanced-reports.vr-performance', compact('sessions', 'chartData', 'modules'));
    }

    /**
     * Report 5: AI Usage & Cost Monitor
     */
    public function aiUsage(Request $request)
    {
        $days = (int) $request->get('days', 30);
        $startDate = Carbon::now()->subDays($days);

        $logs = DB::table('ai_usage_logs')
            ->where('created_at', '>=', $startDate)
            ->get();

        $summary = [
            'total_interactions' => $logs->count(),
            'total_tokens' => $logs->sum('total_tokens'),
            'avg_latency' => round($logs->avg('latency_ms') ?: 0, 2),
            'flagged_responses' => $logs->where('is_safe_response', 0)->count(),
        ];

        $typeStats = $logs->groupBy('interaction_type')->map(fn($group) => $group->count());
        $modelStats = $logs->groupBy('model_name')->map(fn($group) => $group->count());

        $dailyTokens = DB::table('ai_usage_logs')
            ->select(DB::raw('DATE(created_at) as date'), DB::raw('SUM(total_tokens) as tokens'))
            ->where('created_at', '>=', $startDate)
            ->groupBy(DB::raw('DATE(created_at)'))
            ->orderBy('date')
            ->pluck('tokens', 'date');

        return view('admin.advanced-reports.ai-usage', compact('summary', 'typeStats', 'modelStats', 'dailyTokens'));
    }

    /**
     * Report 6: Trend Report
     */
    public function trends(Request $request)
    {
        $days = (int) $request->get('days', 30);
        $dates = collect();
        $startDate = Carbon::now()->subDays($days);

        for ($i = $days; $i >= 0; $i--) {
            $dates->push(Carbon::now()->subDays($i)->format('Y-m-d'));
        }

        $vrSessions = VrSession::select(DB::raw('DATE(started_at) as date'), DB::raw('COUNT(*) as count'))
            ->where('started_at', '>=', $startDate)
            ->groupBy('date')
            ->pluck('count', 'date');

        $aiUsage = DB::table('ai_usage_logs')
            ->select(DB::raw('DATE(created_at) as date'), DB::raw('COUNT(*) as count'))
            ->where('created_at', '>=', $startDate)
            ->groupBy('date')
            ->pluck('count', 'date');

        $assessments = AssessmentAttempt::select(DB::raw('DATE(created_at) as date'), DB::raw('COUNT(*) as count'))
            ->where('created_at', '>=', $startDate)
            ->groupBy('date')
            ->pluck('count', 'date');

        $trendData = $dates->map(function ($date) use ($vrSessions, $aiUsage, $assessments) {
            return [
                'date' => $date,
                'vr_sessions' => $vrSessions->get($date, 0),
                'ai_usage' => $aiUsage->get($date, 0),
                'assessments' => $assessments->get($date, 0),
            ];
        });

        return view('admin.advanced-reports.trends', compact('trendData', 'days'));
    }

    /**
     * Export CSV helper for any report
     */
    public function exportCsv(Request $request)
    {
        // Simple placeholder for export CSV
        $reportType = $request->get('type');
        return response()->json(['message' => "CSV generation for $reportType triggered", 'file' => 'example.csv']);
    }
}
