<?php

namespace Tests\Feature\Api\V1\Admin;

use App\Models\User;
use App\Models\VrSceneContent;
use App\Models\VrSceneLayout;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VrSceneLayoutManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_layout_endpoint_returns_published_layout(): void
    {
        $layout = VrSceneLayout::create($this->layoutPayload([
            'status' => VrSceneLayout::STATUS_PUBLISHED,
            'published_at' => now(),
        ]));

        $this->getJson('/api/v1/vr/scenes/gmp_standard_room/layout')
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', 'VR scene layout retrieved.')
            ->assertJsonPath('data.id', $layout->id)
            ->assertJsonPath('data.scene_slug', 'gmp_standard_room')
            ->assertJsonPath('data.layout_json.sceneSlug', 'gmp_standard_room');
    }

    public function test_public_layout_endpoint_does_not_return_draft_only_layout(): void
    {
        VrSceneLayout::create($this->layoutPayload([
            'status' => VrSceneLayout::STATUS_DRAFT,
        ]));

        $this->getJson('/api/v1/vr/scenes/gmp_standard_room/layout')
            ->assertNotFound()
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', 'Published VR scene layout not found.');
    }

    public function test_admin_can_create_draft_layout(): void
    {
        $admin = $this->makeUser(User::ROLE_ADMIN);

        $this->actingAs($admin)
            ->postJson('/api/v1/admin/vr-scene-layouts', $this->layoutPayload([
                'status' => VrSceneLayout::STATUS_DRAFT,
            ]))
            ->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', 'VR scene layout created.')
            ->assertJsonPath('data.status', VrSceneLayout::STATUS_DRAFT)
            ->assertJsonPath('data.layout_json.sceneSlug', 'gmp_standard_room');

        $this->assertDatabaseHas('vr_scene_layouts', [
            'scene_slug' => 'gmp_standard_room',
            'status' => VrSceneLayout::STATUS_DRAFT,
            'created_by' => $admin->id,
            'updated_by' => $admin->id,
        ]);
    }

    public function test_admin_can_publish_layout(): void
    {
        $admin = $this->makeUser(User::ROLE_ADMIN);
        $layout = VrSceneLayout::create($this->layoutPayload([
            'status' => VrSceneLayout::STATUS_DRAFT,
        ]));

        $this->actingAs($admin)
            ->postJson("/api/v1/admin/vr-scene-layouts/{$layout->id}/publish")
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', 'VR scene layout published.')
            ->assertJsonPath('data.status', VrSceneLayout::STATUS_PUBLISHED);

        $this->assertDatabaseHas('vr_scene_layouts', [
            'id' => $layout->id,
            'status' => VrSceneLayout::STATUS_PUBLISHED,
            'published_by' => $admin->id,
        ]);
    }

    public function test_publishing_one_layout_archives_previous_published_layout_for_same_scene(): void
    {
        $admin = $this->makeUser(User::ROLE_ADMIN);
        $previous = VrSceneLayout::create($this->layoutPayload([
            'status' => VrSceneLayout::STATUS_PUBLISHED,
            'version' => 1,
            'published_at' => now()->subDay(),
        ]));
        $next = VrSceneLayout::create($this->layoutPayload([
            'status' => VrSceneLayout::STATUS_DRAFT,
            'version' => 2,
            'layout_json' => $this->validLayout(['version' => 2]),
        ]));

        $this->actingAs($admin)
            ->postJson("/api/v1/admin/vr-scene-layouts/{$next->id}/publish")
            ->assertOk()
            ->assertJsonPath('data.id', $next->id)
            ->assertJsonPath('data.status', VrSceneLayout::STATUS_PUBLISHED);

        $this->assertDatabaseHas('vr_scene_layouts', [
            'id' => $previous->id,
            'status' => VrSceneLayout::STATUS_ARCHIVED,
        ]);

        $this->assertDatabaseHas('vr_scene_layouts', [
            'id' => $next->id,
            'status' => VrSceneLayout::STATUS_PUBLISHED,
        ]);
    }

    public function test_unknown_component_type_is_rejected(): void
    {
        $admin = $this->makeUser(User::ROLE_ADMIN);
        $layoutJson = $this->validLayout();
        $layoutJson['components'][0]['type'] = 'orbit_control_panel';

        $this->actingAs($admin)
            ->postJson('/api/v1/admin/vr-scene-layouts', $this->layoutPayload([
                'layout_json' => $layoutJson,
            ]))
            ->assertUnprocessable()
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', 'Validation failed.');
    }

    public function test_duplicate_component_ids_are_rejected(): void
    {
        $admin = $this->makeUser(User::ROLE_ADMIN);
        $layoutJson = $this->validLayout();
        $layoutJson['components'][1]['id'] = $layoutJson['components'][0]['id'];

        $this->actingAs($admin)
            ->postJson('/api/v1/admin/vr-scene-layouts', $this->layoutPayload([
                'layout_json' => $layoutJson,
            ]))
            ->assertUnprocessable()
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', 'Validation failed.');
    }

    public function test_existing_vr_scene_content_cms_endpoint_still_works(): void
    {
        VrSceneContent::create([
            'scene_slug' => 'gmp_standard_room',
            'content_key' => 'cpob_12_aspects',
            'content_type' => 'grid_panel',
            'locale' => 'id',
            'title' => '12 Aspek CPOB / GMP',
            'items_json' => [
                ['number' => 1, 'title' => 'Sistem Mutu', 'description' => 'Mutu terkendali.', 'location_hint' => 'Panel.', 'cpob_key' => 'quality_system', 'is_active' => true, 'sort_order' => 1],
            ],
            'metadata_json' => ['source' => 'test'],
            'status' => VrSceneContent::STATUS_PUBLISHED,
            'is_active' => true,
        ]);

        $this->getJson('/api/v1/vr/scenes/gmp_standard_room/content?locale=id&content_key=cpob_12_aspects')
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', 'VR scene content retrieved.')
            ->assertJsonPath('meta.total', 1)
            ->assertJsonPath('data.0.content_key', 'cpob_12_aspects')
            ->assertJsonPath('data.0.items.0.title', 'Sistem Mutu');
    }

    private function makeUser(string $role): User
    {
        return User::factory()->create([
            'role' => $role,
            'status' => User::STATUS_ACTIVE,
        ]);
    }

    private function layoutPayload(array $overrides = []): array
    {
        return array_merge([
            'scene_slug' => 'gmp_standard_room',
            'title' => 'GMP Standard Room',
            'template_key' => 'cleanroom_standard',
            'version' => 1,
            'status' => VrSceneLayout::STATUS_DRAFT,
            'layout_json' => $this->validLayout(),
            'metadata_json' => [
                'module_code' => 'PH-CPOB-00',
            ],
        ], $overrides);
    }

    private function validLayout(array $overrides = []): array
    {
        return array_merge([
            'sceneSlug' => 'gmp_standard_room',
            'contentKey' => 'cpob_12_aspects',
            'components' => [
                [
                    'id' => 'opening-briefing',
                    'type' => 'learning_panel',
                    'title' => 'Tujuan Utama',
                    'transform' => [
                        'position' => [0, 1.8, -3.5],
                        'rotation' => [0, 0, 0],
                        'scale' => [1, 1, 1],
                    ],
                ],
                [
                    'id' => 'floor-surface',
                    'type' => 'hotspot_marker',
                    'title' => 'Permukaan Lantai',
                    'transform' => [
                        'position' => [-1, 0.1, 1],
                        'rotation' => [0, 0, 0],
                        'scale' => [1, 1, 1],
                    ],
                ],
            ],
            'learningFlow' => [
                [
                    'id' => 'inspect-room',
                    'requiredComponentIds' => ['opening-briefing', 'floor-surface'],
                ],
            ],
        ], $overrides);
    }
}
