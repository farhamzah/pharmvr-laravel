<?php

namespace Tests\Feature\Api\V1\Ai;

use App\Models\User;
use App\Models\PharmaiConversation;
use App\Models\PharmaiMessage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PharmaiApiContractTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    /** @test */
    public function it_matches_the_list_conversations_contract()
    {
        PharmaiConversation::create([
            'user_id' => $this->user->id,
            'title' => 'GMP Basics',
            'status' => 'active',
            'last_message_at' => now()
        ]);

        $response = $this->actingAs($this->user)->getJson('/api/v1/ai/conversations');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    '*' => ['id', 'title', 'last_message_at', 'status', 'created_at']
                ],
                'errors'
            ])
            ->assertJsonPath('success', true);
    }

    /** @test */
    public function it_matches_the_create_conversation_contract()
    {
        $response = $this->actingAs($this->user)->postJson('/api/v1/ai/conversations', [
            'title' => 'Cleanroom Protocols'
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => ['id', 'title', 'status'],
                'errors'
            ])
            ->assertJsonPath('data.title', 'Cleanroom Protocols');
    }

    /** @test */
    public function it_matches_the_conversation_detail_contract()
    {
        $conversation = PharmaiConversation::create([
            'user_id' => $this->user->id,
            'title' => 'History Test',
            'status' => 'active'
        ]);

        PharmaiMessage::create([
            'conversation_id' => $conversation->id,
            'role' => 'user',
            'content' => 'Question'
        ]);

        $response = $this->actingAs($this->user)->getJson("/api/v1/ai/conversations/{$conversation->id}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'id',
                    'title',
                    'messages' => [
                        '*' => ['id', 'role', 'content', 'created_at']
                    ]
                ]
            ]);
    }

    /** @test */
    public function it_matches_the_send_message_contract()
    {
        $conversation = PharmaiConversation::create([
            'user_id' => $this->user->id,
            'title' => 'Interaction Test'
        ]);

        $response = $this->actingAs($this->user)->postJson("/api/v1/ai/conversations/{$conversation->id}/messages", [
            'message' => 'Apa itu validasi?'
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => ['id', 'role', 'content', 'created_at', 'metadata'],
                'errors'
            ])
            ->assertJsonPath('data.role', 'assistant');
    }

    /** @test */
    public function it_matches_the_stateless_chat_contract()
    {
        $response = $this->actingAs($this->user)->postJson('/api/v1/ai/chat', [
            'message' => 'Quick check'
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => ['role', 'content', 'metadata']
            ]);
    }
}
