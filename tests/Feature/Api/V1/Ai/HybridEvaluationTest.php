<?php

namespace Tests\Feature\Api\V1\Ai;

use App\Models\User;
use App\Models\VrDevice;
use App\Models\VrSession;
use App\Models\TrainingModule;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class HybridEvaluationTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $module;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->module = TrainingModule::factory()->create(['title' => 'Sterile Area']);
    }

    /** @test */
    public function system_uses_hybrid_evaluation_for_sterile_breach()
    {
        $device = VrDevice::factory()->create(['user_id' => $this->user->id]);
        $session = VrSession::create([
            'user_id' => $this->user->id,
            'device_id' => $device->id,
            'training_module_id' => $this->module->id,
            'session_status' => 'playing',
        ]);

        $response = $this->actingAs($this->user)
            ->postJson('/api/v1/vr/ai/feedback', [
                'session_id' => $session->id,
                'event_type' => 'sterile_breach',
                'event' => ['item' => 'table'],
                'module_slug' => 'sterile-area'
            ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.mode', 'feedback')
            ->assertJsonPath('data.severity', 'critical'); // Driven by Rule Engine GMP-ST-01

        $this->assertDatabaseHas('vr_ai_interactions', [
            'vr_session_id' => $session->id,
            'severity' => 'critical',
            'trigger_event_type' => 'sterile_breach'
        ]);
        
        // Verify metadata contains rule result
        $interactionId = $response->json('data.interaction_id');
        $interaction = \App\Models\VrAiInteraction::find($interactionId);
        $this->assertEquals('GMP-ST-01', $interaction->metadata['rule_result']['rule_id']);
    }
}
