<?php

namespace Tests\Feature\Api\V1\Analytics;

use App\Models\User;
use App\Models\VrDevice;
use App\Models\VrSession;
use App\Models\TrainingModule;
use App\Models\SessionAnalytics;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;
use Carbon\Carbon;

class AnalyticsTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $device;
    protected $module;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->device = VrDevice::create([
            'user_id' => $this->user->id,
            'headset_identifier' => 'QUEST-ANALYTICS-TEST',
            'device_token_hash' => Hash::make('token'),
            'status' => 'active',
        ]);
        $this->module = TrainingModule::create([
            'title' => 'Dispensing Operation',
            'slug' => 'dispensing',
            'description' => 'Test',
            'is_active' => true,
        ]);
    }

    /** @test */
    public function completing_a_session_triggers_analytics_and_achievements()
    {
        $session = VrSession::create([
            'user_id' => $this->user->id,
            'device_id' => $this->device->id,
            'training_module_id' => $this->module->id,
            'session_status' => 'playing',
            'started_at' => Carbon::now()->subMinutes(10),
        ]);

        // Mock some events
        $session->events()->create([
            'user_id' => $this->user->id,
            'device_id' => $this->device->id,
            'training_module_id' => $this->module->id,
            'event_type' => 'sterile_breach',
            'event_timestamp' => now(),
            'event_payload' => []
        ]);

        $response = $this->postJson("/api/v1/vr/headset/sessions/{$session->id}/complete", [
            'device_access_token' => 'token',
            'final_step' => 'Done',
            'final_progress' => 100,
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure(['data' => ['analytics_summary']]);

        // Verify Database
        $this->assertDatabaseHas('session_analytics', [
            'vr_session_id' => $session->id,
            'breach_count' => 1,
        ]);

        // Verify Achievement (First Session)
        $this->assertDatabaseHas('user_achievements', [
            'user_id' => $this->user->id,
            'achievement_slug' => 'first-session'
        ]);

        // Verify AI Evaluation exists in metrics_json
        $analytics = SessionAnalytics::where('vr_session_id', $session->id)->first();
        $this->assertNotNull($analytics->metrics_json['ai_evaluation'] ?? null);
    }

    /** @test */
    public function a_user_can_view_analytics_overview()
    {
        // 1. Create a dummy analytic record
        $session = VrSession::create([
            'user_id' => $this->user->id,
            'device_id' => $this->device->id,
            'training_module_id' => $this->module->id,
            'session_status' => 'completed',
        ]);
        
        SessionAnalytics::create([
            'vr_session_id' => $session->id,
            'total_score' => 85,
            'accuracy_score' => 90,
            'speed_score' => 80,
            'breach_count' => 1,
            'duration_seconds' => 600,
            'completed_steps' => 5,
            'total_steps' => 5,
        ]);

        $response = $this->actingAs($this->user)->getJson('/api/v1/analytics/overview');

        $response->assertStatus(200)
            ->assertJsonPath('data.summary.total_sessions', 1)
            ->assertJsonPath('data.summary.average_score', 85);
    }

    /** @test */
    public function a_user_can_view_achievements()
    {
        $this->user->achievements()->create([
            'achievement_slug' => 'pioner',
            'earned_at' => now(),
        ]);

        $response = $this->actingAs($this->user)->getJson('/api/v1/analytics/achievements');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.achievement_slug', 'pioner');
    }
}
