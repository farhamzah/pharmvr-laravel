<?php

namespace Tests\Feature\Api\V1\Admin;

use App\Enums\AssessmentStatus;
use App\Enums\AssessmentType;
use App\Models\Assessment;
use App\Models\AssessmentAttempt;
use App\Models\Certificate;
use App\Models\TrainingModule;
use App\Models\User;
use App\Models\UserTrainingProgress;
use App\Models\VrSession;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminDashboardSummaryTest extends TestCase
{
    use RefreshDatabase;

    public function test_unauthenticated_user_cannot_access_dashboard_summary(): void
    {
        $this->getJson('/api/v1/admin/dashboard/summary')
            ->assertUnauthorized();
    }

    public function test_student_cannot_access_dashboard_summary(): void
    {
        $student = User::factory()->create([
            'role' => User::ROLE_STUDENT,
            'status' => User::STATUS_ACTIVE,
        ]);

        $this->actingAs($student)
            ->getJson('/api/v1/admin/dashboard/summary')
            ->assertForbidden()
            ->assertJsonPath('success', false);
    }

    public function test_admin_can_access_dashboard_summary(): void
    {
        $admin = User::factory()->create([
            'role' => User::ROLE_ADMIN,
            'status' => User::STATUS_ACTIVE,
        ]);

        $student = User::factory()->create([
            'role' => User::ROLE_STUDENT,
            'status' => User::STATUS_ACTIVE,
        ]);

        $module = TrainingModule::factory()->create();
        $pretest = $this->makeAssessment($module, AssessmentType::PRETEST->value, 'Pretest');
        $posttest = $this->makeAssessment($module, AssessmentType::POSTTEST->value, 'Posttest');

        AssessmentAttempt::create([
            'user_id' => $student->id,
            'assessment_id' => $pretest->id,
            'score' => 70,
            'passed' => true,
            'status' => 'completed',
            'started_at' => now()->subMinutes(20),
            'completed_at' => now()->subMinutes(18),
        ]);

        AssessmentAttempt::create([
            'user_id' => $student->id,
            'assessment_id' => $posttest->id,
            'score' => 90,
            'passed' => true,
            'status' => 'completed',
            'started_at' => now()->subMinutes(10),
            'completed_at' => now()->subMinutes(8),
        ]);

        UserTrainingProgress::create([
            'user_id' => $student->id,
            'training_module_id' => $module->id,
            'completion_percentage' => 80,
            'status' => 'in_progress',
            'pre_test_status' => 'passed',
            'vr_status' => 'completed',
            'post_test_status' => 'passed',
            'last_active_step' => 'completed',
            'last_accessed_at' => now(),
        ]);

        VrSession::factory()->create([
            'user_id' => $student->id,
            'training_module_id' => $module->id,
            'session_status' => 'playing',
            'progress_percentage' => 55,
            'last_activity_at' => now(),
        ]);

        Certificate::create([
            'user_id' => $student->id,
            'certificate_type' => 'production_path_completed',
            'certificate_id' => 'PHARMVR-TEST-0001',
            'title' => 'Production Path Certificate',
            'status' => 'issued',
            'issued_at' => now(),
        ]);

        $this->actingAs($admin)
            ->getJson('/api/v1/admin/dashboard/summary')
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', 'Admin dashboard summary retrieved.')
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'metrics' => [
                        'active_users',
                        'active_vr_sessions',
                        'completion_rate',
                        'average_pretest_score',
                        'average_posttest_score',
                        'certificate_eligible',
                    ],
                    'latest_vr_sessions',
                    'latest_assessment_attempts',
                    'system_health' => [
                        'api',
                        'database',
                        'queue',
                    ],
                ],
                'meta',
                'errors',
            ])
            ->assertJsonPath('data.metrics.average_pretest_score', 70)
            ->assertJsonPath('data.metrics.average_posttest_score', 90)
            ->assertJsonPath('data.metrics.completion_rate', 80)
            ->assertJsonPath('data.metrics.certificate_eligible', 1)
            ->assertJsonPath('data.system_health.api', 'ok')
            ->assertJsonPath('data.system_health.database', 'ok');
    }

    public function test_instructor_can_access_dashboard_summary(): void
    {
        $instructor = User::factory()->create([
            'role' => User::ROLE_INSTRUCTOR,
            'status' => User::STATUS_ACTIVE,
        ]);

        $this->actingAs($instructor)
            ->getJson('/api/v1/admin/dashboard/summary')
            ->assertOk()
            ->assertJsonPath('success', true);
    }

    private function makeAssessment(TrainingModule $module, string $type, string $title): Assessment
    {
        return Assessment::create([
            'module_id' => $module->id,
            'type' => $type,
            'title' => $title,
            'status' => AssessmentStatus::ACTIVE->value,
            'number_of_questions_to_take' => 5,
            'passing_score' => 70,
            'time_limit_minutes' => 10,
        ]);
    }
}
