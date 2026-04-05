<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\V1\Auth\AuthController;
use App\Http\Controllers\Api\V1\Profile\ProfileController;
use App\Http\Controllers\Api\V1\Content\HomeController;
use App\Http\Controllers\Api\V1\Content\EducationController;
use App\Http\Controllers\Api\V1\Content\NewsController;
use App\Http\Controllers\Api\V1\Ai\PharmaiChatController;
use App\Http\Controllers\Api\V1\Ai\AiChatController;
use App\Http\Controllers\Api\V1\Ai\AiAvatarGuideController;
use App\Http\Controllers\Admin\Ai\AiKnowledgeSourceController;
use App\Http\Controllers\Admin\Ai\AiAvatarProfileController;
use App\Http\Controllers\Admin\Ai\AiAvatarScenePromptController;
use App\Http\Controllers\Api\V1\Vr\VrAiController;
use App\Http\Controllers\Api\V1\MediaController;
use App\Http\Controllers\Api\V1\Analytics\AnalyticsController;
use App\Http\Controllers\Api\V1\App\AppSettingController;

Route::prefix('v1')->group(function () {
    // Media Proxy (Public)
    Route::get('/media/{path}', [MediaController::class, 'serve'])->where('path', '.*');

    // Public Auth Routes
    Route::post('/auth/register', [AuthController::class, 'register']);
    Route::post('/auth/login', [AuthController::class, 'login']);
    Route::post('/auth/forgot-password', [AuthController::class, 'forgotPassword']);
    Route::post('/auth/reset-password', [AuthController::class, 'resetPassword']);

    // Protected Routes
    Route::middleware('auth:sanctum')->group(function () {
        // Auth
        Route::get('/auth/me', [AuthController::class, 'me']);
        Route::post('/auth/logout', [AuthController::class, 'logout']);
        
        // Profile
        Route::get('/profile', [ProfileController::class, 'show']);
        Route::post('/profile', [ProfileController::class, 'update']);
        Route::put('/profile/password', [ProfileController::class, 'changePassword']);

        // Home Hub
        Route::get('/home', [HomeController::class, 'index']);
        Route::get('/app/settings', [AppSettingController::class, 'index']);

        // Edukasi
        Route::get('/edukasi', [EducationController::class, 'index']);
        Route::get('/edukasi/{slug}', [EducationController::class, 'show']);

        // News
        Route::get('/news', [NewsController::class, 'index']);
        Route::get('/news/sources', [NewsController::class, 'sources']);
        Route::get('/news/categories', [NewsController::class, 'categories']);
        Route::get('/news/{slug}', [NewsController::class, 'show']);

        // Assessments
        Route::get('/assessments/{moduleSlug}/{type}', [\App\Http\Controllers\Api\V1\Assessment\AssessmentController::class, 'intro']);
        Route::post('/assessments/{assessment}/start', [\App\Http\Controllers\Api\V1\Assessment\AssessmentController::class, 'start']);
        
        // New Question Bank Based Assessments
        Route::get('/modules/{module:slug}/assessments/{type}/start', [\App\Http\Controllers\Api\AssessmentDeliveryController::class, 'start']);

        // Assessment Attempts
        Route::get('/assessment-attempts/{attempt}', [\App\Http\Controllers\Api\V1\Assessment\AssessmentController::class, 'questions']);
        Route::post('/assessment-attempts/{attempt}/submit', [\App\Http\Controllers\Api\V1\Assessment\AssessmentController::class, 'submit']);
        Route::get('/assessment-attempts/{attempt}/result', [\App\Http\Controllers\Api\V1\Assessment\AssessmentController::class, 'results']);

        // Phase 4: VR Backend (Mobile Side - Auth Required)
        Route::prefix('vr')->group(function () {
            // Pairing
            Route::prefix('pairings')->group(function () {
                Route::post('/start', [\App\Http\Controllers\Api\V1\Vr\VrPairingController::class, 'start']);
                Route::get('/current', [\App\Http\Controllers\Api\V1\Vr\VrPairingController::class, 'current']);
                Route::post('/{pairing}/cancel', [\App\Http\Controllers\Api\V1\Vr\VrPairingController::class, 'cancel']);
            });
            
            // Session Initiation
            Route::prefix('sessions')->group(function () {
                Route::get('/current', [\App\Http\Controllers\Api\V1\Vr\VrSessionController::class, 'current']);
                Route::post('/start', [\App\Http\Controllers\Api\V1\Vr\VrSessionController::class, 'mobileStart']);
                Route::get('/{session}', [\App\Http\Controllers\Api\V1\Vr\VrSessionController::class, 'show']);
            });

            // Status & Readiness
            Route::get('/status', [\App\Http\Controllers\Api\V1\Vr\VrStatusController::class, 'status']);
            Route::get('/modules/{moduleSlug}/launch-readiness', [\App\Http\Controllers\Api\V1\Vr\VrStatusController::class, 'launchReadiness']);
            Route::get('/readiness/{moduleId}', [\App\Http\Controllers\Api\V1\Vr\VrStatusController::class, 'readiness']);
        });

        // PharmAI (App Side) - Optimized for Flutter
        Route::prefix('ai')->group(function () {
            Route::prefix('conversations')->group(function () {
                Route::get('/', [PharmaiChatController::class, 'index']);
                Route::post('/', [PharmaiChatController::class, 'store']);
                Route::get('/{conversation}', [PharmaiChatController::class, 'show']);
                Route::post('/{conversation}/messages', [PharmaiChatController::class, 'sendMessage']);
            });
            Route::post('/chat', [PharmaiChatController::class, 'statelessChat']);
        });

        // Phase 6 & 7: Analytics, Reporting & Leaderboards
        Route::prefix('analytics')->group(function () {
            Route::get('/overview', [AnalyticsController::class, 'overview']);
            Route::get('/achievements', [AnalyticsController::class, 'achievements']);
            Route::get('/sessions/{analytics}', [AnalyticsController::class, 'sessionDetail']);
            
            // Leaderboards
            Route::get('/leaderboard/global', [\App\Http\Controllers\Api\V1\Analytics\LeaderboardController::class, 'global']);
            Route::get('/leaderboard/modules/{module:slug}', [\App\Http\Controllers\Api\V1\Analytics\LeaderboardController::class, 'module']);
            
            // Unified Progress
            Route::get('/user/progress', [\App\Http\Controllers\Api\V1\Analytics\LeaderboardController::class, 'userProgress']);
        });

        // Trusted Source AI Assistant (Student Chat & VR Guide)
        Route::prefix('ai-assistant')->group(function () {
            Route::post('/chat/start', [AiChatController::class, 'start']);
            Route::post('/chat/ask', [AiChatController::class, 'ask']);
            Route::get('/chat/sessions', [AiChatController::class, 'sessions']);
            Route::get('/chat/sessions/{session}', [AiChatController::class, 'showSession']);
            Route::get('/chat/sessions/{session}/messages', [AiChatController::class, 'messages']);

            Route::get('/avatar/profiles', [AiAvatarGuideController::class, 'profiles']);
            Route::post('/avatar/guide', [AiAvatarGuideController::class, 'guide']);
            Route::post('/avatar/ask', [AiAvatarGuideController::class, 'ask']);
            Route::get('/avatar/scenes/{sceneKey}/prompts', [AiAvatarGuideController::class, 'scenePrompts']);
        });

        // Admin AI Management (Inside V1 for now, or separate prefix if preferred)
        Route::prefix('admin/ai')->middleware(['admin'])->group(function () {
            Route::apiResource('knowledge-sources', AiKnowledgeSourceController::class);
            Route::post('knowledge-sources/{source}/reprocess', [AiKnowledgeSourceController::class, 'reprocess']);
            Route::patch('knowledge-sources/{source}/toggle', [AiKnowledgeSourceController::class, 'toggleActive']);

            Route::apiResource('avatars', AiAvatarProfileController::class);
            Route::patch('avatars/{profile}/toggle', [AiAvatarProfileController::class, 'toggleActive']);
            
            Route::apiResource('scene-prompts', AiAvatarScenePromptController::class);
        });
    });

    // Phase 4: VR Backend (Headset Side - Device Token Required, Public Route)
    Route::prefix('vr')->group(function () {
        // Headset Specific (Pairing, Heartbeat, Sessions)
        Route::prefix('headset')->group(function () {
            Route::post('/pair', [\App\Http\Controllers\Api\V1\Vr\VrPairingController::class, 'pair'])
                ->middleware('throttle:5,1'); // Rate limit 5 attempts per minute
            Route::post('/heartbeat', [\App\Http\Controllers\Api\V1\Vr\VrDeviceController::class, 'heartbeat'])
                ->middleware('throttle:60,1');
            Route::post('/unpair', [\App\Http\Controllers\Api\V1\Vr\VrDeviceController::class, 'unpair']);
            
            // Session Lifecycle (Uses Device Access Token validation internally)
            Route::prefix('sessions')->group(function () {
                Route::post('/start', [\App\Http\Controllers\Api\V1\Vr\VrSessionController::class, 'headsetStart']);
                Route::put('/{session}/progress', [\App\Http\Controllers\Api\V1\Vr\VrSessionController::class, 'updateProgress']);
                Route::post('/{session}/complete', [\App\Http\Controllers\Api\V1\Vr\VrSessionController::class, 'completeSession']);
                Route::post('/{session}/interrupt', [\App\Http\Controllers\Api\V1\Vr\VrSessionController::class, 'interruptSession']);
                Route::post('/{session}/events', [\App\Http\Controllers\Api\V1\Vr\VrSessionController::class, 'storeEvent'])
                    ->middleware('throttle:60,1');
                Route::post('/{session}/quiz-events', [\App\Http\Controllers\Api\V1\Vr\VrSessionController::class, 'storeQuizEvent'])
                    ->middleware('throttle:20,1');
                Route::post('/{session}/stage-results', [\App\Http\Controllers\Api\V1\Vr\VrSessionController::class, 'storeStageResult'])
                    ->middleware('throttle:20,1');
                Route::post('/{session}/hint-logs', [\App\Http\Controllers\Api\V1\Vr\VrSessionController::class, 'storeHintLog'])
                    ->middleware('throttle:60,1');
                Route::post('/{session}/unified-events', [\App\Http\Controllers\Api\V1\Vr\VrSessionController::class, 'storeUnifiedEvent'])
                    ->middleware('throttle:60,1');
            });

        });

        // VR AI Guided Intelligence
        Route::prefix('ai')->group(function () {
            Route::post('/hint', [VrAiController::class, 'generateHint']);
            Route::post('/reminder', [VrAiController::class, 'generateReminder']);
            Route::post('/feedback', [VrAiController::class, 'generateFeedback']);
        })->middleware('throttle:60,1');

        // Legacy/Alias for compatibility
        Route::post('/pairings/confirm', [\App\Http\Controllers\Api\V1\Vr\VrPairingController::class, 'confirmPairing']);
    });
});
