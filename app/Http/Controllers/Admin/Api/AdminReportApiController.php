<?php

namespace App\Http\Controllers\Admin\Api;

use App\Http\Controllers\Controller;
use App\Models\AssessmentAttempt;
use App\Models\QuestionBankItem;
use App\Models\QuestionBankOption;
use App\Models\TrainingModule;
use App\Models\User;
use App\Models\UserAnswer;
use App\Models\UserTrainingProgress;
use App\Models\VrSession;
use App\Models\SessionAnalytics;
use App\Models\AiUsageLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;
use Carbon\Carbon;
use Carbon\CarbonPeriod;

class AdminReportApiController extends Controller
{
    // ──────────────────────────────────────────────
    // 1. Pre-Test vs Post-Test Comparison
    // ──────────────────────────────────────────────
    public function pretestPosttest(Request $request): JsonResponse
    {
        $moduleId = $request->input('module_id');
        $period   = (string) $request->input('period', '90d');
        $since    = $this->periodToDate($period);

        /** @var \Illuminate\Database\Eloquent\Builder $attemptsQuery */
        $attemptsQuery = AssessmentAttempt::with(['user', 'assessment.trainingModule'])
            ->whereNotNull('completed_at');

        if ($since) {
            $attemptsQuery->where('completed_at', '>=', $since);
        }

        if ($moduleId) {
            $attemptsQuery->whereHas('assessment', function ($q) use ($moduleId) {
                /** @var \Illuminate\Database\Eloquent\Builder $q */
                $q->where('module_id', $moduleId);
            });
        }

        $attempts    = $attemptsQuery->get();
        $perStudent  = $this->getPerStudentProgression($attempts);
        $perModule   = $this->aggregatePerModuleProgression($perStudent);

        $paired = collect($perStudent)->filter(function (array $s) {
            return $s['pretest_score'] !== null && $s['posttest_score'] !== null;
        });

        return response()->json([
            'summary' => [
                'avg_pretest'       => round($paired->avg('pretest_score') ?? 0, 1),
                'avg_posttest'      => round($paired->avg('posttest_score') ?? 0, 1),
                'avg_learning_gain' => round($paired->avg('learning_gain') ?? 0, 1),
                'total_students'    => $paired->count(),
            ],
            'per_student' => $perStudent,
            'per_module'  => $perModule,
        ]);
    }

    private function aggregatePerModuleProgression(array $perStudent): Collection
    {
        return collect($perStudent)
            ->filter(fn($s) => $s['pretest_score'] !== null && $s['posttest_score'] !== null)
            ->groupBy('module_id')
            ->map(function ($students) {
                return [
                    'module_id'     => $students->first()['module_id'],
                    'module_title'  => $students->first()['module'],
                    'avg_pretest'   => round($students->avg('pretest_score'), 1),
                    'avg_posttest'  => round($students->avg('posttest_score'), 1),
                    'avg_gain'      => round($students->avg('learning_gain'), 1),
                    'student_count' => $students->count(),
                ];
            })->values();
    }

