<?php

namespace Tests\Feature\Api\V1\Vr;

use App\Enums\AssessmentStatus;
use App\Enums\AssessmentType;
use App\Models\Assessment;
use App\Models\AssessmentAttempt;
use App\Models\Scene;
use App\Models\TrainingModule;
use App\Models\User;
use App\Models\UserTrainingProgress;
use App\Models\VrSession;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HygieneGateTest extends TestCase
{
    use RefreshDatabase;

    public function test_hygiene_requires_pretest_and_gowning_requires_hygiene_posttest(): void
    {
        $user = User::factory()->create();
        [$hygieneModule, $pretest, $posttest, $hygieneScene, $gowningScene] = $this->makeHygienePath();

        $this->actingAs($user)->getJson('/api/v1/vr/scenes')
            ->assertOk()
            ->assertJsonFragment([
                'slug' => 'hygiene',
                'is_locked' => true,
            ]);

        AssessmentAttempt::create([
            'user_id' => $user->id,
            'assessment_id' => $pretest->id,
            'score' => 0,
            'passed' => true,
            'status' => 'completed',
            'started_at' => now()->subMinute(),
            'completed_at' => now(),
        ]);

        $readiness = $this->actingAs($user)->getJson('/api/v1/vr/modules/hygiene/launch-readiness');
        $readiness->assertOk()
            ->assertJsonPath('data.pre_test_completed', true)
            ->assertJsonPath('data.can_launch_vr', true)
            ->assertJsonPath('data.vr_status', 'available')
            ->assertJsonPath('data.next_action', 'launch_vr');

        VrSession::create([
            'user_id' => $user->id,
            'training_module_id' => $hygieneScene->training_module_id,
            'scene_id' => $hygieneScene->id,
            'session_status' => 'completed',
            'platform' => 'webxr',
            'progress_percentage' => 100,
            'started_at' => now()->subMinutes(5),
            'completed_at' => now(),
            'last_activity_at' => now(),
        ]);

        UserTrainingProgress::create([
            'user_id' => $user->id,
            'training_module_id' => $hygieneModule->id,
            'pre_test_status' => 'passed',
            'vr_status' => 'completed',
            'post_test_status' => 'available',
            'last_active_step' => 'post_test',
            'status' => 'in_progress',
            'completion_percentage' => 50,
        ]);

        $this->actingAs($user)->getJson('/api/v1/vr/modules/hygiene/launch-readiness')
            ->assertOk()
            ->assertJsonPath('data.vr_status', 'completed')
            ->assertJsonPath('data.post_test_passed', false)
            ->assertJsonPath('data.can_launch_vr', false)
            ->assertJsonPath('data.next_action', 'posttest_required');

        $this->assertFalse($gowningScene->fresh()->isUnlockedFor($user));

        AssessmentAttempt::create([
            'user_id' => $user->id,
            'assessment_id' => $posttest->id,
            'score' => 70,
            'passed' => true,
            'status' => 'completed',
            'started_at' => now()->subMinute(),
            'completed_at' => now(),
        ]);

        $this->assertTrue($gowningScene->fresh()->isUnlockedFor($user));
        $this->assertSame($hygieneModule->id, $posttest->module_id);

        $this->actingAs($user)->getJson('/api/v1/vr/modules/hygiene/launch-readiness')
            ->assertOk()
            ->assertJsonPath('data.post_test_passed', true)
            ->assertJsonPath('data.next_action', 'next_scene_unlocked')
            ->assertJsonPath('data.legacy_next_action', 'gowning_unlocked')
            ->assertJsonPath('data.next_scene_slug', 'gowning');
    }

    public function test_gowning_follows_generic_production_path_matrix(): void
    {
        $user = User::factory()->create();
        [$hygieneModule, $hygienePretest, $hygienePosttest, $hygieneScene, $gowningScene, $gowningModule, $gowningPretest, $gowningPosttest] = $this->makeHygienePath();

        $this->actingAs($user)->getJson('/api/v1/vr/modules/gowning/launch-readiness')
            ->assertOk()
            ->assertJsonPath('data.scene_slug', 'gowning')
            ->assertJsonPath('data.scene_unlocked', false)
            ->assertJsonPath('data.previous_scene_slug', 'hygiene')
            ->assertJsonPath('data.can_launch_vr', false);

        AssessmentAttempt::create([
            'user_id' => $user->id,
            'assessment_id' => $hygienePretest->id,
            'score' => 0,
            'passed' => true,
            'status' => 'completed',
            'started_at' => now()->subMinutes(10),
            'completed_at' => now()->subMinutes(9),
        ]);

        VrSession::create([
            'user_id' => $user->id,
            'training_module_id' => $hygieneScene->training_module_id,
            'scene_id' => $hygieneScene->id,
            'session_status' => 'completed',
            'platform' => 'webxr',
            'progress_percentage' => 100,
            'started_at' => now()->subMinutes(8),
            'completed_at' => now()->subMinutes(7),
            'last_activity_at' => now()->subMinutes(7),
        ]);

        AssessmentAttempt::create([
            'user_id' => $user->id,
            'assessment_id' => $hygienePosttest->id,
            'score' => 80,
            'passed' => true,
            'status' => 'completed',
            'started_at' => now()->subMinutes(6),
            'completed_at' => now()->subMinutes(5),
        ]);

        $this->actingAs($user)->getJson('/api/v1/vr/modules/gowning/launch-readiness')
            ->assertOk()
            ->assertJsonPath('data.scene_unlocked', true)
            ->assertJsonPath('data.pre_test_completed', false)
            ->assertJsonPath('data.next_action', 'pretest_required')
            ->assertJsonPath('data.recommended_next_route', '/assessments/gowning/pre_test');

        AssessmentAttempt::create([
            'user_id' => $user->id,
            'assessment_id' => $gowningPretest->id,
            'score' => 0,
            'passed' => true,
            'status' => 'completed',
            'started_at' => now()->subMinutes(4),
            'completed_at' => now()->subMinutes(3),
        ]);

        $this->actingAs($user)->getJson('/api/v1/vr/modules/gowning/launch-readiness')
            ->assertOk()
            ->assertJsonPath('data.pre_test_completed', true)
            ->assertJsonPath('data.vr_status', 'available')
            ->assertJsonPath('data.can_launch_vr', true)
            ->assertJsonPath('data.next_action', 'launch_vr');

        VrSession::create([
            'user_id' => $user->id,
            'training_module_id' => $gowningScene->training_module_id,
            'scene_id' => $gowningScene->id,
            'session_status' => 'completed',
            'platform' => 'webxr',
            'progress_percentage' => 100,
            'started_at' => now()->subMinutes(2),
            'completed_at' => now()->subMinute(),
            'last_activity_at' => now()->subMinute(),
        ]);

        UserTrainingProgress::create([
            'user_id' => $user->id,
            'training_module_id' => $gowningModule->id,
            'pre_test_status' => 'passed',
            'vr_status' => 'completed',
            'post_test_status' => 'available',
            'last_active_step' => 'post_test',
            'status' => 'in_progress',
            'completion_percentage' => 50,
        ]);

        $this->actingAs($user)->getJson('/api/v1/vr/modules/gowning/launch-readiness')
            ->assertOk()
            ->assertJsonPath('data.vr_status', 'completed')
            ->assertJsonPath('data.post_test_passed', false)
            ->assertJsonPath('data.next_action', 'posttest_required')
            ->assertJsonPath('data.recommended_next_route', '/assessments/gowning/post_test');
    }

    private function makeHygienePath(): array
    {
        $productionModule = TrainingModule::factory()->create(['slug' => 'solid-dosage-path']);
        $hygieneModule = TrainingModule::factory()->create(['slug' => 'hygiene', 'title' => 'Hygiene']);
        $gowningModule = TrainingModule::factory()->create(['slug' => 'gowning', 'title' => 'Gowning']);

        $pretest = Assessment::create([
            'module_id' => $hygieneModule->id,
            'type' => AssessmentType::PRETEST->value,
            'title' => 'Pre-Test Hygiene',
            'status' => AssessmentStatus::ACTIVE->value,
            'number_of_questions_to_take' => 5,
            'passing_score' => 0,
            'time_limit_minutes' => 10,
        ]);

        $posttest = Assessment::create([
            'module_id' => $hygieneModule->id,
            'type' => AssessmentType::POSTTEST->value,
            'title' => 'Post-Test Hygiene',
            'status' => AssessmentStatus::ACTIVE->value,
            'number_of_questions_to_take' => 5,
            'passing_score' => 70,
            'time_limit_minutes' => 10,
        ]);

        $gowningPretest = Assessment::create([
            'module_id' => $gowningModule->id,
            'type' => AssessmentType::PRETEST->value,
            'title' => 'Pre-Test Gowning',
            'status' => AssessmentStatus::ACTIVE->value,
            'number_of_questions_to_take' => 5,
            'passing_score' => 0,
            'time_limit_minutes' => 10,
        ]);

        $gowningPosttest = Assessment::create([
            'module_id' => $gowningModule->id,
            'type' => AssessmentType::POSTTEST->value,
            'title' => 'Post-Test Gowning',
            'status' => AssessmentStatus::ACTIVE->value,
            'number_of_questions_to_take' => 5,
            'passing_score' => 70,
            'time_limit_minutes' => 10,
        ]);

        $hygieneScene = Scene::create([
            'training_module_id' => $productionModule->id,
            'slug' => 'hygiene',
            'title' => 'Hygiene',
            'order_index' => 1,
            'priority' => 'P0',
            'difficulty' => 'beginner',
            'estimated_minutes' => 10,
            'environment_asset' => 'hygiene',
            'is_active' => true,
        ]);

        $gowningScene = Scene::create([
            'training_module_id' => $productionModule->id,
            'slug' => 'gowning',
            'title' => 'Gowning',
            'order_index' => 2,
            'priority' => 'P0',
            'difficulty' => 'beginner',
            'estimated_minutes' => 10,
            'environment_asset' => 'gowning',
            'is_active' => true,
            'required_previous_scene_id' => $hygieneScene->id,
        ]);

        return [
            $hygieneModule,
            $pretest,
            $posttest,
            $hygieneScene,
            $gowningScene,
            $gowningModule,
            $gowningPretest,
            $gowningPosttest,
        ];
    }
}
