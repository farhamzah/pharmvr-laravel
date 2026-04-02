<?php

namespace Tests\Feature\Api\V1\Ai;

use App\Models\User;
use App\Models\PharmaiConversation;
use App\Models\VrSession;
use App\Models\AiUsageLog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AiUsageTrackingTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    /** @test */
    public function it_logs_usage_when_sending_app_chat_message()
    {
        $conversation = PharmaiConversation::factory()->create(['user_id' => $this->user->id]);

        $response = $this->actingAs($this->user)
            ->postJson("/api/v1/ai/conversations/{$conversation->id}/messages", [
                'message' => 'Tell me more about GMP'
            ]);

        $response->assertStatus(201);

        $this->assertDatabaseHas('ai_usage_logs', [
            'user_id' => $this->user->id,
            'interaction_type' => 'app_chat',
            'conversation_id' => $conversation->id,
            'provider_name' => 'mock'
        ]);

        $log = AiUsageLog::first();
        $this->assertNotNull($log->latency_ms);
        $this->assertNotNull($log->total_tokens);
    }

    /** @test */
    public function it_logs_usage_when_generating_vr_feedback()
    {
        $session = VrSession::factory()->create([
            'user_id' => $this->user->id,
            'session_status' => 'playing'
        ]);

        $response = $this->actingAs($this->user)
            ->postJson("/api/v1/vr/ai/feedback", [
                'session_id' => $session->id,
                'event_type' => 'sterile_breach',
                'event' => ['item' => 'table'],
                'module_slug' => 'sterile-area'
            ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('ai_usage_logs', [
            'user_id' => $this->user->id,
            'interaction_type' => 'vr_feedback',
            'vr_session_id' => $session->id,
            'domain_mode' => 'session_evaluator'
        ]);
        
        $log = AiUsageLog::where('interaction_type', 'vr_feedback')->first();
        $this->assertEquals(true, ($log->metadata['is_voice'] ?? false));
    }
}
