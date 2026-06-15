<?php

namespace Tests\Feature\Api\V1\Vr;

use App\Models\User;
use App\Models\VrSceneContent;
use Database\Seeders\VrSceneContentSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VrSceneContentTest extends TestCase
{
    use RefreshDatabase;

    public function test_seeded_cpob_12_aspects_content_exists(): void
    {
        $this->seed(VrSceneContentSeeder::class);

        $content = VrSceneContent::where('scene_slug', 'gmp_standard_room')
            ->where('content_key', 'cpob_12_aspects')
            ->where('locale', 'id')
            ->first();

        $this->assertNotNull($content);
        $this->assertSame('grid_panel', $content->content_type);
        $this->assertSame('12 Aspek CPOB / GMP', $content->title);
        $this->assertCount(12, $content->items_json);
    }

    public function test_api_returns_published_cpob_12_aspects_for_gmp_standard_room(): void
    {
        $this->seed(VrSceneContentSeeder::class);

        $this->getJson('/api/v1/vr/scenes/gmp_standard_room/content?locale=id&content_key=cpob_12_aspects')
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', 'VR scene content retrieved.')
            ->assertJsonPath('meta.scene_slug', 'gmp_standard_room')
            ->assertJsonPath('meta.total', 1)
            ->assertJsonPath('data.0.content_key', 'cpob_12_aspects')
            ->assertJsonPath('data.0.content_type', 'grid_panel')
            ->assertJsonPath('data.0.title', '12 Aspek CPOB / GMP')
            ->assertJsonPath('data.0.items.0.number', 1)
            ->assertJsonPath('data.0.items.0.title', 'Sistem Mutu')
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [[
                    'id',
                    'scene_slug',
                    'content_key',
                    'content_type',
                    'locale',
                    'title',
                    'subtitle',
                    'body',
                    'items' => [[
                        'number',
                        'title',
                        'description',
                        'location_hint',
                        'cpob_key',
                        'is_active',
                        'sort_order',
                    ]],
                    'metadata',
                    'sort_order',
                    'status',
                    'version',
                    'updated_at',
                ]],
                'meta',
                'errors',
            ]);
    }

    public function test_inactive_and_draft_content_are_not_returned_by_default(): void
    {
        VrSceneContent::create([
            'scene_slug' => 'gmp_standard_room',
            'content_key' => 'cpob_12_aspects',
            'content_type' => 'grid_panel',
            'locale' => 'id',
            'title' => 'Inactive Content',
            'items_json' => [],
            'is_active' => false,
            'status' => VrSceneContent::STATUS_PUBLISHED,
            'version' => 1,
        ]);

        VrSceneContent::create([
            'scene_slug' => 'gmp_standard_room',
            'content_key' => 'cpob_12_aspects',
            'content_type' => 'grid_panel',
            'locale' => 'id',
            'title' => 'Draft Content',
            'items_json' => [],
            'is_active' => true,
            'status' => VrSceneContent::STATUS_DRAFT,
            'version' => 2,
        ]);

        $this->getJson('/api/v1/vr/scenes/gmp_standard_room/content?locale=id&content_key=cpob_12_aspects')
            ->assertOk()
            ->assertJsonPath('meta.total', 0)
            ->assertJsonPath('data', []);
    }

    public function test_admin_can_create_and_update_vr_scene_content(): void
    {
        $admin = User::factory()->create([
            'role' => User::ROLE_ADMIN,
            'status' => User::STATUS_ACTIVE,
        ]);

        $payload = [
            'scene_slug' => 'gmp_standard_room',
            'content_key' => 'cpob_12_aspects_custom',
            'content_type' => 'grid_panel',
            'locale' => 'id',
            'title' => 'Custom CPOB Panel',
            'items_json' => [
                [
                    'number' => 1,
                    'title' => 'Sistem Mutu',
                    'description' => 'Description',
                    'location_hint' => 'Panel',
                    'cpob_key' => 'quality_system',
                    'is_active' => true,
                    'sort_order' => 1,
                ],
            ],
            'status' => VrSceneContent::STATUS_DRAFT,
            'is_active' => true,
        ];

        $response = $this->actingAs($admin)
            ->postJson('/api/v1/admin/vr-scene-contents', $payload)
            ->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.title', 'Custom CPOB Panel');

        $contentId = $response->json('data.id');

        $this->actingAs($admin)
            ->patchJson("/api/v1/admin/vr-scene-contents/{$contentId}", [
                'title' => 'Published CPOB Panel',
                'status' => VrSceneContent::STATUS_PUBLISHED,
            ])
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.title', 'Published CPOB Panel')
            ->assertJsonPath('data.status', VrSceneContent::STATUS_PUBLISHED);

        $this->assertDatabaseHas('vr_scene_contents', [
            'id' => $contentId,
            'title' => 'Published CPOB Panel',
            'status' => VrSceneContent::STATUS_PUBLISHED,
        ]);
    }

    public function test_invalid_admin_payload_is_rejected(): void
    {
        $admin = User::factory()->create([
            'role' => User::ROLE_ADMIN,
            'status' => User::STATUS_ACTIVE,
        ]);

        $this->actingAs($admin)
            ->postJson('/api/v1/admin/vr-scene-contents', [
                'scene_slug' => '',
                'content_key' => 'cpob_12_aspects',
                'content_type' => 'unknown',
                'locale' => 'id',
                'title' => '',
                'items_json' => 'not json',
            ])
            ->assertUnprocessable()
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', 'Validation failed.');
    }
}
