<?php

namespace Tests\Feature\Api\V1\Admin;

use App\Enums\AssessmentStatus;
use App\Enums\AssessmentType;
use App\Models\Assessment;
use App\Models\AssessmentAttempt;
use App\Models\Certificate;
use App\Models\Cohort;
use App\Models\Scene;
use App\Models\TrainingModule;
use App\Models\User;
use App\Models\UserTrainingProgress;
use App\Models\VrSession;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminCohortInstructorScopingTest extends TestCase
{
    use RefreshDatabase;

    public function test_unauthenticated_user_cannot_access_cohort_endpoints(): void
    {
        $cohort = Cohort::create(['name' => 'QA Cohort']);

        $this->getJson('/api/v1/admin/cohorts')->assertUnauthorized();
        $this->postJson('/api/v1/admin/cohorts', ['name' => 'New Cohort'])->assertUnauthorized();
        $this->getJson("/api/v1/admin/cohorts/{$cohort->id}")->assertUnauthorized();
    }

    public function test_student_cannot_access_cohort_endpoints(): void
    {
        $student = $this->makeUser(User::ROLE_STUDENT);

        $this->actingAs($student)->getJson('/api/v1/admin/cohorts')->assertForbidden();
        $this->actingAs($student)->postJson('/api/v1/admin/cohorts', ['name' => 'New Cohort'])->assertForbidden();
    }

    public function test_admin_can_create_list_and_update_cohorts(): void
    {
        $admin = $this->makeUser(User::ROLE_ADMIN);

        $response = $this->actingAs($admin)
            ->postJson('/api/v1/admin/cohorts', [
                'name' => 'Batch A',
                'code' => 'BATCH-A',
                'description' => 'Morning class',
            ])
            ->assertCreated()
            ->assertJsonPath('message', 'Cohort created.')
            ->assertJsonPath('data.status', Cohort::STATUS_ACTIVE);

        $cohortId = $response->json('data.id');

        $this->actingAs($admin)
            ->getJson('/api/v1/admin/cohorts?search=Batch')
            ->assertOk()
            ->assertJsonPath('meta.total', 1)
            ->assertJsonPath('data.0.code', 'BATCH-A');

        $this->actingAs($admin)
            ->patchJson("/api/v1/admin/cohorts/{$cohortId}", [
                'name' => 'Batch A Updated',
                'status' => Cohort::STATUS_INACTIVE,
            ])
            ->assertOk()
            ->assertJsonPath('data.name', 'Batch A Updated')
            ->assertJsonPath('data.status', Cohort::STATUS_INACTIVE);
    }

    public function test_admin_can_add_student_and_instructor_members(): void
    {
        $admin = $this->makeUser(User::ROLE_ADMIN);
        $instructor = $this->makeUser(User::ROLE_INSTRUCTOR);
        $student = $this->makeUser(User::ROLE_STUDENT);
        $cohort = Cohort::create(['name' => 'Members Cohort']);

        $this->actingAs($admin)
            ->postJson("/api/v1/admin/cohorts/{$cohort->id}/members", [
                'user_id' => $instructor->id,
                'role_in_cohort' => Cohort::MEMBER_ROLE_INSTRUCTOR,
            ])
            ->assertOk()
            ->assertJsonPath('success', true);

        $this->actingAs($admin)
            ->postJson("/api/v1/admin/cohorts/{$cohort->id}/members", [
                'user_id' => $student->id,
                'role_in_cohort' => Cohort::MEMBER_ROLE_STUDENT,
            ])
            ->assertOk()
            ->assertJsonFragment(['email' => $student->email]);

        $this->assertDatabaseHas('cohort_user', [
            'cohort_id' => $cohort->id,
            'user_id' => $student->id,
            'role_in_cohort' => Cohort::MEMBER_ROLE_STUDENT,
        ]);
    }

    public function test_instructor_can_list_only_assigned_cohorts(): void
    {
        $instructor = $this->makeUser(User::ROLE_INSTRUCTOR);
        $assigned = Cohort::create(['name' => 'Assigned Cohort']);
        Cohort::create(['name' => 'Other Cohort']);
        $this->attachMember($assigned, $instructor, Cohort::MEMBER_ROLE_INSTRUCTOR);

        $this->actingAs($instructor)
            ->getJson('/api/v1/admin/cohorts')
            ->assertOk()
            ->assertJsonPath('meta.total', 1)
            ->assertJsonPath('data.0.id', $assigned->id);
    }

    public function test_instructor_cannot_modify_cohorts_or_memberships(): void
    {
        $instructor = $this->makeUser(User::ROLE_INSTRUCTOR);
        $student = $this->makeUser(User::ROLE_STUDENT);
        $cohort = Cohort::create(['name' => 'Read Only Cohort']);
        $this->attachMember($cohort, $instructor, Cohort::MEMBER_ROLE_INSTRUCTOR);

        $this->actingAs($instructor)
            ->patchJson("/api/v1/admin/cohorts/{$cohort->id}", ['name' => 'Changed'])
            ->assertForbidden();

        $this->actingAs($instructor)
            ->postJson("/api/v1/admin/cohorts/{$cohort->id}/members", [
                'user_id' => $student->id,
                'role_in_cohort' => Cohort::MEMBER_ROLE_STUDENT,
            ])
            ->assertForbidden();
    }

    public function test_instructor_with_cohort_only_sees_users_in_assigned_cohort(): void
    {
        [$instructor, $assignedStudent, $outsideStudent] = $this->makeScopedStudents();

        $this->actingAs($instructor)
            ->getJson('/api/v1/admin/users?role=student')
            ->assertOk()
            ->assertJsonPath('meta.total', 1)
            ->assertJsonFragment(['id' => $assignedStudent->id])
            ->assertJsonMissing(['id' => $outsideStudent->id]);
    }

    public function test_instructor_cannot_view_user_outside_assigned_cohort(): void
    {
        [$instructor, $assignedStudent, $outsideStudent] = $this->makeScopedStudents();

        $this->actingAs($instructor)
            ->getJson("/api/v1/admin/users/{$assignedStudent->id}")
            ->assertOk()
            ->assertJsonPath('data.id', $assignedStudent->id);

        $this->actingAs($instructor)
            ->getJson("/api/v1/admin/users/{$outsideStudent->id}")
            ->assertForbidden();
    }

    public function test_instructor_attempts_are_limited_to_assigned_students(): void
    {
        [$instructor, $assignedStudent, $outsideStudent] = $this->makeScopedStudents();
        $assignedAttempt = $this->makeAttemptFor($assignedStudent);
        $outsideAttempt = $this->makeAttemptFor($outsideStudent, 'outside');

        $this->actingAs($instructor)
            ->getJson('/api/v1/admin/attempts')
            ->assertOk()
            ->assertJsonPath('meta.total', 1)
            ->assertJsonPath('data.0.id', $assignedAttempt->id);

        $this->actingAs($instructor)
            ->getJson("/api/v1/admin/attempts/{$outsideAttempt->id}")
            ->assertForbidden();
    }

    public function test_instructor_reports_are_limited_to_assigned_students(): void
    {
        [$instructor, $assignedStudent, $outsideStudent] = $this->makeScopedStudents();
        $assignedAttempt = $this->makeAttemptFor($assignedStudent);
        $outsideAttempt = $this->makeAttemptFor($outsideStudent, 'outside');

        $this->actingAs($instructor)
            ->getJson('/api/v1/admin/reports/student-performance')
            ->assertOk()
            ->assertJsonPath('meta.total', 1)
            ->assertJsonPath('data.0.attempt_id', $assignedAttempt->id);

        $response = $this->actingAs($instructor)
            ->get('/api/v1/admin/reports/student-performance/export.csv')
            ->assertOk();

        $csv = $response->streamedContent();
        $this->assertStringContainsString((string) $assignedAttempt->id, $csv);
        $this->assertStringNotContainsString($outsideStudent->email, $csv);
    }

    public function test_instructor_completions_and_certificates_are_limited_to_assigned_students(): void
    {
        [$instructor, $assignedStudent, $outsideStudent] = $this->makeScopedStudents();
        $this->makeProductionPathModules();
        $this->completeModuleFor($assignedStudent, 'hygiene');
        $this->completeModuleFor($outsideStudent, 'hygiene');
        $assignedCertificate = $this->issueCertificate($assignedStudent);
        $outsideCertificate = $this->issueCertificate($outsideStudent);

        $this->actingAs($instructor)
            ->getJson('/api/v1/admin/completions')
            ->assertOk()
            ->assertJsonPath('meta.total', 1)
            ->assertJsonPath('data.0.user_id', $assignedStudent->id);

        $this->actingAs($instructor)
            ->getJson("/api/v1/admin/completions/{$outsideStudent->id}")
            ->assertForbidden();

        $this->actingAs($instructor)
            ->getJson('/api/v1/admin/certificates')
            ->assertOk()
            ->assertJsonPath('meta.total', 1)
            ->assertJsonPath('data.0.id', $assignedCertificate->id)
            ->assertJsonMissing(['number' => $outsideCertificate->certificate_id]);

        $this->actingAs($instructor)
            ->getJson('/api/v1/admin/certificates/eligibility')
            ->assertOk()
            ->assertJsonPath('meta.total', 1)
            ->assertJsonPath('data.0.user_id', $assignedStudent->id);
    }

    public function test_admin_and_super_admin_see_global_student_data(): void
    {
        $admin = $this->makeUser(User::ROLE_ADMIN);
        $superAdmin = $this->makeUser(User::ROLE_SUPER_ADMIN);
        $this->makeUser(User::ROLE_STUDENT);
        $this->makeUser(User::ROLE_STUDENT);

        $this->actingAs($admin)
            ->getJson('/api/v1/admin/users?role=student')
            ->assertOk()
            ->assertJsonPath('meta.total', 2);

        $this->actingAs($superAdmin)
            ->getJson('/api/v1/admin/users?role=student')
            ->assertOk()
            ->assertJsonPath('meta.total', 2);
    }

    public function test_instructor_with_no_cohort_sees_empty_student_specific_lists(): void
    {
        $instructor = $this->makeUser(User::ROLE_INSTRUCTOR);
        $student = $this->makeUser(User::ROLE_STUDENT);
        $this->makeAttemptFor($student, 'unscoped-attempt');
        $this->makeProductionPathModules();
        $this->completeModuleFor($student, 'hygiene');
        $this->issueCertificate($student);

        $this->actingAs($instructor)->getJson('/api/v1/admin/users?role=student')->assertJsonPath('meta.total', 0);
        $this->actingAs($instructor)->getJson('/api/v1/admin/attempts')->assertJsonPath('meta.total', 0);
        $this->actingAs($instructor)->getJson('/api/v1/admin/reports/student-performance')->assertJsonPath('meta.total', 0);
        $this->actingAs($instructor)->getJson('/api/v1/admin/completions')->assertJsonPath('meta.total', 0);
        $this->actingAs($instructor)->getJson('/api/v1/admin/certificates')->assertJsonPath('meta.total', 0);
        $this->actingAs($instructor)->getJson('/api/v1/admin/certificates/eligibility')->assertJsonPath('meta.total', 0);
    }

    public function test_student_facing_assessment_delivery_still_requires_student_auth_without_admin_scope(): void
    {
        $student = $this->makeUser(User::ROLE_STUDENT);
        $module = TrainingModule::factory()->create(['slug' => 'student-flow']);
        Assessment::create([
            'module_id' => $module->id,
            'type' => AssessmentType::PRETEST->value,
            'title' => 'Student Pretest',
            'status' => AssessmentStatus::ACTIVE->value,
            'number_of_questions_to_take' => 1,
            'passing_score' => 70,
            'time_limit_minutes' => 10,
        ]);

        $this->actingAs($student)
            ->getJson('/api/v1/admin/cohorts')
            ->assertForbidden();

        $this->actingAs($student)
            ->getJson('/api/v1/modules/student-flow/readiness')
            ->assertOk()
            ->assertJsonPath('success', true);
    }

    private function makeUser(string $role): User
    {
        return User::factory()->create([
            'role' => $role,
            'status' => User::STATUS_ACTIVE,
        ]);
    }

    private function makeScopedStudents(): array
    {
        $instructor = $this->makeUser(User::ROLE_INSTRUCTOR);
        $assignedStudent = $this->makeUser(User::ROLE_STUDENT);
        $outsideStudent = $this->makeUser(User::ROLE_STUDENT);
        $cohort = Cohort::create(['name' => 'Instructor Cohort']);
        $this->attachMember($cohort, $instructor, Cohort::MEMBER_ROLE_INSTRUCTOR);
        $this->attachMember($cohort, $assignedStudent, Cohort::MEMBER_ROLE_STUDENT);

        return [$instructor, $assignedStudent, $outsideStudent, $cohort];
    }

    private function attachMember(Cohort $cohort, User $user, string $role): void
    {
        $cohort->members()->syncWithoutDetaching([
            $user->id => ['role_in_cohort' => $role],
        ]);
    }

    private function makeAttemptFor(User $student, string $moduleSlug = 'hygiene'): AssessmentAttempt
    {
        $module = TrainingModule::factory()->create([
            'title' => ucfirst($moduleSlug),
            'slug' => $moduleSlug,
        ]);

        Scene::create([
            'training_module_id' => $module->id,
            'slug' => $moduleSlug,
            'title' => ucfirst($moduleSlug) . ' Scene',
            'description' => 'Scene description.',
            'learning_objectives' => ['Understand SOP'],
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
            'status' => AssessmentStatus::ACTIVE->value,
            'number_of_questions_to_take' => 1,
            'passing_score' => 70,
            'time_limit_minutes' => 10,
        ]);

        return AssessmentAttempt::create([
            'user_id' => $student->id,
            'assessment_id' => $assessment->id,
            'score' => 85,
            'passed' => true,
            'status' => 'completed',
            'started_at' => now()->subMinutes(12),
            'completed_at' => now()->subMinutes(2),
        ]);
    }

    private function makeProductionPathModules(): void
    {
        foreach (['hygiene', 'gowning'] as $index => $slug) {
            $module = TrainingModule::factory()->create([
                'title' => ucfirst($slug),
                'slug' => $slug,
                'is_active' => true,
            ]);

            Scene::create([
                'training_module_id' => $module->id,
                'slug' => $slug,
                'title' => ucfirst($slug) . ' Scene',
                'description' => 'Scene description.',
                'learning_objectives' => ['Complete workflow'],
                'order_index' => $index + 1,
                'priority' => 'P0',
                'difficulty' => 'beginner',
                'estimated_minutes' => 10,
                'environment_asset' => null,
                'is_active' => true,
                'required_previous_scene_id' => null,
            ]);

            foreach ([AssessmentType::PRETEST, AssessmentType::POSTTEST] as $type) {
                Assessment::create([
                    'module_id' => $module->id,
                    'type' => $type->value,
                    'title' => $type->value . ' ' . $module->title,
                    'status' => AssessmentStatus::ACTIVE->value,
                    'number_of_questions_to_take' => 1,
                    'passing_score' => 70,
                    'time_limit_minutes' => 10,
                ]);
            }
        }
    }

    private function completeModuleFor(User $student, string $moduleSlug): void
    {
        $module = TrainingModule::where('slug', $moduleSlug)->with(['assessments', 'scenes'])->firstOrFail();
        $scene = $module->scenes->first();

        foreach ([AssessmentType::PRETEST, AssessmentType::POSTTEST] as $type) {
            $assessment = $module->assessments->first(fn (Assessment $assessment) => $assessment->type->value === $type->value);

            AssessmentAttempt::create([
                'user_id' => $student->id,
                'assessment_id' => $assessment->id,
                'score' => 85,
                'passed' => true,
                'status' => 'completed',
                'started_at' => now()->subMinutes(20),
                'completed_at' => now()->subMinutes(10),
            ]);
        }

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

        VrSession::factory()->completed()->create([
            'user_id' => $student->id,
            'training_module_id' => $module->id,
            'scene_id' => $scene->id,
            'session_status' => 'completed',
            'progress_percentage' => 100,
            'completed_at' => now()->subMinutes(5),
            'last_activity_at' => now()->subMinutes(5),
        ]);
    }

    private function issueCertificate(User $student): Certificate
    {
        return Certificate::create([
            'user_id' => $student->id,
            'certificate_type' => 'production_path_completed',
            'certificate_id' => 'PHARMVR-COHORT-' . $student->id,
            'title' => 'Production Path Completed',
            'status' => 'issued',
            'issued_at' => now(),
        ]);
    }
}
