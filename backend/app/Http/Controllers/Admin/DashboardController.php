<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\VrSession;
use App\Models\TrainingModule;
use App\Models\AssessmentAttempt;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // 1. Core KPIs
        $totalUsers = User::count();
        $activeVrSessions = VrSession::whereIn('session_status', ['playing', 'starting'])
            ->where('last_activity_at', '>=', now()->subMinutes(15))
            ->count();
        $totalModules = TrainingModule::count();

        // 2. Success Rates (Calculated from Assessment attempts)
        // Assuming AssessmentAttempt model exists (checked in previous turns)
        $avgScore = DB::table('assessment_attempts')->avg('score') ?? 0;
        $passCount = DB::table('assessment_attempts')->where('passed', true)->count();
        $totalAttempts = DB::table('assessment_attempts')->count();
        $passRate = $totalAttempts > 0 ? ($passCount / $totalAttempts) * 100 : 0;

        // 3. Recent Activity (Audit logs & Sessions)
        $recentAuditLogs = AuditLog::with('user')->latest()->take(5)->get();
        $recentVrSessions = VrSession::with('user')->latest()->take(5)->get();

        // 4. VR Trends (Grouped by status for a chart-like breakdown)
        $sessionStats = VrSession::select('session_status', DB::raw('count(*) as total'))
            ->groupBy('session_status')
            ->get()
            ->pluck('total', 'session_status');

        // 4.1. 7-Day Activity Trend (Phase 4 Dashboard Enhancement)
        $last7Days = collect(range(6, 0))->map(function ($daysAgo) {
            $date = now()->subDays($daysAgo)->format('Y-m-d');
            return [
                'date' => now()->subDays($daysAgo)->format('d M'),
                'vr_sessions' => VrSession::whereDate('started_at', $date)->count(),
                'assessments' => DB::table('assessment_attempts')->whereDate('created_at', $date)->count(),
                'registrations' => User::whereDate('created_at', $date)->count(),
                'ai_usage' => DB::table('ai_usage_logs')->whereDate('created_at', $date)->count(),
            ];
        });

        // 5. System Health Check
        $health = [
            'database' => true,
            'storage' => is_writable(storage_path('app/public')),
            'uptime' => '99.9%', // Mocked for now
        ];

        try {
            DB::connection()->getPdo();
        } catch (\Exception $e) {
            $health['database'] = false;
        }

        return view('admin.dashboard', compact(
            'totalUsers',
            'activeVrSessions',
            'totalModules',
            'passRate',
            'avgScore',
            'recentAuditLogs',
            'recentVrSessions',
            'sessionStats',
            'last7Days',
            'health'
        ));
    }
}
