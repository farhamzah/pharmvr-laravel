<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\TrainingModule;
use App\Models\UserTrainingProgress;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportingController extends Controller
{
    /**
     * Display a summary of training progress.
     */
    public function trainingProgress(Request $request)
    {
        $query = User::role('student')->with(['trainingProgress', 'profile']);

        if ($request->has('search')) {
            $query->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('email', 'like', '%' . $request->search . '%');
        }

        $students = $query->paginate(15);
        $modules = TrainingModule::all();

        return view('admin.reporting.training-progress', compact('students', 'modules'));
    }

    /**
     * Show detailed progress for a specific user.
     */
    public function userReport(User $user)
    {
        $user->load(['trainingProgress.module', 'assessmentAttempts.assessment', 'profile']);
        
        return view('admin.reporting.user-report', compact('user'));
    }

    /**
     * Display AI interaction analytics.
     */
    public function aiAnalytics()
    {
        $interactionStats = \App\Models\AiUsageLog::select('interaction_type', DB::raw('count(*) as count'))
            ->groupBy('interaction_type')
            ->get();
            
        $recentInteractions = \App\Models\AiUsageLog::with('user')->latest()->limit(20)->get();
        
        return view('admin.reporting.ai-analytics', compact('interactionStats', 'recentInteractions'));
    }

    /**
     * Display assessment results report for all users.
     */
    public function assessmentReport(Request $request)
    {
        $query = \App\Models\AssessmentAttempt::with(['user', 'assessment.trainingModule'])
            ->whereNotNull('completed_at');

        // Filters
        if ($request->filled('module_id')) {
            $query->whereHas('assessment', fn($q) => $q->where('module_id', $request->module_id));
        }
        if ($request->filled('type')) {
            $query->whereHas('assessment', fn($q) => $q->where('type', $request->type));
        }
        if ($request->filled('status')) {
            if ($request->status === 'passed') {
                $query->where('passed', true);
            } elseif ($request->status === 'failed') {
                $query->where('passed', false);
            }
        }
        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('user', fn($q) => $q->where('name', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%"));
        }

        $attempts = $query->latest('completed_at')->paginate(20)->withQueryString();
        $modules = TrainingModule::all();

        // Global stats
        $totalAttempts = \App\Models\AssessmentAttempt::whereNotNull('completed_at')->count();
        $passedCount = \App\Models\AssessmentAttempt::whereNotNull('completed_at')->where('passed', true)->count();
        $avgScore = \App\Models\AssessmentAttempt::whereNotNull('completed_at')->avg('score') ?? 0;
        
        // Pre-test vs Post-test stats
        $preTestAvg = \App\Models\AssessmentAttempt::whereNotNull('completed_at')
            ->whereHas('assessment', fn($q) => $q->where('type', 'pretest'))->avg('score') ?? 0;
        $postTestAvg = \App\Models\AssessmentAttempt::whereNotNull('completed_at')
            ->whereHas('assessment', fn($q) => $q->where('type', 'posttest'))->avg('score') ?? 0;
        
        $uniqueStudents = \App\Models\AssessmentAttempt::whereNotNull('completed_at')
            ->distinct('user_id')->count('user_id');

        return view('admin.reporting.assessment-report', compact(
            'attempts', 'modules', 'totalAttempts', 'passedCount', 'avgScore',
            'preTestAvg', 'postTestAvg', 'uniqueStudents'
        ));
    }

    /**
     * Export assessment report as CSV.
     */
    public function exportAssessmentCsv(Request $request)
    {
        $query = \App\Models\AssessmentAttempt::with(['user', 'assessment.trainingModule'])
            ->whereNotNull('completed_at');

        if ($request->filled('module_id')) {
            $query->whereHas('assessment', fn($q) => $q->where('module_id', $request->module_id));
        }
        if ($request->filled('type')) {
            $query->whereHas('assessment', fn($q) => $q->where('type', $request->type));
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('search')) {
            $query->whereHas('user', fn($q) => $q->where('name', 'like', '%' . $request->search . '%'));
        }

        $attempts = $query->latest('completed_at')->get();

        $filename = 'assessment_report_' . now()->format('Y-m-d_H-i') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($attempts) {
            $file = fopen('php://output', 'w');
            // BOM for Excel UTF-8 compatibility
            fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF));
            fputcsv($file, ['No', 'Nama User', 'Email', 'Modul', 'Tipe', 'Skor', 'Status', 'Lulus', 'Waktu Mulai', 'Waktu Selesai', 'Durasi (menit)']);

            foreach ($attempts as $i => $attempt) {
                $duration = $attempt->started_at && $attempt->completed_at
                    ? round($attempt->started_at->diffInMinutes($attempt->completed_at), 1)
                    : 0;

                fputcsv($file, [
                    $i + 1,
                    $attempt->user->name ?? '-',
                    $attempt->user->email ?? '-',
                    $attempt->assessment->trainingModule->title ?? '-',
                    $attempt->assessment->type->value ?? '-',
                    $attempt->score ?? 0,
                    $attempt->status,
                    $attempt->passed ? 'Ya' : 'Tidak',
                    $attempt->started_at?->format('d/m/Y H:i'),
                    $attempt->completed_at?->format('d/m/Y H:i'),
                    $duration,
                ]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Export assessment report as PDF (HTML-based).
     */
    public function exportAssessmentPdf(Request $request)
    {
        $query = \App\Models\AssessmentAttempt::with(['user', 'assessment.trainingModule'])
            ->whereNotNull('completed_at');

        if ($request->filled('module_id')) {
            $query->whereHas('assessment', fn($q) => $q->where('module_id', $request->module_id));
        }
        if ($request->filled('type')) {
            $query->whereHas('assessment', fn($q) => $q->where('type', $request->type));
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $attempts = $query->latest('completed_at')->get();
        $totalAttempts = $attempts->count();
        $passedCount = $attempts->where('passed', true)->count();
        $avgScore = $attempts->avg('score');

        return view('admin.reporting.assessment-report-pdf', compact('attempts', 'totalAttempts', 'passedCount', 'avgScore'));
    }
}
