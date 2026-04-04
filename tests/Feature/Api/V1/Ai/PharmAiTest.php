<?php

namespace Tests\Feature\Api\V1\Ai;

use App\Models\User;
use App\Models\PharmaiConversation;
use App\Models\VrDevice;
use App\Models\VrSession;
use App\Models\TrainingModule;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class PharmAiTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $module;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->module = TrainingModule::create([
            'title' => 'GMP sterile production',
            'slug' => 'gmp-sterile',
            'description' => 'Test module',
            'is_active' => true,
        ]);
    }

    /** @test */
    public function a_user_can_create_and_list_conversations()
    {
        $response = $this->actingAs($this->user)->postJson('/api/v1/ai/conversations', [
            'title' => 'CPOB Basics'
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.title', 'CPOB Basics');

        $listResponse = $this->actingAs($this->user)->getJson('/api/v1/ai/conversations');
        $listResponse->assertStatus(200)
            ->assertJsonCount(1, 'data');
    }

    /** @test */
    public function a_user_can_send_a_message_and_get_mock_ai_response()
    {
        $conversation = PharmaiConversation::create([
            'user_id' => $this->user->id,
            'title' => 'Auditing Prep'
        ]);

        $response = $this->actingAs($this->user)->postJson("/api/v1/ai/conversations/{$conversation->id}/messages", [
            'message' => 'Apa itu CPOB?'
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.role', 'assistant');

        $this->assertDatabaseHas('pharmai_messages', [
            'conversation_id' => $conversation->id,
            'role' => 'user',
            'content' => 'Apa itu CPOB?'
        ]);

        $this->assertDatabaseHas('pharmai_messages', [
            'conversation_id' => $conversation->id,
            'role' => 'assistant'
        ]);
    }

    /** @test */
    public function ai_enforces_domain_restriction_mock()
    {
        $conversation = PharmaiConversation::create([
            'user_id' => $this->user->id,
            'title' => 'Offtopic Test'
        ]);

        $response = $this->actingAs($this->user)->postJson("/api/v1/ai/conversations/{$conversation->id}/messages", [
            'message' => 'Siapa pemeran utama film Iron Man?'
        ]);

        $response->assertStatus(200);
        $this->assertStringContainsString('Maaf, saya hanya dapat membantu', $response->json('data.content'));
    }

    /** @test */
    public function headset_can_trigger_ai_hint_generation()
    {
        $device = VrDevice::create([
            'user_id' => $this->user->id,
            'headset_identifier' => 'QUEST3-REFINED-TEST',
            'device_token_hash' => Hash::make('token'),
            'status' => 'active',
        ]);

        $session = VrSession::create([
            'user_id' => $this->user->id,
            'device_id' => $device->id,
            'training_module_id' => $this->module->id,
            'session_status' => 'playing',
        ]);

        $response = $this->postJson('/api/v1/vr/ai/hint', [
            'session_id' => $session->id,
            'module_slug' => 'gmp-sterile',
            'current_step' => 'gowning',
            'progress_percentage' => 25,
            'recent_events' => [
                ['event_type' => 'sterile_breach', 'event_payload' => ['item' => 'table']]
            ]
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure(['success', 'data' => [
                'interaction_id', 'mode', 'short_text', 'display_text', 'speech_text', 'severity'
            ]]);

        $this->assertDatabaseHas('vr_ai_interactions', [
            'vr_session_id' => $session->id,
            'trigger_event_type' => 'sterile_breach',
            'hint_type' => 'hint'
        ]);
    }

    /** @test */
    public function headset_can_trigger_ai_reminder_generation()
    {
        $device = VrDevice::factory()->create(['user_id' => $this->user->id]);
        $session = VrSession::create([
            'user_id' => $this->user->id,
            'device_id' => $device->id,
            'training_module_id' => $this->module->id,
            'session_status' => 'playing',
        ]);

        $response = $this->postJson('/api/v1/vr/ai/reminder', [
            'session_id' => $session->id,
            'topic' => 'hygiene',
            'module_slug' => 'gmp-sterile'
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.mode', 'reminder');
    }

    /** @test */
    public function headset_can_trigger_ai_feedback_generation()
    {
        $device = VrDevice::factory()->create(['user_id' => $this->user->id]);
        $session = VrSession::create([
            'user_id' => $this->user->id,
            'device_id' => $device->id,
            'training_module_id' => $this->module->id,
            'session_status' => 'playing',
        ]);

        $response = $this->postJson('/api/v1/vr/ai/feedback', [
            'session_id' => $session->id,
            'event_type' => 'gowning_step',
            'event' => ['event_type' => 'gowning_step', 'is_correct' => false],
            'module_slug' => 'gmp-sterile'
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.mode', 'feedback')
            ->assertJsonPath('data.severity', 'warning');
    }

    /** @test */
    public function a_user_can_use_stateless_chat()
    {
        $response = $this->actingAs($this->user)->postJson('/api/v1/ai/chat', [
            'message' => 'Apa syarat masuk cleanroom?',
            'context' => ['module' => 'sterile_basics']
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.role', 'assistant');
        
        // Mock response should contain pharma keywords
        $this->assertStringContainsString('CPOB', $response->json('data.content'));
    }
}
