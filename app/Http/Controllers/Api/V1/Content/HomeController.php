<?php

namespace App\Http\Controllers\Api\V1\Content;

use App\Http\Controllers\Controller;
use App\Models\TrainingModule;
use App\Models\UserTrainingProgress;
use App\Models\EducationContent;
use App\Http\Resources\Api\V1\Content\TrainingModuleResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HomeController extends Controller
{
    /**
     * Get the Home Hub data (VR-first).
     */
    public function index(Request $request)
    {
        $user = $request->user()->load(['profile']);

        return response()->json([
            'success' => true,
            'message' => 'Home hub data successfully retrieved.',
            'data'    => [
                'user_greeting'             => $this->getGreetingData($user),
                'banner_url'                => $this->getBannerUrl(),
                'vr_status_header'          => $this->getVrStatusHeader($user),
                'hero_module_card'          => $this->getHeroModuleCard($user),
                'progress_summary'          => $this->getProgressSummary($user),
                'featured_learning_preview' => $this->getFeaturedLearningPreview(),
                'latest_news_preview'       => \App\Models\News::where('is_active', true)
                    ->latest('published_at')
                    ->limit(2)
                    ->get()
                    ->map(function($news) {
                        return [
                            'id'           => $news->id,
                            'title'        => $news->title,
                            'slug'         => $news->slug,
                            'summary'      => $news->summary,
                            'image_url'    => $news->image_url,
                            'category'     => $news->category,
                            'published_at' => $news->published_at?->toIso8601String(),
                        ];
                    }),
                'smart_actions'             => [
                    ['label' => 'Tanya PharmAI', 'action' => 'open_ai', 'icon' => 'auto_awesome'],
                    ['label' => 'Lanjut Belajar', 'action' => 'open_edukasi', 'icon' => 'menu_book'],
                    ['label' => 'Berita Terkini', 'action' => 'open_news', 'icon' => 'newspaper'],
                ],
            ]
        ]);
    }

    /**
     * Get user greeting data.
     */
    private function getGreetingData($user)
    {
        return [
            'full_name'        => $user->name,
            'avatar_url'       => $user->profile?->avatar_url ? \Illuminate\Support\Facades\Storage::url($user->profile->avatar_url) : null,
            'academic_summary' => $user->profile?->university ?? 'Lengkapi profil akademik Anda',
        ];
    }

    /**
     * Get VR status header data.
     */
    private function getVrStatusHeader($user)
    {
        $device = \App\Models\VrDevice::where('user_id', $user->id)
            ->where('status', 'active')
            ->orderBy('last_seen_at', 'desc')
            ->first();

        $activeSession = \App\Models\VrSession::where('user_id', $user->id)
            ->whereIn('session_status', ['starting', 'playing'])
            ->with('trainingModule')
            ->first();

        $connectionStatus = 'offline';
        if ($device && $device->last_seen_at) {
            $diffMinutes = $device->last_seen_at->diffInMinutes(now());
            if ($diffMinutes <= 2) $connectionStatus = 'connected';
            elseif ($diffMinutes <= 10) $connectionStatus = 'standby';
        }

        $nextAction = 'Hubungkan Meta Quest 3';
        $nextRoute = 'vr_connect';
        
        if (!$device) {
            $nextAction = 'Mulai Pairing Perangkat';
            $nextRoute = 'vr_pairing_start';
        } elseif ($activeSession) {
            $nextAction = 'Lanjutkan Sesi Pelatihan';
            $nextRoute = 'vr_session_status';
        } elseif ($connectionStatus === 'connected' || $connectionStatus === 'standby') {
            $nextAction = 'Pilih Modul Pelatihan';
            $nextRoute = 'training_hub';
        }

        $hints = [];
        if (!$device) $hints[] = 'Headset Meta Quest 3 belum dipasangkan.';
        elseif ($connectionStatus === 'offline') $hints[] = 'Quest 3 sedang tidak tersambung.';
        
        if (!$activeSession) {
             $hints[] = 'Pilih modul untuk memulai simulasi.';
        }

        return [
            'is_paired'         => (bool) $device,
            'device_type'       => $device?->device_type ?? 'meta_quest_3',
            'connection_status' => $connectionStatus,
            'paired_status'     => $device?->status ?? 'inactive',
            'headset_name'      => $device?->device_name ?? 'No Device Paired',
            'last_seen'         => $device?->last_seen_at?->diffForHumans() ?? null,
            'ready_to_enter'    => (bool) ($device && $activeSession),
            'active_session'    => $activeSession ? [
                'id'           => $activeSession->id,
                'module_title' => $activeSession->trainingModule->title,
                'progress'     => $activeSession->progress_percentage,
                'current_step' => $activeSession->current_step,
            ] : null,
            'launch_readiness_hints' => $hints,
            'recommended_next_action' => $nextAction,
            'recommended_next_route'  => $nextRoute,
        ];
    }

    /**
     * Get hero module card data.
     */
    private function getHeroModuleCard($user)
    {
        $activeProgress = UserTrainingProgress::where('user_id', $user->id)
            ->whereIn('status', ['in_progress', 'available'])
            ->with('trainingModule')
            ->orderByRaw("FIELD(status, 'in_progress', 'available')")
            ->latest('last_accessed_at')
            ->first();

        if ($activeProgress) {
            $hero = $activeProgress->trainingModule;
            
            $actionLabel = 'Lanjut Belajar';
            $primaryAction = 'continue_training';
            $isReady = true;

            if ($activeProgress->pre_test_status === 'available') {
                $actionLabel = 'Ikuti Pre-Test';
                $primaryAction = 'start_pre_test';
                $isReady = false;
            } elseif ($activeProgress->vr_status === 'available') {
                $actionLabel = 'Mulai Simulasi VR';
                $primaryAction = 'enter_simulation';
            } elseif ($activeProgress->post_test_status === 'available') {
                $actionLabel = 'Ikuti Post-Test';
                $primaryAction = 'start_post_test';
            }

            return [
                'id'                => $hero->id,
                'code'              => $hero->slug,
                'title'             => $hero->title,
                'description'       => $hero->description,
                'estimated_duration'=> $hero->estimated_duration . ' min',
                'difficulty'        => $hero->difficulty,
                'is_ready'          => $isReady,
                'action_label'      => $actionLabel,
                'actions'           => [$primaryAction, 'view_details'],
                'current_step'      => $activeProgress->last_active_step,
            ];
        }

        $recommended = TrainingModule::where('is_active', true)->first();
        if ($recommended) {
            $preTest = \App\Models\Assessment::where('module_id', $recommended->id)
                ->where('type', \App\Enums\AssessmentType::PRETEST->value)
                ->where('status', \App\Enums\AssessmentStatus::ACTIVE->value)
                ->first();
            
            $isReady = true;
            $actionLabel = 'Mulai Simulasi';
            $primaryAction = 'start_simulation';

            if ($preTest) {
                $hasPassed = \App\Models\AssessmentAttempt::where('user_id', $user->id)
                    ->where('assessment_id', $preTest->id)
                    ->where('status', 'passed')
                    ->exists();
                
                if (!$hasPassed) {
                    $isReady = false;
                    $actionLabel = 'Ikuti Pre-Test';
                    $primaryAction = 'start_pre_test';
                }
            }

            return [
                'id'                => $recommended->id,
                'code'              => $recommended->slug,
                'title'             => $recommended->title,
                'description'       => $recommended->description,
                'estimated_duration'=> $recommended->estimated_duration . ' min',
                'difficulty'        => $recommended->difficulty,
                'is_ready'          => $isReady,
                'action_label'      => $actionLabel,
                'actions'           => [$primaryAction, 'connect_vr'],
            ];
        }

        return null;
    }

    /**
     * Get progress summary data.
     */
    private function getProgressSummary($user)
    {
        $totalModules = TrainingModule::where('is_active', true)->count();
        $completedModules = UserTrainingProgress::where('user_id', $user->id)
            ->where('status', 'completed')
            ->count();
        
        return [
            'total_modules'         => (int) $totalModules,
            'completed_modules'     => (int) $completedModules,
            'progress_percentage'   => $totalModules > 0 ? (int) round(($completedModules / $totalModules) * 100) : 0,
            'vr_sessions_count'     => (int) \App\Models\VrSession::where('user_id', $user->id)->count(),
            'total_xp'              => 0, // Placeholder
        ];
    }

    /**
     * Get the banner URL from settings.
     */
    private function getBannerUrl()
    {
        $setting = \App\Models\Setting::where('key', 'home_banner')->first();
        $path = $setting ? $setting->value : 'assets/images/hero_banner.jpg';

        if (str_starts_with($path, 'http')) {
            return $path;
        }

        // Handle relative paths from storage
        if (!str_starts_with($path, 'storage/') && !str_starts_with($path, 'assets/')) {
            $path = 'storage/' . $path;
        }

        return url($path);
    }

    /**
     * Get featured learning preview data.
     */
    private function getFeaturedLearningPreview()
    {
        return [
            'modul'    => EducationContent::where('type', 'module')->where('is_active', true)->latest()->first(),
            'video'    => EducationContent::where('type', 'video')->where('is_active', true)->latest()->first(),
            'document' => EducationContent::where('type', 'document')->where('is_active', true)->latest()->first(),
        ];
    }
}