    // ──────────────────────────────────────────────
    // 2. Question Item Analysis
    // ──────────────────────────────────────────────
    public function questionAnalysis(Request $request): JsonResponse
    {
        $moduleId = $request->input('module_id');
        $type     = $request->input('type'); // pretest, posttest

        $query = QuestionBankItem::with(['options', 'trainingModule'])
            ->where('is_active', true);

        if ($moduleId) {
            $query->where('training_module_id', $moduleId);
        }
        if ($type) {
            $query->where(function ($q) use ($type) {
                $q->where('usage_scope', $type)->orWhere('usage_scope', 'both');
            });
        }

        $questions = $query->get();

        $questionsData = $questions->map(function ($question) {
            $totalAnswers  = UserAnswer::where('question_id', $question->id)->count();
            $correctOption = $question->options->firstWhere('is_correct', true);
            $correctCount  = 0;

            if ($correctOption) {
                $correctCount = UserAnswer::where('question_id', $question->id)
                    ->where('option_id', $correctOption->id)
                    ->count();
            }

            $correctRate = $totalAnswers > 0 ? round(($correctCount / $totalAnswers) * 100, 1) : 0;

            // Discrimination index (simplified)
            $discrimination = 'N/A';
            if ($totalAnswers >= 10) {
                if ($correctRate >= 80) $discrimination = 'easy';
                elseif ($correctRate >= 50) $discrimination = 'good';
                elseif ($correctRate >= 30) $discrimination = 'fair';
                else $discrimination = 'hard';
            }

            // Option distribution
            $optionsData = $question->options->map(function ($opt) use ($question, $totalAnswers) {
                $selCount = UserAnswer::where('question_id', $question->id)
                    ->where('option_id', $opt->id)
                    ->count();

                return [
                    'option_id'      => $opt->id,
                    'text'           => $opt->option_text,
                    'is_correct'     => (bool) $opt->is_correct,
                    'selection_count' => $selCount,
                    'selection_rate'  => $totalAnswers > 0 ? round(($selCount / $totalAnswers) * 100, 1) : 0,
                ];
            });

            return [
                'question_id'    => $question->id,
                'question_text'  => $question->question_text,
                'module'         => $question->trainingModule?->title ?? '-',
                'module_id'      => $question->training_module_id,
                'difficulty'     => $question->difficulty ?? 'medium',
                'usage_scope'    => $question->usage_scope,
                'times_answered' => $totalAnswers,
                'times_correct'  => $correctCount,
                'correct_rate'   => $correctRate,
                'discrimination' => $discrimination,
                'options'        => $optionsData,
            ];
        });

        $answered = $questionsData->filter(fn($q) => $q['times_answered'] > 0);
        $hardest  = $answered->sortBy('correct_rate')->first();
        $easiest  = $answered->sortByDesc('correct_rate')->first();

        return response()->json([
            'questions' => $questionsData->values(),
            'summary'   => [
                'total_questions'      => $questionsData->count(),
                'avg_correct_rate'     => round($answered->avg('correct_rate') ?? 0, 1),
                'hardest_question_id'  => $hardest['question_id'] ?? null,
                'hardest_correct_rate' => $hardest['correct_rate'] ?? null,
                'easiest_question_id'  => $easiest['question_id'] ?? null,
                'easiest_correct_rate' => $easiest['correct_rate'] ?? null,
            ],
        ]);
    }

    // ──────────────────────────────────────────────
    // 3. Completion Funnel
    // ──────────────────────────────────────────────
    public function completionFunnel(Request $request): JsonResponse
    {
        $moduleId = $request->input('module_id');

        $modulesQuery = TrainingModule::where('is_active', true);
        if ($moduleId) {
            $modulesQuery->where('id', $moduleId);
        }
        $modules = $modulesQuery->get();

        $funnelData = $modules->map(function ($module) {
            $progress = UserTrainingProgress::where('training_module_id', $module->id)->get();

            $totalEnrolled   = $progress->count();
            $startedPretest  = $progress->filter(fn($p) => !in_array($p->pre_test_status, ['locked', 'available']))->count();
            $passedPretest   = $progress->filter(fn($p) => in_array($p->pre_test_status, ['passed', 'failed']))->count(); // attempted
            $startedVr       = $progress->filter(fn($p) => !in_array($p->vr_status, ['locked']))->count();
            $completedVr     = $progress->filter(fn($p) => $p->vr_status === 'completed')->count();
            $startedPosttest = $progress->filter(fn($p) => !in_array($p->post_test_status, ['locked']))->count();
            $passedPosttest  = $progress->filter(fn($p) => $p->post_test_status === 'passed')->count();

            return [
                'module_id'        => $module->id,
                'module_title'     => $module->title,
                'total_enrolled'   => $totalEnrolled,
                'started_pretest'  => max($startedPretest, $passedPretest),
                'completed_pretest' => $passedPretest,
                'started_vr'       => $startedVr,
                'completed_vr'     => $completedVr,
                'started_posttest' => $startedPosttest,
                'passed_posttest'  => $passedPosttest,
                'drop_off_rates'   => [
                    'pretest_to_vr'  => $passedPretest > 0 ? round((1 - $startedVr / $passedPretest) * 100, 1) : 0,
                    'vr_to_posttest' => $completedVr > 0 ? round((1 - $startedPosttest / $completedVr) * 100, 1) : 0,
                    'posttest_pass'  => $startedPosttest > 0 ? round((1 - $passedPosttest / $startedPosttest) * 100, 1) : 0,
                ],
            ];
        });

        // Overall stats
        $totalEnrolled  = $funnelData->sum('total_enrolled');
        $totalCompleted = $funnelData->sum('passed_posttest');

        return response()->json([
            'modules' => $funnelData->values(),
            'summary' => [
                'total_enrolled'       => $totalEnrolled,
                'total_completed'      => $totalCompleted,
                'overall_completion'   => $totalEnrolled > 0 ? round(($totalCompleted / $totalEnrolled) * 100, 1) : 0,
            ],
        ]);
    }

