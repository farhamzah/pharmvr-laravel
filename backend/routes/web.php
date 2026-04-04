<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\Ai\AiAdminDashboardController;
use App\Http\Controllers\Admin\Ai\AiSourceWebController;
use App\Http\Controllers\Admin\Ai\AiAvatarWebController;
use App\Http\Controllers\Admin\Ai\AiScenePromptWebController;
use App\Http\Controllers\Admin\Ai\AiLogWebController;

Route::get('/', function () {
    return view('welcome');
});

// Storage Fallback for local development (Windows symlink issues)
Route::get('/storage/{path}', [\App\Http\Controllers\StorageController::class, 'show'])->where('path', '.*');

// Admin Authentication
Route::get('admin/login', [\App\Http\Controllers\Admin\AdminLoginController::class, 'showLoginForm'])->name('login');
Route::post('admin/login', [\App\Http\Controllers\Admin\AdminLoginController::class, 'login']);
Route::post('admin/logout', [\App\Http\Controllers\Admin\AdminLoginController::class, 'logout'])->name('admin.logout');

// Admin Routes
Route::prefix('admin')->name('admin.')->middleware(['auth', 'admin', 'throttle:admin'])->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    // User Management
    Route::middleware(['can:manage-users', 'throttle:admin-sensitive'])->group(function () {
        Route::resource('users', \App\Http\Controllers\Admin\UserController::class)->except(['show']);
        Route::post('users/{user}/ban', [\App\Http\Controllers\Admin\UserController::class, 'ban'])->name('users.ban');
        Route::post('users/{user}/unban', [\App\Http\Controllers\Admin\UserController::class, 'unban'])->name('users.unban');
        Route::post('users/{user}/force-logout', [\App\Http\Controllers\Admin\UserController::class, 'forceLogout'])->name('users.force-logout');
    });

    // Education & News (Content)
    Route::middleware('can:manage-content')->group(function () {
        Route::resource('education', \App\Http\Controllers\Admin\EducationController::class);
        Route::get('education/{education}/add-content', [\App\Http\Controllers\Admin\EducationController::class, 'addContent'])->name('education.add-content');
        Route::post('education/{education}/store-content', [\App\Http\Controllers\Admin\EducationController::class, 'storeContent'])->name('education.store-content');
        
        Route::resource('videos', \App\Http\Controllers\Admin\VideoController::class);
        Route::patch('videos/{video}/toggle', [\App\Http\Controllers\Admin\VideoController::class, 'toggleStatus'])->name('videos.toggle');
        
        Route::resource('documents', \App\Http\Controllers\Admin\DocumentController::class);
        Route::patch('documents/{document}/toggle', [\App\Http\Controllers\Admin\DocumentController::class, 'toggleStatus'])->name('documents.toggle');

        Route::resource('news', \App\Http\Controllers\Admin\NewsController::class);
        
        // News Sources
        Route::get('news-sources', [\App\Http\Controllers\Admin\NewsSourceController::class, 'index'])->name('news-sources.index');
        Route::patch('news-sources/{news_source}/toggle', [\App\Http\Controllers\Admin\NewsSourceController::class, 'toggleActive'])->name('news-sources.toggle');
        Route::post('news-sources/{news_source}/sync', [\App\Http\Controllers\Admin\NewsSourceController::class, 'sync'])->name('news-sources.sync');
        Route::post('news-sources/sync-all', function () {
            \Illuminate\Support\Facades\Artisan::call('news:sync', ['--force' => true]);
            return redirect()->back()->with('success', 'Sync all triggered. ' . \Illuminate\Support\Facades\Artisan::output());
        })->name('news-sources.sync-all');
        
        // Assessments & Question Bank
        Route::get('/assessments', [\App\Http\Controllers\Admin\AssessmentController::class, 'index'])->name('assessments.index');
        Route::post('/assessments/initialize', [\App\Http\Controllers\Admin\AssessmentController::class, 'initializeAll'])->name('assessments.initialize');
        Route::get('/assessments/module/{module}', [\App\Http\Controllers\Admin\AssessmentController::class, 'show'])->name('assessments.show');
        Route::put('assessments/{assessment}', [\App\Http\Controllers\Admin\AssessmentController::class, 'update'])->name('assessments.update');
        Route::patch('assessments/{assessment}/toggle', [\App\Http\Controllers\Admin\AssessmentController::class, 'toggle'])->name('assessments.toggle');
        
        Route::prefix('modules/{module}')->name('assessments.')->group(function () {
            Route::resource('questions', \App\Http\Controllers\Admin\QuestionBankController::class);
        });
        
        Route::post('assessments/{assessment}/reset/{user}', [\App\Http\Controllers\Admin\AssessmentController::class, 'resetUserAttempts'])->name('assessments.reset-attempts');
    });

    // Monitoring & Hardware Override
    Route::middleware('can:view-monitoring')->group(function () {
        Route::get('monitoring/vr', [\App\Http\Controllers\Admin\MonitoringController::class, 'vrSessions'])->name('monitoring.vr');
        Route::get('monitoring/vr/{session}', [\App\Http\Controllers\Admin\MonitoringController::class, 'vrSessionDetail'])->name('monitoring.vr.detail');
        Route::get('monitoring/ai', [\App\Http\Controllers\Admin\MonitoringController::class, 'aiInteractions'])->name('monitoring.ai');
        Route::get('monitoring/progress', [\App\Http\Controllers\Admin\MonitoringController::class, 'studentProgress'])->name('monitoring.progress');

        // Hardware Fleet Management
        Route::resource('vr-devices', \App\Http\Controllers\Admin\VrDeviceAdminController::class)->except(['create', 'store', 'edit']);
    });

    // Reporting & Analytics
    Route::get('reporting/training', [\App\Http\Controllers\Admin\ReportingController::class, 'trainingProgress'])->name('reporting.training');
    Route::get('reporting/ai', [\App\Http\Controllers\Admin\ReportingController::class, 'aiAnalytics'])->name('reporting.ai');
    Route::get('reporting/users/{user}', [\App\Http\Controllers\Admin\ReportingController::class, 'userReport'])->name('reporting.user-report');
    Route::get('reporting/assessments', [\App\Http\Controllers\Admin\ReportingController::class, 'assessmentReport'])->name('reporting.assessments');
    Route::get('reporting/assessments/export-csv', [\App\Http\Controllers\Admin\ReportingController::class, 'exportAssessmentCsv'])->name('reporting.assessments.export-csv');
    Route::get('reporting/assessments/export-pdf', [\App\Http\Controllers\Admin\ReportingController::class, 'exportAssessmentPdf'])->name('reporting.assessments.export-pdf');

    // Advanced Reporting Hub & Suite
    Route::prefix('advanced-reports')->name('advanced-reports.')->group(function () {
        $ctrl = \App\Http\Controllers\Admin\AdvancedReportController::class;
        Route::get('/', [$ctrl, 'hub'])->name('hub');
        Route::get('pretest-posttest', [$ctrl, 'pretestPosttest'])->name('pretest-posttest');
        Route::get('question-analysis', [$ctrl, 'questionAnalysis'])->name('question-analysis');
        Route::get('completion-funnel', [$ctrl, 'completionFunnel'])->name('completion-funnel');
        Route::get('vr-performance', [$ctrl, 'vrPerformance'])->name('vr-performance');
        Route::get('ai-usage', [$ctrl, 'aiUsage'])->name('ai-usage');
        Route::get('trends', [$ctrl, 'trends'])->name('trends');
        Route::get('export-csv', [$ctrl, 'exportCsv'])->name('export-csv');
    });

    // System Governance
    Route::middleware(['can:manage-system', 'throttle:admin-sensitive'])->group(function () {
        Route::get('audit-logs', [\App\Http\Controllers\Admin\AuditLogController::class, 'index'])->name('audit-logs.index');
        Route::get('audit-logs/{auditLog}', [\App\Http\Controllers\Admin\AuditLogController::class, 'show'])->name('audit-logs.show');
        Route::get('settings', [\App\Http\Controllers\Admin\SettingController::class, 'index'])->name('settings.index');
        Route::match(['put', 'post'], 'settings', [\App\Http\Controllers\Admin\SettingController::class, 'update'])->name('settings.update');
    });

    // AI Assistant Admin (Knowledge Base & Avatars)
    Route::prefix('ai')->name('ai.')->group(function () {
        Route::get('/', [AiAdminDashboardController::class, 'index'])->name('dashboard');
        
        // Knowledge Sources
        Route::resource('sources', AiSourceWebController::class);
        Route::post('sources/{source}/reprocess', [AiSourceWebController::class, 'reprocess'])->name('sources.reprocess');
        Route::post('sources/{source}/reindex', [AiSourceWebController::class, 'reindex'])->name('sources.reindex');
        Route::patch('sources/{source}/toggle', [AiSourceWebController::class, 'toggleActive'])->name('sources.toggle');
        
        // Avatars
        Route::resource('avatars', AiAvatarWebController::class);
        Route::patch('avatars/{avatar}/toggle', [AiAvatarWebController::class, 'toggleActive'])->name('avatars.toggle');
        
        // Scene Prompts
        Route::resource('scene-prompts', AiScenePromptWebController::class)->except(['show', 'create', 'edit']);
        
        // Logs
        Route::get('logs', [AiLogWebController::class, 'index'])->name('logs.index');
    });
});
