<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use App\Models\VrSceneContent;
use Database\Seeders\VrSceneContentSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VrSceneContentWebAdminTest extends TestCase
{
    use RefreshDatabase;

    public function test_super_admin_can_manage_vr_scene_content_from_backend_admin_panel(): void
    {
        $this->seed(VrSceneContentSeeder::class);

        $admin = User::factory()->create([
            'role' => User::ROLE_SUPER_ADMIN,
            'status' => User::STATUS_ACTIVE,
        ]);

        $content = VrSceneContent::where('scene_slug', 'gmp_standard_room')
            ->where('content_key', 'cpob_12_aspects')
            ->where('locale', 'id')
            ->firstOrFail();

        $this->actingAs($admin)
            ->get('/admin')
            ->assertOk()
            ->assertSee('VR Scene Contents');

        $this->actingAs($admin)
            ->get('/admin/vr-scene-contents')
            ->assertOk()
            ->assertSee('gmp_standard_room')
            ->assertSee('cpob_12_aspects')
            ->assertSee('12 Aspek CPOB / GMP');

        $itemsJson = json_encode($content->items_json, JSON_THROW_ON_ERROR);
        $metadataJson = json_encode($content->metadata_json ?? [], JSON_THROW_ON_ERROR);

        $this->actingAs($admin)
            ->put("/admin/vr-scene-contents/{$content->id}", [
                'scene_slug' => $content->scene_slug,
                'content_key' => $content->content_key,
                'content_type' => $content->content_type,
                'locale' => $content->locale,
                'title' => '12 Aspek CPOB / GMP Updated',
                'subtitle' => $content->subtitle,
                'body' => $content->body,
                'items_json' => $itemsJson,
                'metadata_json' => $metadataJson,
                'sort_order' => $content->sort_order,
                'is_active' => '1',
                'status' => VrSceneContent::STATUS_PUBLISHED,
                'version' => $content->version,
            ])
            ->assertRedirect("/admin/vr-scene-contents/{$content->id}/edit");

        $this->assertDatabaseHas('vr_scene_contents', [
            'id' => $content->id,
            'title' => '12 Aspek CPOB / GMP Updated',
            'status' => VrSceneContent::STATUS_PUBLISHED,
            'is_active' => true,
        ]);

        $this->getJson('/api/v1/vr/scenes/gmp_standard_room/content?locale=id&content_key=cpob_12_aspects')
            ->assertOk()
            ->assertJsonPath('data.0.title', '12 Aspek CPOB / GMP Updated');
    }

    public function test_web_admin_rejects_invalid_json_payload(): void
    {
        $content = VrSceneContent::create([
            'scene_slug' => 'gmp_standard_room',
            'content_key' => 'cpob_12_aspects',
            'content_type' => 'grid_panel',
            'locale' => 'id',
            'title' => '12 Aspek CPOB / GMP',
            'items_json' => [],
            'metadata_json' => [],
            'is_active' => true,
            'status' => VrSceneContent::STATUS_PUBLISHED,
            'version' => 1,
        ]);

        $admin = User::factory()->create([
            'role' => User::ROLE_SUPER_ADMIN,
            'status' => User::STATUS_ACTIVE,
        ]);

        $this->actingAs($admin)
            ->from("/admin/vr-scene-contents/{$content->id}/edit")
            ->put("/admin/vr-scene-contents/{$content->id}", [
                'scene_slug' => $content->scene_slug,
                'content_key' => $content->content_key,
                'content_type' => $content->content_type,
                'locale' => $content->locale,
                'title' => $content->title,
                'items_json' => '{not valid json',
                'metadata_json' => '{}',
                'sort_order' => 0,
                'is_active' => '1',
                'status' => VrSceneContent::STATUS_PUBLISHED,
                'version' => 1,
            ])
            ->assertRedirect("/admin/vr-scene-contents/{$content->id}/edit")
            ->assertSessionHasErrors('items_json');
    }
}