    // ──────────────────────────────────────────────
    // 4. VR Session Performance
    // ──────────────────────────────────────────────
    public function vrPerformance(Request $request): JsonResponse
    {
        $moduleId = $request->input('module_id');
        $period   = (string) $request->input('period', '90d');
        $since    = $this->periodToDate($period);

        /** @var \Illuminate\Database\Eloquent\Builder $sessionsQuery */
        $sessionsQuery = VrSession::with(['user', 'trainingModule', 'analytics']);

        if ($since) {
            $sessionsQuery->where('created_at', '>=', $since);
        }

        if ($moduleId) {
            $sessionsQuery->where('training_module_id', $moduleId);
        }

        $sessions = $sessionsQuery->get();
        $analytics = SessionAnalytics::whereIn('vr_session_id', $sessions->pluck('id'))->get();

        $completedSessions = $sessions->where('session_status', 'completed')->count();
        $totalSessions     = $sessions->count();

        $perModule = $this->aggregateVrPerformanceByModule($sessions);
        $recent    = $sessions->sortByDesc('created_at')->take(20)->map(function (VrSession $s) {
            return $this->mapVrSessionDetails($s);
        })->values();

        return response()->json([
            'summary' => [
                'total_sessions'     => $totalSessions,
                'avg_accuracy'       => round($analytics->avg('accuracy_score') ?? 0, 1),
                'avg_speed'          => round($analytics->avg('speed_score') ?? 0, 1),
                'avg_duration_min'   => round(($analytics->avg('duration_seconds') ?? 0) / 60, 1),
                'avg_breach_count'   => round($analytics->avg('breach_count') ?? 0, 1),
                'completion_rate'    => $totalSessions > 0 ? round(($completedSessions / $totalSessions) * 100, 1) : 0,
            ],
            'per_module'       => $perModule,
            'recent_sessions'  => $recent,
        ]);
    }

    private function aggregateVrPerformanceByModule(Collection $sessions): Collection
    {
        return $sessions->groupBy('training_module_id')->map(function ($group) {
            $moduleAnalytics = SessionAnalytics::whereIn('vr_session_id', $group->pluck('id'))->get();
            $module = $group->first()->trainingModule;

            return [
                'module_id'       => $module?->id,
                'module_title'    => $module?->title ?? '-',
                'session_count'   => $group->count(),
                'avg_accuracy'    => round($moduleAnalytics->avg('accuracy_score') ?? 0, 1),
                'avg_speed'       => round($moduleAnalytics->avg('speed_score') ?? 0, 1),
                'avg_duration_min' => round(($moduleAnalytics->avg('duration_seconds') ?? 0) / 60, 1),
                'avg_breach_count' => round($moduleAnalytics->avg('breach_count') ?? 0, 1),
            ];
        })->values();
    }

    // ──────────────────────────────────────────────
    // 5. AI Cost & Usage Dashboard
    // ──────────────────────────────────────────────
    public function aiUsage(Request $request): JsonResponse
    {
        $period = $request->input('period', '30d');
        $since  = $this->periodToDate($period);

        $query = AiUsageLog::query()
            ->when($since, fn($q) => $q->where('created_at', '>=', $since));

        $logs = $query->get();

        $totalTokens = $logs->sum('total_tokens');
        // Gemini 1.5 Flash approximate pricing: $0.075 per 1M input, $0.30 per 1M output
        // Simplified: ~$0.15 per 1M tokens average
        $estimatedCost = round(($totalTokens / 1000000) * 0.15, 2);

        $byType     = $this->aggregateAiUsageByType($logs);
        $dailyUsage = $this->aggregateAiUsageDaily($logs);

        $safeCount = $logs->where('is_safe_response', true)->count();

        return response()->json([
            'summary' => [
                'total_interactions'  => $logs->count(),
                'total_tokens'        => $totalTokens,
                'estimated_cost_usd'  => $estimatedCost,
                'avg_latency_ms'      => round($logs->avg('latency_ms') ?? 0),
                'safe_response_rate'  => $logs->count() > 0 ? round(($safeCount / $logs->count()) * 100, 1) : 100,
            ],
            'by_type'     => $byType,
            'daily_usage' => $dailyUsage,
        ]);
    }

