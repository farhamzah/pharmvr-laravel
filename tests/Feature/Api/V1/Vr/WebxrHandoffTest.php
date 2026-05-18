<?php

namespace Tests\Feature\Api\V1\Vr;

use App\Models\User;
use App\Models\WebxrHandoffToken;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WebxrHandoffTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_create_handoff_token(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->postJson('/api/v1/auth/webxr/handoff');

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonStructure([
                'data' => ['handoff_token', 'expires_at', 'webxr_url'],
            ]);

        $plainToken = $response->json('data.handoff_token');

        $this->assertDatabaseHas('webxr_handoff_tokens', [
            'user_id' => $user->id,
            'token_hash' => WebxrHandoffToken::hashPlainToken($plainToken),
            'used_at' => null,
        ]);
    }

    public function test_unauthenticated_user_cannot_create_handoff_token(): void
    {
        $this->postJson('/api/v1/auth/webxr/handoff')
            ->assertUnauthorized();
    }

    public function test_valid_handoff_token_can_be_exchanged_once(): void
    {
        $user = User::factory()->create();
        $plainToken = 'test-webxr-handoff-token';

        WebxrHandoffToken::create([
            'user_id' => $user->id,
            'token_hash' => WebxrHandoffToken::hashPlainToken($plainToken),
            'expires_at' => now()->addMinute(),
        ]);

        $response = $this->postJson('/api/v1/auth/webxr/exchange', [
            'handoff_token' => $plainToken,
        ]);

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonStructure([
                'data' => ['token', 'token_type', 'user'],
            ])
            ->assertJsonPath('data.token_type', 'Bearer')
            ->assertJsonPath('data.user.id', $user->id);

        $this->assertNotNull(WebxrHandoffToken::first()->used_at);
    }

    public function test_reused_handoff_token_is_rejected(): void
    {
        $user = User::factory()->create();
        $plainToken = 'used-webxr-handoff-token';

        WebxrHandoffToken::create([
            'user_id' => $user->id,
            'token_hash' => WebxrHandoffToken::hashPlainToken($plainToken),
            'expires_at' => now()->addMinute(),
            'used_at' => now(),
        ]);

        $this->postJson('/api/v1/auth/webxr/exchange', [
            'handoff_token' => $plainToken,
        ])
            ->assertStatus(422)
            ->assertJsonPath('success', false);
    }

    public function test_expired_handoff_token_is_rejected(): void
    {
        $user = User::factory()->create();
        $plainToken = 'expired-webxr-handoff-token';

        WebxrHandoffToken::create([
            'user_id' => $user->id,
            'token_hash' => WebxrHandoffToken::hashPlainToken($plainToken),
            'expires_at' => now()->subSecond(),
        ]);

        $this->postJson('/api/v1/auth/webxr/exchange', [
            'handoff_token' => $plainToken,
        ])
            ->assertStatus(422)
            ->assertJsonPath('success', false);
    }
}
