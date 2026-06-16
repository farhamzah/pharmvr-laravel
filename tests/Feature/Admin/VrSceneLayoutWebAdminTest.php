<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use App\Models\VrSceneLayout;
use Database\Seeders\VrSceneLayoutSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VrSceneLayoutWebAdminTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_vr_scene_layouts_index_and_create_form(): void
    {
        $this->seed(VrSceneLayoutSeeder::class);
        $admin = $this->makeAdmin();

        $this->actingAs($admin)
            ->get('/admin')
            ->assertOk()
            ->assertSee('VR Scene Layouts');

        $this->actingAs($admin)
            ->get('/admin/vr-scene-layouts')
            ->assertOk()
            ->assertSee('gmp_standard_room')
            ->assertSee('GMP Standard Room')
            ->assertSee('cleanroom_standard');

        $this->actingAs($admin)
            ->get('/admin/vr-scene-layouts/create')
            ->assertOk()
            ->assertSee('Create VR Scene Layout')
            ->assertSee('layout_json')
            ->assertSee('Component Types')
            ->assertSee('hotspot_marker')
            ->assertSee('JSON Snippets')
            ->assertSee('Transform Guidance');
    }

    public function test_admin_can_create_valid_draft_layout(): void
    {
        $admin = $this->makeAdmin();

        $this->actingAs($admin)
            ->post('/admin/vr-scene-layouts', $this->formPayload([
                'status' => VrSceneLayout::STATUS_DRAFT,
            ]))
            ->assertRedirect();

        $this->assertDatabaseHas('vr_scene_layouts', [
            'scene_slug' => 'gmp_standard_room',
            'title' => 'GMP Standard Room Draft',
            'status' => VrSceneLayout::STATUS_DRAFT,
            'created_by' => $admin->id,
            'updated_by' => $admin->id,
        ]);
    }

    public function test_web_admin_rejects_invalid_json_payload(): void
    {
        $admin = $this->makeAdmin();

        $this->actingAs($admin)
            ->from('/admin/vr-scene-layouts/create')
            ->post('/admin/vr-scene-layouts', $this->formPayload([
                'layout_json' => '{not valid json',
            ]))
            ->assertRedirect('/admin/vr-scene-layouts/create')
            ->assertSessionHasErrors('layout_json');
    }

    public function test_web_admin_rejects_duplicate_component_ids(): void
    {
        $admin = $this->makeAdmin();
        $layout = $this->validLayout();
        $layout['components'][1]['id'] = $layout['components'][0]['id'];

        $this->actingAs($admin)
            ->from('/admin/vr-scene-layouts/create')
            ->post('/admin/vr-scene-layouts', $this->formPayload([
                'layout_json' => json_encode($layout, JSON_THROW_ON_ERROR),
            ]))
            ->assertRedirect('/admin/vr-scene-layouts/create')
            ->assertSessionHasErrors('layout_json.components.1.id');
    }

    public function test_web_admin_rejects_unknown_component_type(): void
    {
        $admin = $this->makeAdmin();
        $layout = $this->validLayout();
        $layout['components'][0]['type'] = 'unknown_component';

        $this->actingAs($admin)
            ->from('/admin/vr-scene-layouts/create')
            ->post('/admin/vr-scene-layouts', $this->formPayload([
                'layout_json' => json_encode($layout, JSON_THROW_ON_ERROR),
            ]))
            ->assertRedirect('/admin/vr-scene-layouts/create')
            ->assertSessionHasErrors('layout_json.components.0.type');
    }

    public function test_admin_can_edit_layout(): void
    {
        $admin = $this->makeAdmin();
        $layout = VrSceneLayout::create($this->modelPayload());

        $this->actingAs($admin)
            ->get("/admin/vr-scene-layouts/{$layout->id}/edit")
            ->assertOk()
            ->assertSee('Edit VR Scene Layout')
            ->assertSee('View Public API')
            ->assertSee('Validation Warnings')
            ->assertSee('learning_panel')
            ->assertSee('floor_arrow')
            ->assertSee('position: [x, y, z]');

        $this->actingAs($admin)
            ->put("/admin/vr-scene-layouts/{$layout->id}", $this->formPayload([
                'title' => 'Updated Layout Title',
                'version' => 2,
            ]))
            ->assertRedirect("/admin/vr-scene-layouts/{$layout->id}/edit");

        $this->assertDatabaseHas('vr_scene_layouts', [
            'id' => $layout->id,
            'title' => 'Updated Layout Title',
            'version' => 2,
            'updated_by' => $admin->id,
        ]);
    }

    public function test_validate_layout_action_accepts_valid_layout(): void
    {
        $admin = $this->makeAdmin();
        $layout = VrSceneLayout::create($this->modelPayload());

        $this->actingAs($admin)
            ->from("/admin/vr-scene-layouts/{$layout->id}/edit")
            ->put("/admin/vr-scene-layouts/{$layout->id}", $this->formPayload([
                'editor_action' => 'validate',
            ]))
            ->assertRedirect("/admin/vr-scene-layouts/{$layout->id}/edit")
            ->assertSessionHas('layout_validation.valid', true)
            ->assertSessionHas('success', 'Layout JSON is valid.');
    }

    public function test_validate_layout_action_rejects_invalid_json(): void
    {
        $admin = $this->makeAdmin();
        $layout = VrSceneLayout::create($this->modelPayload());

        $this->actingAs($admin)
            ->from("/admin/vr-scene-layouts/{$layout->id}/edit")
            ->put("/admin/vr-scene-layouts/{$layout->id}", $this->formPayload([
                'editor_action' => 'validate',
                'layout_json' => '{not valid json',
            ]))
            ->assertRedirect("/admin/vr-scene-layouts/{$layout->id}/edit")
            ->assertSessionHasErrors('layout_json');
    }

    public function test_validate_layout_action_rejects_duplicate_component_ids(): void
    {
        $admin = $this->makeAdmin();
        $layout = VrSceneLayout::create($this->modelPayload());
        $json = $this->validLayout();
        $json['components'][1]['id'] = $json['components'][0]['id'];

        $this->actingAs($admin)
            ->from("/admin/vr-scene-layouts/{$layout->id}/edit")
            ->put("/admin/vr-scene-layouts/{$layout->id}", $this->formPayload([
                'editor_action' => 'validate',
                'layout_json' => json_encode($json, JSON_THROW_ON_ERROR),
            ]))
            ->assertRedirect("/admin/vr-scene-layouts/{$layout->id}/edit")
            ->assertSessionHas('layout_validation.valid', false);
    }

    public function test_validate_layout_action_rejects_unknown_component_type(): void
    {
        $admin = $this->makeAdmin();
        $layout = VrSceneLayout::create($this->modelPayload());
        $json = $this->validLayout();
        $json['components'][0]['type'] = 'unknown_component';

        $this->actingAs($admin)
            ->from("/admin/vr-scene-layouts/{$layout->id}/edit")
            ->put("/admin/vr-scene-layouts/{$layout->id}", $this->formPayload([
                'editor_action' => 'validate',
                'layout_json' => json_encode($json, JSON_THROW_ON_ERROR),
            ]))
            ->assertRedirect("/admin/vr-scene-layouts/{$layout->id}/edit")
            ->assertSessionHas('layout_validation.valid', false);
    }

    public function test_format_action_pretty_prints_valid_json(): void
    {
        $admin = $this->makeAdmin();
        $layout = VrSceneLayout::create($this->modelPayload());

        $this->actingAs($admin)
            ->from("/admin/vr-scene-layouts/{$layout->id}/edit")
            ->put("/admin/vr-scene-layouts/{$layout->id}", $this->formPayload([
                'editor_action' => 'format',
                'layout_json' => json_encode($this->validLayout(), JSON_THROW_ON_ERROR),
                'metadata_json' => json_encode(['module_code' => 'PH-CPOB-00'], JSON_THROW_ON_ERROR),
            ]))
            ->assertRedirect("/admin/vr-scene-layouts/{$layout->id}/edit")
            ->assertSessionHasInput('layout_json', fn (string $value) => str_contains($value, "\n    \"sceneSlug\""))
            ->assertSessionHasInput('metadata_json', fn (string $value) => str_contains($value, "\n    \"module_code\""));
    }

    public function test_admin_can_publish_layout_and_public_api_returns_it(): void
    {
        $admin = $this->makeAdmin();
        $layout = VrSceneLayout::create($this->modelPayload([
            'status' => VrSceneLayout::STATUS_DRAFT,
            'layout_json' => $this->validLayout(['title' => 'Published From UI']),
        ]));

        $this->actingAs($admin)
            ->post("/admin/vr-scene-layouts/{$layout->id}/publish")
            ->assertRedirect("/admin/vr-scene-layouts/{$layout->id}/edit");

        $this->assertDatabaseHas('vr_scene_layouts', [
            'id' => $layout->id,
            'status' => VrSceneLayout::STATUS_PUBLISHED,
            'published_by' => $admin->id,
        ]);

        $this->getJson('/api/v1/vr/scenes/gmp_standard_room/layout')
            ->assertOk()
            ->assertJsonPath('data.id', $layout->id)
            ->assertJsonPath('data.layout_json.title', 'Published From UI');
    }

    public function test_admin_can_archive_layout(): void
    {
        $admin = $this->makeAdmin();
        $layout = VrSceneLayout::create($this->modelPayload([
            'status' => VrSceneLayout::STATUS_PUBLISHED,
            'published_at' => now(),
        ]));

        $this->actingAs($admin)
            ->post("/admin/vr-scene-layouts/{$layout->id}/archive")
            ->assertRedirect("/admin/vr-scene-layouts/{$layout->id}/edit");

        $this->assertDatabaseHas('vr_scene_layouts', [
            'id' => $layout->id,
            'status' => VrSceneLayout::STATUS_ARCHIVED,
            'updated_by' => $admin->id,
        ]);
    }

    public function test_admin_can_duplicate_layout_as_draft(): void
    {
        $admin = $this->makeAdmin();
        $layout = VrSceneLayout::create($this->modelPayload([
            'status' => VrSceneLayout::STATUS_PUBLISHED,
            'version' => 3,
            'published_at' => now(),
        ]));

        $this->actingAs($admin)
            ->post("/admin/vr-scene-layouts/{$layout->id}/duplicate")
            ->assertRedirect();

        $this->assertDatabaseHas('vr_scene_layouts', [
            'scene_slug' => 'gmp_standard_room',
            'version' => 4,
            'status' => VrSceneLayout::STATUS_DRAFT,
            'created_by' => $admin->id,
        ]);
    }

    public function test_unauthenticated_users_cannot_access_admin_ui(): void
    {
        $this->get('/admin/vr-scene-layouts')
            ->assertRedirect('/admin/login');
    }

    private function makeAdmin(): User
    {
        return User::factory()->create([
            'role' => User::ROLE_SUPER_ADMIN,
            'status' => User::STATUS_ACTIVE,
        ]);
    }

    private function formPayload(array $overrides = []): array
    {
        return array_merge([
            'scene_slug' => 'gmp_standard_room',
            'title' => 'GMP Standard Room Draft',
            'template_key' => 'cleanroom_standard',
            'version' => 1,
            'status' => VrSceneLayout::STATUS_DRAFT,
            'layout_json' => json_encode($this->validLayout(), JSON_THROW_ON_ERROR),
            'metadata_json' => json_encode(['module_code' => 'PH-CPOB-00'], JSON_THROW_ON_ERROR),
        ], $overrides);
    }

    private function modelPayload(array $overrides = []): array
    {
        return array_merge([
            'scene_slug' => 'gmp_standard_room',
            'title' => 'GMP Standard Room Draft',
            'template_key' => 'cleanroom_standard',
            'version' => 1,
            'status' => VrSceneLayout::STATUS_DRAFT,
            'layout_json' => $this->validLayout(),
            'metadata_json' => ['module_code' => 'PH-CPOB-00'],
            'validation_warnings_json' => [],
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