    private function aggregateAiUsageByType(Collection $logs): Collection
    {
        return $logs->groupBy('interaction_type')->map(function ($group, $type) {
            return [
                'type'   => $type,
                'count'  => $group->count(),
                'tokens' => $group->sum('total_tokens'),
                'avg_latency_ms' => round($group->avg('latency_ms') ?? 0),
            ];
        })->values();
    }

    private function aggregateAiUsageDaily(Collection $logs): Collection
    {
        return $logs->groupBy(fn($l) => $l->created_at->format('Y-m-d'))
            ->map(function ($group, $date) {
                return [
                    'date'         => $date,
                    'interactions' => $group->count(),
                    'tokens'       => $group->sum('total_tokens'),
                ];
            })
            ->sortKeys()
            ->values();
    }

    // ──────────────────────────────────────────────
    // 6. Trend Report
    // ──────────────────────────────────────────────
    public function trends(Request $request): JsonResponse
    {
        $period = $request->input('period', '30d');
        $since  = $this->periodToDate($period);
        $dateFormat = 'Y-m-d';

        $dates = $this->generateDateRange($since, $dateFormat);

        $registrations  = $this->getRegistrationTrends($since, $dateFormat);
        $assessments    = $this->getAssessmentTrends($since, $dateFormat);
        $vrSessions     = $this->getVrSessionTrends($since, $dateFormat);
        $aiInteractions = $this->getAiInteractionTrends($since, $dateFormat);

        return response()->json([
            'period'          => $period,
            'registrations'   => $this->formatTrendDates($dates, $registrations),
            'assessments'     => $this->formatTrendDates($dates, $assessments, ['count', 'avg_score']),
            'vr_sessions'     => $this->formatTrendDates($dates, $vrSessions),
            'ai_interactions' => $this->formatTrendDates($dates, $aiInteractions),
        ]);
    }

    private function generateDateRange(?Carbon $since, string $format): Collection
    {
        if (!$since) return collect();

        $period = CarbonPeriod::create($since, now());
        $dates = collect();

        foreach ($period as $date) {
            /** @var Carbon $date */
            $dates->push($date->format($format));
        }

        return $dates;
    }

    private function getRegistrationTrends(?Carbon $since, string $format): Collection
    {
        return User::query()
            ->when($since, fn($q) => $q->where('created_at', '>=', $since))
            ->get()
            ->groupBy(fn($u) => $u->created_at->format($format))
            ->map(fn($g, $date) => ['date' => $date, 'count' => $g->count()]);
    }

    private function getAssessmentTrends(?Carbon $since, string $format): Collection
    {
        return AssessmentAttempt::whereNotNull('completed_at')
            ->when($since, fn($q) => $q->where('completed_at', '>=', $since))
            ->get()
            ->groupBy(fn($a) => $a->completed_at->format($format))
            ->map(fn($g, $date) => [
                'date'      => $date,
                'count'     => $g->count(),
                'avg_score' => round($g->avg('score') ?? 0, 1),
            ]);
    }

    private function getVrSessionTrends(?Carbon $since, string $format): Collection
    {
        return VrSession::query()
            ->when($since, fn($q) => $q->where('created_at', '>=', $since))
            ->get()
            ->groupBy(fn($s) => $s->created_at->format($format))
            ->map(fn($g, $date) => ['date' => $date, 'count' => $g->count()]);
    }

