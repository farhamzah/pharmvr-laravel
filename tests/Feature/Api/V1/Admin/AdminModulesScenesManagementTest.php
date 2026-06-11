<?php

namespace Tests\Feature\Api\V1\Admin;

use App\Models\Scene;
use App\Models\TrainingModule;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminModulesScenesManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_unauthenticated_user_cannot_list_modules_or_scenes(): void
    {
        $this->getJson('/api/v1/admin/modules')->assertUnauthorized();
        $this->getJson('/api/v1/admin/scenes')->assertUnauthorized();
    }

    public function test_student_cannot_list_modules_or_scenes(): void
    {
        $student = $this->makeUser(User::ROLE_STUDENT);

        $this->actingAs($student)
            ->getJson('/api/v1/admin/modules')
            ->assertForbidden()
            ->assertJsonPath('success', false);

        $this->actingAs($student)
            ->getJson('/api/v1/admin/scenes')
            ->assertForbidden()
            ->assertJsonPath('success', false);
    }

    public function test_instructor_can_list_modules_and_scenes(): void
    {
        $instructor = $this->makeUser(User::ROLE_INSTRUCTOR);
        $module = $this->makeModule('Hygiene Module', 'hygiene');
        $scene = $this->makeScene($module, 'hygiene', 'Hygiene Scene');

        $this->actingAs($instructor)
            ->getJson('/api/v1/admin/modules?search=Hygiene&type=production&status=active')
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', 'Modules retrieved.')
            ->assertJsonPath('meta.total', 1)
            ->assertJsonFragment([
                'id' => $module->id,
                'slug' => 'hygiene',
                'type' => 'production',
                'status' => 'active',
                'scene_slug' => 'hygiene',
            ]);

        $this->actingAs($instructor)
            ->getJson('/api/v1/admin/scenes?search=Hygiene&type=production&status=active')
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', 'Scenes retrieved.')
            ->assertJsonPath('meta.total', 1)
            ->assertJsonFragment([
                'id' => $scene->id,
                'slug' => 'hygiene',
                'type' => 'production',
                'status' => 'active',
                'module_slug' => 'hygiene',
            ]);
    }

    public function test_admin_can_view_module_and_scene_detail(): void
    {
        $admin = $this->makeUser(User::ROLE_ADMIN);
        $module = $this->makeModule('Warehouse Module', 'warehouse');
        $scene = $this->makeScene($module, 'warehouse', 'Warehouse Scene', [
            'learning_objectives' => ['Review receiving flow'],
        ]);

        $this->actingAs($admin)
            ->getJson("/api/v1/admin/modules/{$module->id}")
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', 'Module retrieved.')
            ->assertJsonPath('data.type', 'support')
            ->assertJsonPath('data.learning_objectives.0', 'Review receiving flow')
            ->assertJsonPath('data.scenes.0.id', $scene->id);

        $this->actingAs($admin)
            ->getJson("/api/v1/admin/scenes/{$scene->id}")
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', 'Scene retrieved.')
            ->assertJsonPath('data.type', 'support')
            ->assertJsonPath('data.module_slug', 'warehouse')
            ->assertJsonPath('data.learning_objectives.0', 'Review receiving flow');
    }

    public function test_instructor_cannot_update_module_or_scene(): void
    {
        $instructor = $this->makeUser(User::ROLE_INSTRUCTOR);
        $module = $this->makeModule();
        $scene = $this->makeScene($module);

        $this->actingAs($instructor)
            ->patchJson("/api/v1/admin/modules/{$module->id}", ['status' => 'inactive'])
            ->assertForbidden()
            ->assertJsonPath('success', false);

        $this->actingAs($instructor)
            ->patchJson("/api/v1/admin/scenes/{$scene->id}", ['status' => 'inactive'])
            ->assertForbidden()
            ->assertJsonPath('success', false);
    }

    public function test_admin_can_update_module_metadata(): void
    {
        $admin = $this->makeUser(User::ROLE_ADMIN);
        $module = $this->makeModule();
        $scene = $this->makeScene($module, 'hygiene');

        $this->actingAs($admin)
            ->patchJson("/api/v1/admin/modules/{$module->id}", [
                'title' => 'Updated Module',
                'description' => 'Updated module copy.',
                'status' => 'inactive',
                'difficulty' => 'Intermediate',
                'estimated_duration' => 42,
                'order' => 7,
                'scene_slug' => 'updated_hygiene',
            ])
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', 'Module updated.')
            ->assertJsonPath('data.title', 'Updated Module')
            ->assertJsonPath('data.status', 'inactive')
            ->assertJsonPath('data.order', 7)
            ->assertJsonPath('data.scene_slug', 'updated_hygiene');

        $this->assertDatabaseHas('training_modules', [
            'id' => $module->id,
            'title' => 'Updated Module',
            'is_active' => false,
            'estimated_duration' => 42,
        ]);

        $this->assertDatabaseHas('scenes', [
            'id' => $scene->id,
            'slug' => 'updated_hygiene',
            'order_index' => 7,
        ]);
    }

    public function test_admin_can_update_scene_metadata(): void
    {
        $admin = $this->makeUser(User::ROLE_ADMIN);
        $module = $this->makeModule('Source Module', 'source-module');
        $targetModule = $this->makeModule('Target Module', 'target-module');
        $scene = $this->makeScene($module, 'gowning');

        $this->actingAs($admin)
            ->patchJson("/api/v1/admin/scenes/{$scene->id}", [
                'name' => 'Updated Scene',
                'description' => 'Updated scene copy.',
                'status' => 'inactive',
                'difficulty' => 'advanced',
                'estimated_duration_minutes' => 24,
                'order' => 3,
                'module_slug' => 'target-module',
            ])
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', 'Scene updated.')
            ->assertJsonPath('data.name', 'Updated Scene')
            ->assertJsonPath('data.status', 'inactive')
            ->assertJsonPath('data.order', 3)
            ->assertJsonPath('data.module_slug', 'target-module');

        $this->assertDatabaseHas('scenes', [
            'id' => $scene->id,
            'title' => 'Updated Scene',
            'is_active' => false,
            'estimated_minutes' => 24,
            'order_index' => 3,
            'training_module_id' => $targetModule->id,
        ]);
    }

    public function test_invalid_status_and_type_are_rejected(): void
    {
        $admin = $this->makeUser(User::ROLE_ADMIN);
        $module = $this->makeModule();
        $scene = $this->makeScene($module);

        $this->actingAs($admin)
            ->getJson('/api/v1/admin/modules?type=unknown')
            ->assertUnprocessable()
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', 'Validation failed.');

        $this->actingAs($admin)
            ->patchJson("/api/v1/admin/modules/{$module->id}", ['status' => 'archived'])
            ->assertUnprocessable()
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', 'Validation failed.');

        $this->actingAs($admin)
            ->patchJson("/api/v1/admin/scenes/{$scene->id}", ['type' => 'unknown'])
            ->assertUnprocessable()
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', 'Validation failed.');
    }

    private function makeUser(string $role, string $name = 'Test User'): User
    {
        return User::factory()->create([
            'name' => $name,
            'role' => $role,
            'status' => User::STATUS_ACTIVE,
        ]);
    }

    private function makeModule(string $title = 'Training Module', string $slug = 'training-module'): TrainingModule
    {
        return TrainingModule::factory()->create([
            'title' => $title,
            'slug' => $slug,
            'is_active' => true,
        ]);
    }

    private function makeScene(
        TrainingModule $module,
        string $slug = 'training_room',
        string $title = 'Training Scene',
        array $overrides = []
    ): Scene {
        return Scene::create(array_merge([
            'training_module_id' => $module->id,
            'slug' => $slug,
            'title' => $title,
            'description' => 'Scene description.',
            'learning_objectives' => ['Learn the workflow'],
            'order_index' => 1,
            'priority' => 'P0',
            'difficulty' => 'beginner',
            'estimated_minutes' => 15,
            'environment_asset' => null,
            'is_active' => true,
            'required_previous_scene_id' => null,
        ], $overrides));
    }
}
