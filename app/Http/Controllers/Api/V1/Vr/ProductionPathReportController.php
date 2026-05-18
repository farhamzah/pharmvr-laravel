<?php

namespace App\Http\Controllers\Api\V1\Vr;

use App\Http\Controllers\Controller;
use App\Enums\AssessmentType;
use App\Helpers\QrCodeHelper;
use App\Models\AssessmentAttempt;
use App\Models\Certificate;
use App\Models\Scene;
use App\Models\VrSession;
use App\Traits\ApiResponse;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class ProductionPathReportController extends Controller
{
    use ApiResponse;

    private const PRODUCTION_PATH_SLUG = 'solid_dosage_non_sterile';
    private const PRODUCTION_PATH_TITLE = 'Non-Sterile Solid Dosage Production Path';
    private const CERTIFICATE_TYPE = 'production_path_completed';

    private const PRODUCTION_PATH_SCENES = [
        'hygiene',
        'gowning',
        'airlock',
        'production_corridor',
        'weighing',
        'granulation',
        'final_mixing',
        'tabletting',
        'coating',
        'blistering',
        'secondary_packing',
    ];

    public function show(Request $request): JsonResponse
    {
        return $this->successResponse(
            $this->buildReport($request->user()),
            'Production path report retrieved.'
        );
    }

    public function generateCertificate(Request $request): JsonResponse
    {
        $user = $request->user();
        $report = $this->buildReport($user);

        if (!$report['production_path_completed']) {
            return $this->errorResponse('Production path is not completed.', 422);
        }

        $certificate = Certificate::where('user_id', $user->id)
            ->where('certificate_type', self::CERTIFICATE_TYPE)
            ->first();

        if (!$certificate) {
            $certificate = Certificate::create([
                'user_id' => $user->id,
                'certificate_type' => self::CERTIFICATE_TYPE,
                'certificate_id' => $this->generateCertificateId($user->id),
                'title' => 'Production Path Completed',
                'status' => 'issued',
                'issued_at' => now(),
                'metadata_json' => $report,
            ]);
        }

        return $this->successResponse(
            $this->certificatePayload($certificate, true),
            'Production path certificate generated.',
            201
        );
    }

    public function download(Request $request): JsonResponse|Response
    {
        $user = $request->user();
        $certificate = Certificate::where('user_id', $user->id)
            ->where('certificate_type', self::CERTIFICATE_TYPE)
            ->where('status', 'issued')
            ->first();

        $report = null;
        if (!$certificate) {
            $report = $this->buildReport($user);
            if (!$report['production_path_completed']) {
                return $this->errorResponse('Production path is not completed.', 422);
            }

            $certificate = Certificate::create([
                'user_id' => $user->id,
                'certificate_type' => self::CERTIFICATE_TYPE,
                'certificate_id' => $this->generateCertificateId($user->id),
                'title' => 'Production Path Completed',
                'status' => 'issued',
                'issued_at' => now(),
                'metadata_json' => $report,
            ]);
        }

        $metadata = is_array($certificate->metadata_json) ? $certificate->metadata_json : ($report ?? $this->buildReport($user));
        $filename = 'PharmVR_Production_Path_Certificate_' . $certificate->certificate_id . '.pdf';
        $verificationUrl = QrCodeHelper::verificationUrl($certificate->certificate_id);
        $qrSvg = QrCodeHelper::svgPlaceholder($verificationUrl, 80);

        $pdf = Pdf::loadView('certificates.production_path', [
            'certificate' => $certificate,
            'user' => $user,
            'metadata' => $metadata,
            'productionPathTitle' => $metadata['production_path_title'] ?? self::PRODUCTION_PATH_TITLE,
            'completedScenes' => $metadata['completed_scenes'] ?? count(self::PRODUCTION_PATH_SCENES),
            'totalScenes' => $metadata['total_scenes'] ?? count(self::PRODUCTION_PATH_SCENES),
            'verificationUrl' => $verificationUrl,
            'qrSvg' => $qrSvg,
        ])->setPaper('a4', 'landscape');

        $content = $pdf->output();

        return response($content, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            'Cache-Control' => 'no-store, no-cache, must-revalidate',
        ]);
    }

    private function buildReport(object $user): array
    {
        $scenes = Scene::whereIn('slug', self::PRODUCTION_PATH_SCENES)
            ->get()
            ->keyBy('slug');

        $sessions = VrSession::query()
            ->where('user_id', $user->id)
            ->where('session_status', 'completed')
            ->whereIn('scene_id', $scenes->pluck('id')->filter()->values())
            ->with('scene:id,slug,title')
            ->orderByDesc('completed_at')
            ->orderByDesc('id')
            ->get();

        $latestByScene = $sessions
            ->filter(fn(VrSession $session) => $session->scene)
            ->unique(fn(VrSession $session) => $session->scene->slug)
            ->keyBy(fn(VrSession $session) => $session->scene->slug);

        $postTestPassedByScene = $this->postTestPassedByScene($user->id);

        $sceneResults = [];
        $completedSceneSlugs = [];
        $scoreValues = [];
        $totalHotspotsVisited = 0;
        $totalReflectionsCompleted = 0;
        $lastCompletedScene = null;
        $lastCompletedAt = null;

        foreach (self::PRODUCTION_PATH_SCENES as $slug) {
            $scene = $scenes->get($slug);
            $session = $latestByScene->get($slug);
            $summary = $this->completionSummary($session);
            $vrCompleted = (bool) $session;
            $postTestPassed = (bool) ($postTestPassedByScene[$slug] ?? false);
            $completed = $vrCompleted && $postTestPassed;

            if ($completed) {
                $completedSceneSlugs[] = $slug;
                if (!$lastCompletedAt || ($session->completed_at && $session->completed_at->gt($lastCompletedAt))) {
                    $lastCompletedAt = $session->completed_at;
                    $lastCompletedScene = $slug;
                }
            }

            $score = $this->scoreFromSession($session, $summary);
            if ($score !== null) {
                $scoreValues[] = $score;
            }

            $totalHotspotsVisited += $this->hotspotCount($summary);
            if (($summary['reflection_completed'] ?? false) === true) {
                $totalReflectionsCompleted++;
            }

            $sceneResults[] = [
                'scene_slug' => $slug,
                'title' => $scene?->title ?? Str::headline(str_replace('_', ' ', $slug)),
                'completed' => $completed,
                'vr_completed' => $vrCompleted,
                'post_test_passed' => $postTestPassed,
                'completed_at' => $session?->completed_at?->toISOString(),
                'score' => $score,
                'completion_type' => $summary['completion_type'] ?? null,
            ];
        }

        $secondaryCompleted = in_array('secondary_packing', $completedSceneSlugs, true);
        $productionPathCompleted = count($completedSceneSlugs) === count(self::PRODUCTION_PATH_SCENES) && $secondaryCompleted;
        $certificate = Certificate::where('user_id', $user->id)
            ->where('certificate_type', self::CERTIFICATE_TYPE)
            ->first();

        return [
            'production_path_slug' => self::PRODUCTION_PATH_SLUG,
            'production_path_title' => self::PRODUCTION_PATH_TITLE,
            'production_path_completed' => $productionPathCompleted,
            'total_scenes' => count(self::PRODUCTION_PATH_SCENES),
            'completed_scenes' => count($completedSceneSlugs),
            'completed_scene_slugs' => $completedSceneSlugs,
            'last_completed_scene' => $lastCompletedScene,
            'generated_at' => now()->toISOString(),
            'scene_results' => $sceneResults,
            'total_hotspots_visited' => $totalHotspotsVisited,
            'total_reflections_completed' => $totalReflectionsCompleted,
            'average_score' => count($scoreValues) > 0 ? round(array_sum($scoreValues) / count($scoreValues), 2) : null,
            'certificate' => $this->certificatePayload($certificate, $productionPathCompleted),
        ];
    }

    private function postTestPassedByScene(int $userId): array
    {
        return AssessmentAttempt::query()
            ->where('user_id', $userId)
            ->where('passed', true)
            ->whereNotNull('completed_at')
            ->whereHas('assessment', function ($query) {
                $query
                    ->where('type', AssessmentType::POSTTEST->value)
                    ->whereHas('trainingModule', function ($moduleQuery) {
                        $moduleQuery->whereIn('slug', self::PRODUCTION_PATH_SCENES);
                    });
            })
            ->with('assessment.trainingModule:id,slug')
            ->get()
            ->mapWithKeys(function (AssessmentAttempt $attempt) {
                $slug = $attempt->assessment?->trainingModule?->slug;

                return $slug ? [$slug => true] : [];
            })
            ->all();
    }

    private function completionSummary(?VrSession $session): array
    {
        if (!$session) {
            return [];
        }

        $summary = $session->summary_json ?? [];
        $completionSummary = $summary['completion_summary'] ?? [];

        return is_array($completionSummary) ? $completionSummary : [];
    }

    private function scoreFromSession(?VrSession $session, array $summary): ?float
    {
        if ($session?->total_score !== null) {
            return (float) $session->total_score;
        }

        foreach (['final_score', 'score', 'total_score', 'average_score'] as $key) {
            if (isset($summary[$key]) && is_numeric($summary[$key])) {
                return (float) $summary[$key];
            }
        }

        return null;
    }

    private function hotspotCount(array $summary): int
    {
        if (isset($summary['total_hotspots_visited']) && is_numeric($summary['total_hotspots_visited'])) {
            return (int) $summary['total_hotspots_visited'];
        }

        if (isset($summary['visited_hotspots']) && is_array($summary['visited_hotspots'])) {
            return count(array_unique($summary['visited_hotspots']));
        }

        return 0;
    }

    private function certificatePayload(?Certificate $certificate, bool $eligible): array
    {
        if ($certificate) {
            return [
                'eligible' => true,
                'status' => $certificate->status,
                'certificate_id' => $certificate->certificate_id,
                'issued_at' => $certificate->issued_at?->toISOString(),
                'download_url' => url('/api/v1/vr/certificates/production-path/download'),
            ];
        }

        return [
            'eligible' => $eligible,
            'status' => $eligible ? 'eligible' : 'not_eligible',
            'certificate_id' => null,
            'issued_at' => null,
            'download_url' => null,
        ];
    }

    private function generateCertificateId(int $userId): string
    {
        return sprintf(
            'PHARMVR-PROD-%s-%s-%s',
            Carbon::now()->format('Y'),
            $userId,
            Str::upper(Str::random(8))
        );
    }
}