    private function getAiInteractionTrends(?Carbon $since, string $format): Collection
    {
        return AiUsageLog::query()
            ->when($since, fn($q) => $q->where('created_at', '>=', $since))
            ->get()
            ->groupBy(fn($l) => $l->created_at->format($format))
            ->map(fn($g, $date) => ['date' => $date, 'count' => $g->count()]);
    }

    // ──────────────────────────────────────────────
    // Export: CSV helper
    // ──────────────────────────────────────────────
    public function exportCsv(Request $request)
    {
        $reportType = $request->input('report');

        // Get data from appropriate method
        $dataResponse = match ($reportType) {
            'pretest-posttest' => $this->pretestPosttest($request),
            'question-analysis' => $this->questionAnalysis($request),
            'completion-funnel' => $this->completionFunnel($request),
            'vr-performance'   => $this->vrPerformance($request),
            'ai-usage'         => $this->aiUsage($request),
            'trends'           => $this->trends($request),
            default            => abort(400, 'Invalid report type'),
        };

        $data = json_decode($dataResponse->getContent(), true);
        $filename = "report_{$reportType}_" . now()->format('Y-m-d_H-i') . '.csv';

        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($data, $reportType) {
            $file = fopen('php://output', 'w');
            // BOM for Excel UTF-8
            fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF));

            match ($reportType) {
                'pretest-posttest'  => $this->writePretestPosttestCsv($file, $data),
                'question-analysis' => $this->writeQuestionAnalysisCsv($file, $data),
                'completion-funnel' => $this->writeCompletionFunnelCsv($file, $data),
                'vr-performance'    => $this->writeVrPerformanceCsv($file, $data),
                'ai-usage'          => $this->writeAiUsageCsv($file, $data),
                'trends'            => $this->writeTrendsCsv($file, $data),
                default => null,
            };

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    private function writePretestPosttestCsv($file, array $data): void
    {
        fputcsv($file, ['No', 'Nama', 'Email', 'Modul', 'Skor Pre-Test', 'Skor Post-Test', 'Learning Gain', 'Kategori']);
        foreach (($data['per_student'] ?? []) as $i => $s) {
            fputcsv($file, [$i + 1, $s['name'], $s['email'], $s['module'], $s['pretest_score'] ?? '-', $s['posttest_score'] ?? '-', $s['learning_gain'] ?? '-', $s['gain_category']]);
        }
    }

    private function writeQuestionAnalysisCsv($file, array $data): void
    {
        fputcsv($file, ['No', 'Soal', 'Modul', 'Scope', 'Dijawab', 'Benar', 'Correct Rate %', 'Difficulty']);
        foreach (($data['questions'] ?? []) as $i => $q) {
            fputcsv($file, [$i + 1, $q['question_text'], $q['module'], $q['usage_scope'], $q['times_answered'], $q['times_correct'], $q['correct_rate'], $q['discrimination']]);
        }
    }

    private function writeCompletionFunnelCsv($file, array $data): void
    {
        fputcsv($file, ['Modul', 'Enrolled', 'Pre-Test', 'VR Started', 'VR Completed', 'Post-Test', 'Passed', 'Drop Pre→VR %', 'Drop VR→Post %', 'Drop Post Fail %']);
        foreach (($data['modules'] ?? []) as $m) {
            fputcsv($file, [$m['module_title'], $m['total_enrolled'], $m['completed_pretest'], $m['started_vr'], $m['completed_vr'], $m['started_posttest'], $m['passed_posttest'], $m['drop_off_rates']['pretest_to_vr'], $m['drop_off_rates']['vr_to_posttest'], $m['drop_off_rates']['posttest_pass']]);
        }
    }

    private function writeVrPerformanceCsv($file, array $data): void
    {
        fputcsv($file, ['No', 'User', 'Modul', 'Status', 'Accuracy', 'Speed', 'Durasi (menit)', 'Breach Count', 'Tanggal']);
        foreach (($data['recent_sessions'] ?? []) as $i => $s) {
            fputcsv($file, [$i + 1, $s['user_name'], $s['module'], $s['status'], $s['accuracy'], $s['speed'], $s['duration_min'], $s['breach_count'], $s['date']]);
        }
    }

