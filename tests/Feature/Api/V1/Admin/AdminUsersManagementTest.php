<?php

namespace Tests\Feature\Api\V1\Admin;

use App\Enums\AssessmentStatus;
use App\Enums\AssessmentType;
use App\Models\Assessment;
use App\Models\AssessmentAttempt;
use App\Models\Cohort;
use App\Models\TrainingModule;
use App\Models\User;
use App\Models\UserTrainingProgress;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminUsersManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_unauthenticated_user_cannot_list_users(): void
    {
        $this->getJson('/api/v1/admin/users')
            ->assertUnauthorized();
    }

    public function test_student_cannot_list_users(): void
    {
        $student = $this->makeUser(User::ROLE_STUDENT);

        $this->actingAs($student)
            ->getJson('/api/v1/admin/users')
            ->assertForbidden()
            ->assertJsonPath('success', false);
    }

    public function test_instructor_can_list_users(): void
    {
        $instructor = $this->makeUser(User::ROLE_INSTRUCTOR, 'Instructor User');
        $student = $this->makeUser(User::ROLE_STUDENT, 'Student User');
        $this->assignStudentToInstructor($student, $instructor);

        $this->actingAs($instructor)
            ->getJson('/api/v1/admin/users?search=Student&role=student&status=active')
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', 'Users retrieved.')
            ->assertJsonPath('meta.total', 1)
            ->assertJsonFragment([
                'id' => $student->id,
                'email' => $student->email,
                'role' => User::ROLE_STUDENT,
                'status' => User::STATUS_ACTIVE,
                'last_login_at' => null,
            ]);
    }

    public function test_admin_can_list_users(): void
    {
        $admin = $this->makeUser(User::ROLE_ADMIN);
        $student = $this->makeUser(User::ROLE_STUDENT, 'Managed Student');

        $this->actingAs($admin)
            ->getJson('/api/v1/admin/users?per_page=5')
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'name',
                        'email',
                        'role',
                        'status',
                        'created_at',
                        'last_login_at',
                    ],
                ],
                'meta' => [
                    'current_page',
                    'per_page',
                    'total',
                    'last_page',
                ],
            ])
            ->assertJsonFragment([
                'id' => $student->id,
                'name' => 'Managed Student',
            ]);
    }

    public function test_admin_can_view_user_detail(): void
    {
        $admin = $this->makeUser(User::ROLE_ADMIN);
        $student = $this->makeUser(User::ROLE_STUDENT, 'Progress Student');
        $module = TrainingModule::factory()->create();
        $posttest = Assessment::create([
            'module_id' => $module->id,
            'type' => AssessmentType::POSTTEST->value,
            'title' => 'Posttest',
            'status' => AssessmentStatus::ACTIVE->value,
            'number_of_questions_to_take' => 5,
            'passing_score' => 70,
            'time_limit_minutes' => 10,
        ]);

        UserTrainingProgress::create([
            'user_id' => $student->id,
            'training_module_id' => $module->id,
            'completion_percentage' => 100,
            'status' => 'completed',
            'pre_test_status' => 'passed',
            'vr_status' => 'completed',
            'post_test_status' => 'passed',
            'last_active_step' => 'completed',
            'last_accessed_at' => now(),
        ]);

        AssessmentAttempt::create([
            'user_id' => $student->id,
            'assessment_id' => $posttest->id,
            'score' => 88,
            'passed' => true,
            'status' => 'completed',
            'started_at' => now()->subMinutes(10),
            'completed_at' => now()->subMinutes(8),
        ]);

        $this->actingAs($admin)
            ->getJson("/api/v1/admin/users/{$student->id}")
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', 'User retrieved.')
            ->assertJsonPath('data.email', $student->email)
            ->assertJsonPath('data.progress_summary.completed_modules', 1)
            ->assertJsonPath('data.progress_summary.completed_scenes', 1)
            ->assertJsonPath('data.progress_summary.average_posttest_score', 88);
    }

    public function test_instructor_cannot_update_user_role(): void
    {
        $instructor = $this->makeUser(User::ROLE_INSTRUCTOR);
        $student = $this->makeUser(User::ROLE_STUDENT);

        $this->actingAs($instructor)
            ->patchJson("/api/v1/admin/users/{$student->id}/role", [
                'role' => User::ROLE_ADMIN,
            ])
            ->assertForbidden()
            ->assertJsonPath('success', false);
    }

    public function test_admin_can_update_user_role_to_allowed_operator_roles(): void
    {
        $admin = $this->makeUser(User::ROLE_ADMIN);
        $target = $this->makeUser(User::ROLE_STUDENT);

        foreach ([User::ROLE_INSTRUCTOR, User::ROLE_STUDENT, User::ROLE_ADMIN] as $role) {
            $this->actingAs($admin)
                ->patchJson("/api/v1/admin/users/{$target->id}/role", [
                    'role' => $role,
                ])
                ->assertOk()
                ->assertJsonPath('success', true)
                ->assertJsonPath('message', 'User role updated.')
                ->assertJsonPath('data.role', $role);

            $this->assertDatabaseHas('users', [
                'id' => $target->id,
                'role' => $role,
            ]);
        }
    }

    public function test_invalid_role_is_rejected(): void
    {
        $admin = $this->makeUser(User::ROLE_ADMIN);
        $student = $this->makeUser(User::ROLE_STUDENT);

        $this->actingAs($admin)
            ->patchJson("/api/v1/admin/users/{$student->id}/role", [
                'role' => 'physician',
            ])
            ->assertUnprocessable()
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', 'Validation failed.');
    }

    public function test_student_cannot_update_role(): void
    {
        $student = $this->makeUser(User::ROLE_STUDENT);
        $target = $this->makeUser(User::ROLE_STUDENT);

        $this->actingAs($student)
            ->patchJson("/api/v1/admin/users/{$target->id}/role", [
                'role' => User::ROLE_INSTRUCTOR,
            ])
            ->assertForbidden()
            ->assertJsonPath('success', false);
    }

    private function makeUser(string $role, string $name = 'Test User'): User
    {
        return User::factory()->create([
            'name' => $name,
            'role' => $role,
            'status' => User::STATUS_ACTIVE,
        ]);
    }

    private function assignStudentToInstructor(User $student, User $instructor): void
    {
        $cohort = Cohort::create(['name' => 'Instructor User Test Cohort']);
        $cohort->members()->syncWithoutDetaching([
            $instructor->id => ['role_in_cohort' => Cohort::MEMBER_ROLE_INSTRUCTOR],
            $student->id => ['role_in_cohort' => Cohort::MEMBER_ROLE_STUDENT],
        ]);
    }
}
