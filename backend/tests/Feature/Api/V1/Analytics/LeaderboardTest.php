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

class LeaderboardTest extends TestCase
{
    use RefreshDatabase;

    protected $user1;
    protected $user2;
    protected $device;
    protected $module;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->user1 = User::factory()->create(['name' => 'Alice']);
        $this->user2 = User::factory()->create(['name' => 'Bob']);
        
        $this->module = TrainingModule::create([
            'title' => 'Sterile Prep',
            'slug' => 'sterile-prep',
            'is_active' => true,
        ]);

        $this->device = VrDevice::create([
            'user_id' => $this->user1->id,
            'headset_identifier' => 'QUEST-LEADERBOARD-TEST',
            'device_token_hash' => Hash::make('token'),
            'status' => 'active',
        ]);

        // Create Alice's session and analytics
        $session1 = VrSession::create([
            'user_id' => $this->user1->id,
            'device_id' => $this->device->id,
            'training_module_id' => $this->module->id,
            'session_status' => 'completed',
        ]);
        SessionAnalytics::create([
            'vr_session_id' => $session1->id,
            'total_score' => 95,
            'duration_seconds' => 300,
        ]);

        // Create Bob's session and analytics
        $session2 = VrSession::create([
            'user_id' => $this->user2->id,
            'device_id' => $this->device->id,
            'training_module_id' => $this->module->id,
            'session_status' => 'completed',
        ]);
        SessionAnalytics::create([
            'vr_session_id' => $session2->id,
            'total_score' => 85,
            'duration_seconds' => 400,
        ]);
    }

    /** @test */
    public function users_can_view_global_leaderboard()
    {
        $response = $this->actingAs($this->user1)->getJson('/api/v1/analytics/leaderboard/global');

        $response->assertStatus(200)
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('data.0.name', 'Alice')
            ->assertJsonPath('data.0.top_score', 95)
            ->assertJsonPath('data.1.name', 'Bob');
    }

    /** @test */
    public function users_can_view_module_specific_leaderboard()
    {
        $response = $this->actingAs($this->user1)->getJson("/api/v1/analytics/leaderboard/modules/{$this->module->slug}");

        $response->assertStatus(200)
            ->assertJsonPath('data.module.slug', $this->module->slug)
            ->assertJsonCount(2, 'data.rankings');
    }

    /** @test */
    public function users_can_view_their_unified_progress()
    {
        $response = $this->actingAs($this->user1)->getJson('/api/v1/analytics/user/progress');

        $response->assertStatus(200)
            ->assertJsonPath('data.completion_stats.completed_modules', 1)
            ->assertJsonPath('data.activity_summary.total_sessions', 1);
    }
}
