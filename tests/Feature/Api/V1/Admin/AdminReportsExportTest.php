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

class AdminReportsExportTest extends TestCase
{
    use RefreshDatabase;

    public function test_unauthenticated_user_cannot_access_learning_outcome_report(): void
    {
        $this->getJson('/api/v1/admin/reports/learning-outcomes')->assertUnauthorized();
    }

    public function test_student_cannot_access_reports(): void
    {
        $student = $this->makeUser(User::ROLE_STUDENT);

        $this->actingAs($student)
            ->getJson('/api/v1/admin/reports/student-performance')
            ->assertForbidden();

        $this->actingAs($student)
            ->get('/api/v1/admin/reports/student-performance/export.csv')
            ->assertForbidden();
    }

    public function test_instructor_can_access_reports(): void
    {
        $instructor = $this->makeUser(User::ROLE_INSTRUCTOR);
        $this->makeAttemptSet();

        $this->actingAs($instructor)
            ->getJson('/api/v1/admin/reports/learning-outcomes')
            ->assertOk()
            ->assertJsonPath('success', true);

        $this->actingAs($instructor)
            ->getJson('/api/v1/admin/reports/student-performance')
            ->assertOk()
            ->assertJsonPath('success', true);
    }

    public function test_admin_can_access_learning_outcome_report(): void
    {
        $admin = $this->makeUser(User::ROLE_ADMIN);
        $this->makeAttemptSet(score: 80, passed: true, status: 'completed', moduleSlug: 'hygiene');
        $this->makeAttemptSet(score: 40, passed: false, status: 'failed', moduleSlug: 'gowning');

        $this->actingAs($admin)
            ->getJson('/api/v1/admin/reports/learning-outcomes?type=posttest')
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', 'Learning outcomes report retrieved.')
            ->assertJsonPath('data.summary.total_attempts', 2)
            ->assertJsonPath('data.summary.completed_attempts', 2)
            ->assertJsonPath('data.summary.passed_attempts', 1)
            ->assertJsonPath('data.summary.average_score', 60)
            ->assertJsonPath('data.summary.pass_rate', 50)
            ->assertJsonStructure([
                'data' => [
                    'summary',
                    'by_module' => [
                        [
                            'module_id',
                            'module_title',
                            'scene_slug',
                            'attempts',
                            'average_score',
                            'pass_rate',
                        ],
                    ],
                    'by_assessment_type' => [
                        [
                            'type',
                            'attempts',
                            'average_score',
                            'pass_rate',
                        ],
                    ],
                ],
            ]);
    }

    public function test_admin_can_access_student_performance_report(): void
    {
        $admin = $this->makeUser(User::ROLE_ADMIN);
        [$attempt, , , , $scene] = $this->makeAttemptSet(score: 88, passed: true);

        $this->actingAs($admin)
            ->getJson("/api/v1/admin/reports/student-performance?user_id={$attempt->user_id}&module_id={$attempt->assessment->module_id}&scene_id={$scene->id}")
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', 'Student performance report retrieved.')
            ->assertJsonPath('meta.total', 1)
            ->assertJsonPath('data.0.attempt_id', $attempt->id)
            ->assertJsonPath('data.0.user_id', $attempt->user_id)
            ->assertJsonPath('data.0.assessment_type', AssessmentType::POSTTEST->value)
            ->assertJsonPath('data.0.score', 88)
            ->assertJsonPath('data.0.passed', true);
    }

    public function test_admin_can_export_student_performance_csv(): void
    {
        $admin = $this->makeUser(User::ROLE_ADMIN);
        [$attempt] = $this->makeAttemptSet(score: 77, passed: true);

        $response = $this->actingAs($admin)
            ->get("/api/v1/admin/reports/student-performance/export.csv?user_id={$attempt->user_id}");

        $response->assertOk();
        $this->assertStringContainsString('text/csv', (string) $response->headers->get('content-type'));

        $csv = $response->streamedContent();

        $this->assertStringContainsString('user_id,user_name,user_email,module_title,scene_slug,assessment_title,assessment_type,attempt_id,score,passing_score,passed,status,started_at,completed_at', $csv);
        $this->assertStringContainsString((string) $attempt->id, $csv);
        $this->assertStringContainsString('true', $csv);
    }

