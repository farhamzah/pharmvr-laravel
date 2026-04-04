<?php

namespace Tests\Feature\Api\V1\Ai;

use App\Models\User;
use App\Models\AiAvatarProfile;
use App\Models\AiAvatarScenePrompt;
use App\Models\TrainingModule;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AiAvatarVrIntegrationTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected AiAvatarProfile $avatar;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        
        $this->avatar = AiAvatarProfile::create([
            'name' => 'GMP Guide',
            'slug' => 'gmp-guide',
            'role_title' => 'Senior GMP Auditor',
            'greeting_text' => 'Welcome to the lab, trainee.',
            'is_active' => true
        ]);
    }

    /** @test */
    public function it_returns_greeting_guidance_correctly()
    {
        $response = $this->actingAs($this->user)->postJson('/api/v1/ai-assistant/avatar/guide', [
            'avatar_slug' => 'gmp-guide',
            'scene_key' => 'gowning_room',
            'interaction_mode' => 'greeting'
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('avatar.name', 'GMP Guide')
            ->assertJsonPath('interaction_mode', 'greeting')
            ->assertJsonPath('answer', 'Welcome to the lab, trainee.')
            ->assertJsonStructure([
                'avatar' => ['name', 'slug', 'role'],
                'answer',
                'cited_sources',
                'suggested_followups',
                'scene_context',
                'interaction_mode',
                'response_mode'
            ]);
    }

    /** @test */
    public function it_returns_explain_guidance_with_scene_prompt()
    {
        AiAvatarScenePrompt::create([
            'avatar_profile_id' => $this->avatar->id,
            'scene_key' => 'gowning_room',
            'prompt_text' => 'In this room, you must wear personal protective equipment.',
            'suggested_questions_json' => ['Why use PPE?', 'Where is the coat?'],
            'is_active' => true
        ]);

        $response = $this->actingAs($this->user)->postJson('/api/v1/ai-assistant/avatar/guide', [
            'avatar_slug' => 'gmp-guide',
            'scene_key' => 'gowning_room',
            'interaction_mode' => 'explain'
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('interaction_mode', 'explain')
            ->assertJsonPath('answer', 'In this room, you must wear personal protective equipment.')
            ->assertJsonFragment(['Why use PPE?']);
    }

    /** @test */
    public function it_returns_hint_guidance_for_specific_object()
    {
        AiAvatarScenePrompt::create([
            'avatar_profile_id' => $this->avatar->id,
            'scene_key' => 'gowning_room',
            'object_key' => 'handwash_station',
            'prompt_text' => 'Use the soap and scrub for 20 seconds.',
            'is_active' => true
        ]);

        $response = $this->actingAs($this->user)->postJson('/api/v1/ai-assistant/avatar/guide', [
            'avatar_slug' => 'gmp-guide',
            'scene_key' => 'gowning_room',
            'object_key' => 'handwash_station',
            'interaction_mode' => 'hint'
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('interaction_mode', 'hint')
            ->assertJsonPath('answer', 'Use the soap and scrub for 20 seconds.');
    }

    /** @test */
    public function it_returns_concise_response_to_question_in_vr()
    {
        $response = $this->actingAs($this->user)->postJson('/api/v1/ai-assistant/avatar/ask', [
            'avatar_slug' => 'gmp-guide',
            'question' => 'Apa itu GMP?',
            'interaction_mode' => 'ask'
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('interaction_mode', 'ask')
            ->assertJsonPath('response_mode', 'vr_concise')
            ->assertSee('Sebagai Senior GMP Auditor:');
            
        $this->assertLessThanOrEqual(250, strlen($response->json('answer')));
    }
}
