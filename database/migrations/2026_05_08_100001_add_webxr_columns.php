<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add event_category to vr_session_events for classifying
     * telemetry, step_complete, mistake, and score events.
     * Also make device_id nullable on vr_sessions for WebXR (browser-based) sessions.
     */
    public function up(): void
    {
        // Add event_category to vr_session_events
        Schema::table('vr_session_events', function (Blueprint $table) {
            $table->enum('event_category', ['telemetry', 'step_complete', 'mistake', 'score', 'system'])
                ->default('telemetry')
                ->after('event_type')
                ->index();
        });

        // Add scene_id to vr_sessions for scene-level tracking
        Schema::table('vr_sessions', function (Blueprint $table) {
            $table->foreignId('scene_id')->nullable()->after('training_module_id')
                ->constrained()->onDelete('set null');
            $table->string('platform', 50)->default('webxr')->after('session_status');
            $table->unsignedInteger('total_score')->nullable()->after('progress_percentage');
            $table->unsignedInteger('duration_seconds')->nullable()->after('total_score');
            $table->unsignedInteger('total_mistakes')->nullable()->after('duration_seconds');
        });

        // Make device_id nullable on vr_sessions (WebXR has no device record)
        Schema::table('vr_sessions', function (Blueprint $table) {
            $table->foreignId('device_id')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vr_session_events', function (Blueprint $table) {
            $table->dropColumn('event_category');
        });

        Schema::table('vr_sessions', function (Blueprint $table) {
            $table->dropForeign(['scene_id']);
            $table->dropColumn(['scene_id', 'platform', 'total_score', 'duration_seconds', 'total_mistakes']);
        });
    }
};