    public function test_filters_return_success_and_do_not_crash(): void
    {
        $admin = $this->makeUser(User::ROLE_ADMIN);
        [$attempt, , , , $scene] = $this->makeAttemptSet();

        $query = http_build_query([
            'search' => 'Hygiene',
            'user_id' => $attempt->user_id,
            'module_id' => $attempt->assessment->module_id,
            'scene_id' => $scene->id,
            'assessment_id' => $attempt->assessment_id,
            'type' => AssessmentType::POSTTEST->value,
            'status' => 'completed',
            'passed' => '1',
            'date_from' => now()->subDay()->toDateString(),
            'date_to' => now()->toDateString(),
            'page' => 1,
            'per_page' => 10,
        ]);

        $this->actingAs($admin)
            ->getJson("/api/v1/admin/reports/student-performance?{$query}")
            ->assertOk()
            ->assertJsonPath('meta.total', 1);

        $this->actingAs($admin)
            ->getJson("/api/v1/admin/reports/learning-outcomes?{$query}")
            ->assertOk()
            ->assertJsonPath('data.summary.total_attempts', 1);
    }

    public function test_report_endpoints_do_not_mutate_attempt_records(): void
    {
        $admin = $this->makeUser(User::ROLE_ADMIN);
        [$attempt] = $this->makeAttemptSet();
        $before = $attempt->fresh()->only(['score', 'passed', 'status', 'started_at', 'completed_at', 'updated_at']);

        $this->actingAs($admin)->getJson('/api/v1/admin/reports/learning-outcomes')->assertOk();
        $this->actingAs($admin)->getJson('/api/v1/admin/reports/student-performance')->assertOk();
        $this->actingAs($admin)->get('/api/v1/admin/reports/student-performance/export.csv')->assertOk();

        $after = $attempt->fresh()->only(['score', 'passed', 'status', 'started_at', 'completed_at', 'updated_at']);

        $this->assertSame($before['score'], $after['score']);
        $this->assertSame($before['passed'], $after['passed']);
        $this->assertSame($before['status'], $after['status']);
        $this->assertEquals($before['started_at'], $after['started_at']);
        $this->assertEquals($before['completed_at'], $after['completed_at']);
        $this->assertEquals($before['updated_at'], $after['updated_at']);
    }

    public function test_sensitive_fields_are_not_exposed_in_json_or_csv_reports(): void
    {
        $admin = $this->makeUser(User::ROLE_ADMIN);
        $this->makeAttemptSet();

        $jsonResponse = $this->actingAs($admin)
            ->getJson('/api/v1/admin/reports/student-performance')
            ->assertOk();
        $csvResponse = $this->actingAs($admin)
            ->get('/api/v1/admin/reports/student-performance/export.csv')
            ->assertOk();

        $json = json_encode($jsonResponse->json());
        $csv = $csvResponse->streamedContent();

        foreach (['password', 'token', 'correct_answer', 'is_correct'] as $sensitiveField) {
            $this->assertStringNotContainsString($sensitiveField, $json);
            $this->assertStringNotContainsString($sensitiveField, $csv);
        }
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
    ): array {
        $student = $this->makeUser(User::ROLE_STUDENT);
        $module = TrainingModule::factory()->create([
            'title' => ucfirst(str_replace('_', ' ', $moduleSlug)),
            'slug' => $moduleSlug,
        ]);

        $scene = Scene::create([
            'training_module_id' => $module->id,
            'slug' => $moduleSlug,
            'title' => 'Scene ' . $moduleSlug,
            'description' => 'Scene description.',
            'learning_objectives' => ['Understand workflow'],
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
            'title' => 'Posttest ' . $module->title,
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
            'question_text' => 'What is the right action?',
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
