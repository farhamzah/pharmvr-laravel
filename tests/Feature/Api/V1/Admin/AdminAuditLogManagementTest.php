<?php

namespace Tests\Feature\Api\V1\Admin;

use App\Enums\AssessmentStatus;
use App\Enums\AssessmentType;
use App\Enums\QuestionUsageScope;
use App\Models\Assessment;
use App\Models\AuditLog;
use App\Models\QuestionBankItem;
use App\Models\QuestionBankOption;
use App\Models\Scene;
use App\Models\TrainingModule;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminAuditLogManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_unauthenticated_and_non_operator_users_cannot_view_audit_logs(): void
    {
        $log = AuditLog::create([
            'action' => 'module.updated',
            'model_type' => 'training_module',
            'model_id' => 1,
            'old_values' => ['title' => 'Old'],
            'new_values' => ['title' => 'New'],
        ]);

        $this->getJson('/api/v1/admin/audit-logs')->assertUnauthorized();

        foreach ([User::ROLE_STUDENT, User::ROLE_INSTRUCTOR] as $role) {
            $user = $this->makeUser($role);

            $this->actingAs($user)
                ->getJson('/api/v1/admin/audit-logs')
                ->assertForbidden()
                ->assertJsonPath('success', false);

            $this->actingAs($user)
                ->getJson("/api/v1/admin/audit-logs/{$log->id}")
                ->assertForbidden()
                ->assertJsonPath('success', false);
        }
    }

    public function test_admin_and_super_admin_can_view_audit_log_list_and_detail(): void
    {
        $actor = $this->makeUser(User::ROLE_ADMIN, 'Audit Actor');
        $superAdmin = $this->makeUser(User::ROLE_SUPER_ADMIN, 'Security Lead');

        $log = AuditLog::create([
            'user_id' => $actor->id,
            'action' => 'module.updated',
            'model_type' => 'training_module',
            'model_id' => 10,
            'old_values' => ['title' => 'Old Module'],
            'new_values' => ['title' => 'New Module'],
            'ip_address' => '127.0.0.1',
            'user_agent' => 'Feature Test',
        ]);

        $this->actingAs($actor)
            ->getJson('/api/v1/admin/audit-logs?action=module.updated&target_type=training_module&per_page=5')
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', 'Audit logs retrieved.')
            ->assertJsonPath('meta.total', 1)
            ->assertJsonPath('data.0.id', $log->id)
            ->assertJsonPath('data.0.actor.email', $actor->email)
            ->assertJsonPath('data.0.target_type', 'training_module')
            ->assertJsonPath('data.0.target_label', 'New Module');

        $this->actingAs($superAdmin)
            ->getJson("/api/v1/admin/audit-logs/{$log->id}")
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', 'Audit log retrieved.')
            ->assertJsonPath('data.before_changes.title', 'Old Module')
            ->assertJsonPath('data.after_changes.title', 'New Module');
    }

    public function test_admin_actions_create_canonical_audit_logs_without_breaking_original_behavior(): void
    {
        $admin = $this->makeUser(User::ROLE_ADMIN);
        $target = $this->makeUser(User::ROLE_STUDENT);
        [$module, $assessment, $question, $scene] = $this->makeAssessmentSet();

        $this->actingAs($admin)
            ->patchJson("/api/v1/admin/users/{$target->id}/role", [
                'role' => User::ROLE_INSTRUCTOR,
            ])
            ->assertOk()
            ->assertJsonPath('data.role', User::ROLE_INSTRUCTOR);

        $this->actingAs($admin)
            ->patchJson("/api/v1/admin/modules/{$module->id}", [
                'title' => 'Audited Module',
                'status' => 'inactive',
            ])
            ->assertOk()
            ->assertJsonPath('data.title', 'Audited Module');

        $this->actingAs($admin)
            ->patchJson("/api/v1/admin/scenes/{$scene->id}", [
                'title' => 'Audited Scene',
                'status' => 'inactive',
            ])
            ->assertOk()
            ->assertJsonPath('data.title', 'Audited Scene');

        $this->actingAs($admin)
            ->patchJson("/api/v1/admin/assessments/{$assessment->id}", [
                'passing_score' => 80,
            ])
            ->assertOk()
            ->assertJsonPath('data.passing_score', 80);

        $createResponse = $this->actingAs($admin)
            ->postJson('/api/v1/admin/questions', $this->questionPayload($assessment))
            ->assertCreated()
            ->assertJsonPath('message', 'Question created.');

        $createdQuestionId = $createResponse->json('data.id');

        $this->actingAs($admin)
            ->patchJson("/api/v1/admin/questions/{$question->id}", [
                'question_text' => 'Updated audited question?',
                'correct_answer' => 'B',
                'options' => [
                    ['key' => 'A', 'text' => 'Wrong'],
                    ['key' => 'B', 'text' => 'Correct'],
                ],
            ])
            ->assertOk()
            ->assertJsonPath('data.question_text', 'Updated audited question?');

        foreach ([
            'user.role.updated' => 'user',
            'module.updated' => 'training_module',
            'scene.updated' => 'scene',
            'assessment.updated' => 'assessment',
            'question.created' => 'question_bank_item',
            'question.updated' => 'question_bank_item',
        ] as $action => $targetType) {
            $this->assertDatabaseHas('audit_logs', [
                'user_id' => $admin->id,
                'action' => $action,
                'model_type' => $targetType,
            ]);
        }

        $this->assertDatabaseHas('users', [
            'id' => $target->id,
            'role' => User::ROLE_INSTRUCTOR,
        ]);
        $this->assertDatabaseHas('training_modules', [
            'id' => $module->id,
            'title' => 'Audited Module',
            'is_active' => false,
        ]);
        $this->assertDatabaseHas('scenes', [
            'id' => $scene->id,
            'title' => 'Audited Scene',
            'is_active' => false,
        ]);
        $this->assertDatabaseHas('assessments', [
            'id' => $assessment->id,
            'passing_score' => 80,
        ]);
        $this->assertDatabaseHas('question_bank_items', [
            'id' => $createdQuestionId,
            'module_id' => $assessment->module_id,
        ]);
    }

    public function test_audit_log_api_sanitizes_sensitive_fields(): void
    {
        $admin = $this->makeUser(User::ROLE_ADMIN);
        $log = AuditLog::create([
            'user_id' => $admin->id,
            'action' => 'question.updated',
            'model_type' => 'question_bank_item',
            'model_id' => 99,
            'old_values' => [
                'question_text' => 'Question?',
                'password' => 'secret-password',
                'remember_token' => 'remember-secret',
                'correct_answer' => 'A',
                'options' => [
                    ['key' => 'A', 'text' => 'Correct', 'is_correct' => true],
                ],
            ],
            'new_values' => [
                'question_text' => 'Updated question?',
                'access_token' => 'access-secret',
                'api_token' => 'api-secret',
                'is_correct' => true,
            ],
        ]);

        $response = $this->actingAs($admin)
            ->getJson("/api/v1/admin/audit-logs/{$log->id}")
            ->assertOk()
            ->assertJsonPath('data.before_changes.question_text', 'Question?')
            ->assertJsonPath('data.after_changes.question_text', 'Updated question?');

        $content = $response->getContent();

        foreach ([
            'secret-password',
            'remember-secret',
            'access-secret',
            'api-secret',
            'correct_answer',
            'is_correct',
            'password',
            'remember_token',
        ] as $sensitiveFragment) {
            $this->assertStringNotContainsString($sensitiveFragment, $content);
        }
    }

    public function test_audit_logs_are_read_only_from_api(): void
    {
        $admin = $this->makeUser(User::ROLE_ADMIN);
        $log = AuditLog::create([
            'user_id' => $admin->id,
            'action' => 'module.updated',
            'model_type' => 'training_module',
            'model_id' => 1,
        ]);
        $countBefore = AuditLog::count();

        $this->actingAs($admin)
            ->getJson('/api/v1/admin/audit-logs')
            ->assertOk();

        $this->actingAs($admin)
            ->getJson("/api/v1/admin/audit-logs/{$log->id}")
            ->assertOk();

        $this->actingAs($admin)
            ->deleteJson("/api/v1/admin/audit-logs/{$log->id}")
            ->assertStatus(405);

        $this->assertSame($countBefore, AuditLog::count());
    }

    private function makeUser(string $role, string $name = 'Test User'): User
    {
        return User::factory()->create([
            'name' => $name,
            'role' => $role,
            'status' => User::STATUS_ACTIVE,
        ]);
    }

    private function makeAssessmentSet(): array
    {
        $module = TrainingModule::factory()->create([
            'title' => 'Hygiene',
            'slug' => 'hygiene',
        ]);

        $scene = Scene::create([
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

        $question = QuestionBankItem::create([
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
        ] as $index => [$key, $text, $isCorrect]) {
            QuestionBankOption::create([
                'question_bank_item_id' => $question->id,
                'option_key' => $key,
                'option_text' => $text,
                'is_correct' => $isCorrect,
                'sort_order' => $index + 1,
            ]);
        }

        return [$module, $assessment, $question, $scene];
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
            ],
            'correct_answer' => 'B',
            'explanation' => 'Validated SOP is required.',
            'difficulty' => 'medium',
            'status' => 'active',
        ];
    }
}
