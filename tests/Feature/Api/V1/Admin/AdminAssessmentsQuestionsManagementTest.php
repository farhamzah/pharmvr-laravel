<?php

namespace Tests\Feature\Api\V1\Admin;

use App\Enums\AssessmentStatus;
use App\Enums\AssessmentType;
use App\Enums\QuestionUsageScope;
use App\Models\Assessment;
use App\Models\QuestionBankItem;
use App\Models\QuestionBankOption;
use App\Models\Scene;
use App\Models\TrainingModule;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminAssessmentsQuestionsManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_unauthenticated_user_cannot_list_assessments_or_questions(): void
    {
        $this->getJson('/api/v1/admin/assessments')->assertUnauthorized();
        $this->getJson('/api/v1/admin/questions')->assertUnauthorized();
    }

    public function test_student_cannot_list_assessments_or_questions(): void
    {
        $student = $this->makeUser(User::ROLE_STUDENT);

        $this->actingAs($student)->getJson('/api/v1/admin/assessments')->assertForbidden();
        $this->actingAs($student)->getJson('/api/v1/admin/questions')->assertForbidden();
    }

    public function test_instructor_can_list_and_view_assessments_and_questions(): void
    {
        $instructor = $this->makeUser(User::ROLE_INSTRUCTOR);
        [$module, $assessment, $question] = $this->makeAssessmentSet();

        $this->actingAs($instructor)
            ->getJson("/api/v1/admin/assessments?module_id={$module->id}&type=pretest&status=active")
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', 'Assessments retrieved.')
            ->assertJsonPath('meta.total', 1)
            ->assertJsonFragment([
                'id' => $assessment->id,
                'type' => AssessmentType::PRETEST->value,
                'status' => AssessmentStatus::ACTIVE->value,
            ]);

        $this->actingAs($instructor)
            ->getJson("/api/v1/admin/assessments/{$assessment->id}")
            ->assertOk()
            ->assertJsonPath('data.id', $assessment->id)
            ->assertJsonPath('data.questions.0.id', $question->id);

        $this->actingAs($instructor)
            ->getJson("/api/v1/admin/questions?assessment_id={$assessment->id}")
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', 'Questions retrieved.')
            ->assertJsonPath('meta.total', 1)
            ->assertJsonPath('data.0.correct_answer', 'A');

        $this->actingAs($instructor)
            ->getJson("/api/v1/admin/questions/{$question->id}")
            ->assertOk()
            ->assertJsonPath('data.id', $question->id)
            ->assertJsonPath('data.assessment_id', $assessment->id);
    }

    public function test_instructor_cannot_create_or_update_questions(): void
    {
        $instructor = $this->makeUser(User::ROLE_INSTRUCTOR);
        [, $assessment, $question] = $this->makeAssessmentSet();

        $this->actingAs($instructor)
            ->postJson('/api/v1/admin/questions', $this->questionPayload($assessment))
            ->assertForbidden()
            ->assertJsonPath('success', false);

        $this->actingAs($instructor)
            ->patchJson("/api/v1/admin/questions/{$question->id}", ['question_text' => 'Updated'])
            ->assertForbidden()
            ->assertJsonPath('success', false);
    }

    public function test_admin_can_list_and_view_assessments_and_questions(): void
    {
        $admin = $this->makeUser(User::ROLE_ADMIN);
        [, $assessment, $question] = $this->makeAssessmentSet();

        $this->actingAs($admin)
            ->getJson('/api/v1/admin/assessments?per_page=5')
            ->assertOk()
            ->assertJsonPath('data.0.id', $assessment->id);

        $this->actingAs($admin)
            ->getJson('/api/v1/admin/questions?per_page=5')
            ->assertOk()
            ->assertJsonPath('data.0.id', $question->id);
    }

    public function test_admin_can_update_assessment_passing_score_and_status(): void
    {
        $admin = $this->makeUser(User::ROLE_ADMIN);
        [, $assessment] = $this->makeAssessmentSet();

        $this->actingAs($admin)
            ->patchJson("/api/v1/admin/assessments/{$assessment->id}", [
                'passing_score' => 75,
                'status' => AssessmentStatus::INACTIVE->value,
            ])
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', 'Assessment updated.')
            ->assertJsonPath('data.passing_score', 75)
            ->assertJsonPath('data.status', AssessmentStatus::INACTIVE->value);

        $this->assertDatabaseHas('assessments', [
            'id' => $assessment->id,
            'passing_score' => 75,
            'status' => AssessmentStatus::INACTIVE->value,
        ]);
    }

    public function test_admin_can_create_valid_multiple_choice_question(): void
    {
        $admin = $this->makeUser(User::ROLE_ADMIN);
        [, $assessment] = $this->makeAssessmentSet(question: false);

        $this->actingAs($admin)
            ->postJson('/api/v1/admin/questions', $this->questionPayload($assessment))
            ->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', 'Question created.')
            ->assertJsonPath('data.correct_answer', 'B')
            ->assertJsonPath('data.assessment_id', $assessment->id);

        $this->assertDatabaseHas('question_bank_items', [
            'module_id' => $assessment->module_id,
            'usage_scope' => AssessmentType::PRETEST->value,
            'is_active' => true,
        ]);

        $this->assertDatabaseHas('question_bank_options', [
            'option_key' => 'B',
            'is_correct' => true,
        ]);
    }

    public function test_admin_can_update_question(): void
    {
        $admin = $this->makeUser(User::ROLE_ADMIN);
        [, , $question] = $this->makeAssessmentSet();

        $this->actingAs($admin)
            ->patchJson("/api/v1/admin/questions/{$question->id}", [
                'question_text' => 'Updated question text?',
                'status' => 'inactive',
                'difficulty' => 'hard',
                'options' => [
                    ['key' => 'A', 'text' => 'Wrong'],
                    ['key' => 'B', 'text' => 'Correct'],
                    ['key' => 'C', 'text' => 'Distractor'],
                    ['key' => 'D', 'text' => 'Distractor'],
                ],
                'correct_answer' => 'B',
            ])
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', 'Question updated.')
            ->assertJsonPath('data.question_text', 'Updated question text?')
            ->assertJsonPath('data.status', 'inactive')
            ->assertJsonPath('data.correct_answer', 'B');

        $this->assertDatabaseHas('question_bank_items', [
            'id' => $question->id,
            'question_text' => 'Updated question text?',
            'is_active' => false,
        ]);
    }

    public function test_invalid_assessment_type_is_rejected(): void
    {
        $admin = $this->makeUser(User::ROLE_ADMIN);
        [, $assessment] = $this->makeAssessmentSet();

        $this->actingAs($admin)
            ->patchJson("/api/v1/admin/assessments/{$assessment->id}", ['type' => 'quiz'])
            ->assertUnprocessable()
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', 'Validation failed.');
    }

    public function test_invalid_question_payload_is_rejected(): void
    {
        $admin = $this->makeUser(User::ROLE_ADMIN);
        [, $assessment] = $this->makeAssessmentSet(question: false);

        $this->actingAs($admin)
            ->postJson('/api/v1/admin/questions', [
                'assessment_id' => $assessment->id,
                'question_text' => 'Broken question?',
                'type' => 'multiple_choice',
                'options' => [
                    ['key' => 'A', 'text' => 'Only option'],
                    ['key' => 'A', 'text' => 'Duplicate key'],
                ],
                'correct_answer' => 'C',
            ])
            ->assertUnprocessable()
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', 'Validation failed.');
    }

    public function test_student_cannot_create_or_update_question(): void
    {
        $student = $this->makeUser(User::ROLE_STUDENT);
        [, $assessment, $question] = $this->makeAssessmentSet();

        $this->actingAs($student)
            ->postJson('/api/v1/admin/questions', $this->questionPayload($assessment))
            ->assertForbidden();

        $this->actingAs($student)
            ->patchJson("/api/v1/admin/questions/{$question->id}", ['question_text' => 'Nope'])
            ->assertForbidden();
    }

    private function makeUser(string $role): User
    {
        return User::factory()->create([
            'role' => $role,
            'status' => User::STATUS_ACTIVE,
        ]);
    }

    private function makeAssessmentSet(bool $question = true): array
    {
        $module = TrainingModule::factory()->create([
            'title' => 'Hygiene',
            'slug' => 'hygiene',
        ]);

        Scene::create([
            'training_module_id' => $module->id,
            'slug' => 'hygiene',
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
            'type' => AssessmentType::PRETEST->value,
            'title' => 'Pretest Hygiene',
            'description' => 'Pretest description.',
            'status' => AssessmentStatus::ACTIVE->value,
            'number_of_questions_to_take' => 1,
            'randomize_questions' => true,
            'randomize_options' => true,
            'passing_score' => 70,
            'time_limit_minutes' => 10,
        ]);

        $questionItem = null;

        if ($question) {
            $questionItem = QuestionBankItem::create([
                'module_id' => $module->id,
                'question_text' => 'What is correct hygiene?',
                'usage_scope' => QuestionUsageScope::PRETEST->value,
                'difficulty' => 'medium',
                'explanation' => 'Follow SOP.',
                'is_active' => true,
            ]);

            foreach ([
                ['A', 'Follow SOP', true],
                ['B', 'Skip handwash', false],
                ['C', 'Ignore gowning', false],
                ['D', 'Touch sterile product', false],
            ] as $index => [$key, $text, $isCorrect]) {
                QuestionBankOption::create([
                    'question_bank_item_id' => $questionItem->id,
                    'option_key' => $key,
                    'option_text' => $text,
                    'is_correct' => $isCorrect,
                    'sort_order' => $index + 1,
                ]);
            }
        }

        return [$module, $assessment, $questionItem];
    }

    private function questionPayload(Assessment $assessment): array
    {
        return [
            'assessment_id' => $assessment->id,
            'question_text' => 'Which practice is correct?',
            'type' => 'multiple_choice',
            'options' => [
                ['key' => 'A', 'text' => 'Unsafe practice'],
                ['key' => 'B', 'text' => 'Follow validated SOP'],
                ['key' => 'C', 'text' => 'Skip documentation'],
                ['key' => 'D', 'text' => 'Ignore deviations'],
            ],
            'correct_answer' => 'B',
            'explanation' => 'Validated SOP is required.',
            'difficulty' => 'medium',
            'status' => 'active',
        ];
    }
}
