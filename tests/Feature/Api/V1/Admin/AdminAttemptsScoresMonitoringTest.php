<?php

namespace Tests\Feature\Api\V1\Admin;

use App\Enums\AssessmentStatus;
use App\Enums\AssessmentType;
use App\Enums\QuestionUsageScope;
use App\Models\Assessment;
use App\Models\AssessmentAttempt;
use App\Models\QuestionBankItem;
use App\Models\QuestionBankOption;
use App\Models\Scene;
use App\Models\TrainingModule;
use App\Models\User;
use App\Models\UserAnswer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminAttemptsScoresMonitoringTest extends TestCase
{
    use RefreshDatabase;

    public function test_unauthenticated_user_cannot_list_attempts(): void
    {
        $this->getJson('/api/v1/admin/attempts')->assertUnauthorized();
    }

    public function test_student_cannot_list_attempts(): void
    {
        $student = $this->makeUser(User::ROLE_STUDENT);

        $this->actingAs($student)
            ->getJson('/api/v1/admin/attempts')
            ->assertForbidden();
    }

    public function test_instructor_can_list_attempts(): void
    {
        $instructor = $this->makeUser(User::ROLE_INSTRUCTOR);
        [$attempt] = $this->makeAttemptSet();

        $this->actingAs($instructor)
            ->getJson("/api/v1/admin/attempts?status=completed&passed=1&user_id={$attempt->user_id}")
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', 'Attempts retrieved.')
            ->assertJsonPath('meta.total', 1)
            ->assertJsonPath('data.0.id', $attempt->id)
            ->assertJsonPath('data.0.score', 85)
            ->assertJsonPath('data.0.passed', true)
            ->assertJsonPath('data.0.status', 'completed')
            ->assertJsonPath('data.0.assessment_type', AssessmentType::POSTTEST->value)
            ->assertJsonPath('data.0.user_id', $attempt->user_id);
    }

    public function test_admin_can_list_attempts_with_module_and_scene_filters(): void
    {
        $admin = $this->makeUser(User::ROLE_ADMIN);
        [$attempt, , , , $scene] = $this->makeAttemptSet();

        $this->actingAs($admin)
            ->getJson("/api/v1/admin/attempts?module_id={$attempt->assessment->module_id}&scene_id={$scene->id}&type=posttest")
            ->assertOk()
            ->assertJsonPath('meta.total', 1)
            ->assertJsonPath('data.0.module_id', $attempt->assessment->module_id)
            ->assertJsonPath('data.0.scene_id', $scene->id)
            ->assertJsonPath('data.0.scene_slug', $scene->slug);
    }

    public function test_admin_can_view_attempt_detail_with_answers(): void
    {
        $admin = $this->makeUser(User::ROLE_ADMIN);
        [$attempt, , , $question] = $this->makeAttemptSet();

        $this->actingAs($admin)
            ->getJson("/api/v1/admin/attempts/{$attempt->id}")
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', 'Attempt retrieved.')
            ->assertJsonPath('data.id', $attempt->id)
            ->assertJsonPath('data.user.id', $attempt->user_id)
            ->assertJsonPath('data.assessment.type', AssessmentType::POSTTEST->value)
            ->assertJsonPath('data.module.slug', 'hygiene')
            ->assertJsonPath('data.scene.slug', 'hygiene')
            ->assertJsonPath('data.score', 85)
            ->assertJsonPath('data.passed', true)
            ->assertJsonPath('data.status', 'completed')
            ->assertJsonPath('data.answers.0.question_id', $question->id)
            ->assertJsonPath('data.answers.0.selected_answer', 'A')
            ->assertJsonPath('data.answers.0.correct_answer', 'A')
            ->assertJsonPath('data.answers.0.is_correct', true)
            ->assertJsonPath('data.answers.0.score', 1);
    }

    public function test_instructor_can_view_attempt_detail(): void
    {
        $instructor = $this->makeUser(User::ROLE_INSTRUCTOR);
        [$attempt] = $this->makeAttemptSet();

        $this->actingAs($instructor)
            ->getJson("/api/v1/admin/attempts/{$attempt->id}")
            ->assertOk()
            ->assertJsonPath('data.id', $attempt->id);
    }

    public function test_student_cannot_view_attempt_detail(): void
    {
        $student = $this->makeUser(User::ROLE_STUDENT);
        [$attempt] = $this->makeAttemptSet();

        $this->actingAs($student)
            ->getJson("/api/v1/admin/attempts/{$attempt->id}")
            ->assertForbidden();
    }

    public function test_admin_can_view_scores_summary(): void
    {
        $admin = $this->makeUser(User::ROLE_ADMIN);
        $this->makeAttemptSet(score: 85, passed: true, status: 'completed', moduleSlug: 'hygiene-passed');
        $this->makeAttemptSet(score: 45, passed: false, status: 'failed', moduleSlug: 'hygiene-failed');

        $this->actingAs($admin)
            ->getJson('/api/v1/admin/scores/summary?type=posttest')
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', 'Scores summary retrieved.')
            ->assertJsonPath('data.total_attempts', 2)
            ->assertJsonPath('data.completed_attempts', 2)
            ->assertJsonPath('data.passed_attempts', 1)
            ->assertJsonPath('data.failed_attempts', 1)
            ->assertJsonPath('data.average_score', 65)
            ->assertJsonPath('data.average_posttest_score', 65)
            ->assertJsonPath('data.completion_rate', 100)
            ->assertJsonPath('data.pass_rate', 50);
    }

    public function test_student_cannot_view_scores_summary(): void
    {
        $student = $this->makeUser(User::ROLE_STUDENT);

        $this->actingAs($student)
            ->getJson('/api/v1/admin/scores/summary')
            ->assertForbidden();
    }

    public function test_attempt_response_includes_core_monitoring_fields(): void
    {
        $admin = $this->makeUser(User::ROLE_ADMIN);
        [$attempt] = $this->makeAttemptSet();

        $this->actingAs($admin)
            ->getJson('/api/v1/admin/attempts')
            ->assertOk()
            ->assertJsonStructure([
                'data' => [
                    [
                        'id',
                        'user_id',
                        'user_name',
                        'user_email',
                        'assessment_id',
                        'assessment_title',
                        'assessment_type',
                        'module_id',
                        'module_title',
                        'module_slug',
                        'scene_id',
                        'scene_slug',
                        'score',
                        'passing_score',
                        'passed',
                        'status',
                        'started_at',
                        'completed_at',
                    ],
                ],
            ])
            ->assertJsonPath('data.0.id', $attempt->id);
    }

    public function test_admin_attempt_endpoint_does_not_mutate_attempt_records(): void
    {
        $admin = $this->makeUser(User::ROLE_ADMIN);
        [$attempt] = $this->makeAttemptSet();
        $before = $attempt->fresh()->only(['score', 'passed', 'status', 'started_at', 'completed_at', 'updated_at']);

        $this->actingAs($admin)
            ->getJson("/api/v1/admin/attempts/{$attempt->id}")
            ->assertOk();

        $after = $attempt->fresh()->only(['score', 'passed', 'status', 'started_at', 'completed_at', 'updated_at']);

        $this->assertSame($before['score'], $after['score']);
        $this->assertSame($before['passed'], $after['passed']);
        $this->assertSame($before['status'], $after['status']);
        $this->assertEquals($before['started_at'], $after['started_at']);
        $this->assertEquals($before['completed_at'], $after['completed_at']);
        $this->assertEquals($before['updated_at'], $after['updated_at']);
    }

    private function makeUser(string $role): User
    {
        return User::factory()->create([
            'role' => $role,
            'status' => User::STATUS_ACTIVE,
        ]);
    }

    private function makeAttemptSet(
        int $score = 85,
        bool $passed = true,
        string $status = 'completed',
        string $moduleSlug = 'hygiene'
    ): array
    {
        $student = $this->makeUser(User::ROLE_STUDENT);
        $module = TrainingModule::factory()->create([
            'title' => 'Hygiene',
            'slug' => $moduleSlug,
        ]);

        $scene = Scene::create([
            'training_module_id' => $module->id,
            'slug' => $moduleSlug,
            'title' => 'Hygiene Scene',
            'description' => 'Scene description.',
            'learning_objectives' => ['Understand hygiene'],
            'order_index' => 1,
            'priority' => 'P0',
            'difficulty' => 'beginner',
            'estimated_minutes' => 15,
            'environment_asset' => null,
            'is_active' => true,
            'required_previous_scene_id' => null,
        ]);

        $assessment = Assessment::create([
            'module_id' => $module->id,
            'type' => AssessmentType::POSTTEST->value,
            'title' => 'Posttest Hygiene',
            'description' => 'Posttest description.',
            'status' => AssessmentStatus::ACTIVE->value,
            'number_of_questions_to_take' => 1,
            'randomize_questions' => true,
            'randomize_options' => true,
            'passing_score' => 70,
            'time_limit_minutes' => 10,
        ]);

        $question = QuestionBankItem::create([
            'module_id' => $module->id,
            'question_text' => 'What is correct hygiene?',
            'usage_scope' => QuestionUsageScope::POSTTEST->value,
            'difficulty' => 'medium',
            'explanation' => 'Follow SOP.',
            'is_active' => true,
        ]);

        $correctOption = QuestionBankOption::create([
            'question_bank_item_id' => $question->id,
            'option_key' => 'A',
            'option_text' => 'Follow SOP',
            'is_correct' => true,
            'sort_order' => 1,
        ]);

        QuestionBankOption::create([
            'question_bank_item_id' => $question->id,
            'option_key' => 'B',
            'option_text' => 'Skip documentation',
            'is_correct' => false,
            'sort_order' => 2,
        ]);

        $attempt = AssessmentAttempt::create([
            'user_id' => $student->id,
            'assessment_id' => $assessment->id,
            'score' => $score,
            'passed' => $passed,
            'status' => $status,
            'started_at' => now()->subMinutes(12),
            'completed_at' => $status === 'in_progress' ? null : now()->subMinutes(2),
        ]);

        UserAnswer::create([
            'assessment_attempt_id' => $attempt->id,
            'question_id' => $question->id,
            'option_id' => $correctOption->id,
        ]);

        return [$attempt->load('assessment'), $assessment, $module, $question, $scene];
    }
}