    private function writeAiUsageCsv($file, array $data): void
    {
        fputcsv($file, ['Tanggal', 'Interactions', 'Tokens']);
        foreach (($data['daily_usage'] ?? []) as $d) {
            fputcsv($file, [$d['date'], $d['interactions'], $d['tokens']]);
        }
    }

    private function writeTrendsCsv($file, array $data): void
    {
        fputcsv($file, ['Tanggal', 'Registrasi', 'Assessments', 'VR Sessions', 'AI Interactions']);
        $regs   = collect($data['registrations'] ?? [])->keyBy('date');
        $assess = collect($data['assessments'] ?? [])->keyBy('date');
        $vr     = collect($data['vr_sessions'] ?? [])->keyBy('date');
        $ai     = collect($data['ai_interactions'] ?? [])->keyBy('date');
        $allDates = $regs->keys()->merge($assess->keys())->merge($vr->keys())->merge($ai->keys())->unique()->sort();
        foreach ($allDates as $date) {
            fputcsv($file, [$date, $regs->get($date)['count'] ?? 0, $assess->get($date)['count'] ?? 0, $vr->get($date)['count'] ?? 0, $ai->get($date)['count'] ?? 0]);
        }
    }

    // ──────────────────────────────────────────────
    // Helpers
    // ──────────────────────────────────────────────

    /**
     * Group attempts by user and module, picking best scores.
     */
    private function getPerStudentProgression(Collection $attempts): array
    {
        $grouped = $attempts->groupBy(function ($a) {
            return $a->user_id . '-' . $a->assessment->module_id;
        });

        $perStudent = [];
        foreach ($grouped as $group) {
            $pre  = $group->filter(fn($a) => $a->assessment->type->value === 'pretest')->sortByDesc('score')->first();
            $post = $group->filter(fn($a) => $a->assessment->type->value === 'posttest')->sortByDesc('score')->first();

            if (!$pre && !$post) continue;

            $preScore  = $pre?->score ?? 0;
            $postScore = $post?->score ?? 0;
            $gain      = $postScore - $preScore;

            $gainCategory = 'none';
            if ($pre && $post) {
                if ($gain > 20) $gainCategory = 'high';
                elseif ($gain >= 10) $gainCategory = 'medium';
                elseif ($gain >= 0) $gainCategory = 'low';
                else $gainCategory = 'negative';
            }

            $user   = $pre?->user ?? $post?->user;
            $module = $pre?->assessment->trainingModule ?? $post?->assessment->trainingModule;

            $perStudent[] = [
                'user_id'        => $user?->id,
                'name'           => $user?->name ?? '-',
                'email'          => $user?->email ?? '-',
                'module_id'      => $module?->id,
                'module'         => $module?->title ?? '-',
                'pretest_score'  => $pre ? $preScore : null,
                'posttest_score' => $post ? $postScore : null,
                'learning_gain'  => ($pre && $post) ? $gain : null,
                'gain_category'  => $gainCategory,
            ];
        }

        return $perStudent;
    }

    private function mapVrSessionDetails(VrSession $s): array
    {
        $a = $s->analytics;
        return [
            'session_id'    => $s->id,
            'user_name'     => $s->user?->name ?? '-',
            'module'        => $s->trainingModule?->title ?? '-',
            'status'        => $s->session_status,
            'accuracy'      => $a?->accuracy_score ?? 0,
            'speed'         => $a?->speed_score ?? 0,
            'duration_min'  => $a ? round($a->duration_seconds / 60, 1) : 0,
            'breach_count'  => $a?->breach_count ?? 0,
            'date'          => $s->created_at?->format('Y-m-d H:i'),
        ];
    }

    private function formatTrendDates(Collection $dates, $data, array $fields = ['count']): Collection
    {
        $keyed = collect($data)->keyBy('date');
        return $dates->map(function ($date) use ($keyed, $fields) {
            $entry = ['date' => $date];
            foreach ($fields as $f) {
                $entry[$f] = $keyed->get($date)[$f] ?? 0;
            }
            return $entry;
        })->values();
    }

    private function periodToDate(string $period): ?Carbon
    {
        return match ($period) {
            '7d'  => now()->subDays(7),
            '30d' => now()->subDays(30),
            '90d' => now()->subDays(90),
            'all' => null,
            default => now()->subDays(90),
        };
    }
}
