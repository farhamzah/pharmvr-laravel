<?php

namespace Tests\Feature\Api\V1\Admin;

use App\Enums\AssessmentStatus;
use App\Enums\AssessmentType;
use App\Models\Assessment;
use App\Models\AssessmentAttempt;
use App\Models\Certificate;
use App\Models\Scene;
use App\Models\TrainingModule;
use App\Models\User;
use App\Models\VrSession;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminCompletionCertificateManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_unauthenticated_user_cannot_access_completions(): void
    {
        $this->getJson('/api/v1/admin/completions')->assertUnauthorized();
        $this->getJson('/api/v1/admin/certificates')->assertUnauthorized();
        $this->getJson('/api/v1/admin/certificates/eligibility')->assertUnauthorized();
    }

    public function test_student_cannot_access_completion_or_certificate_admin_endpoints(): void
    {
        $student = $this->makeUser(User::ROLE_STUDENT);

        $this->actingAs($student)->getJson('/api/v1/admin/completions')->assertForbidden();
        $this->actingAs($student)->getJson('/api/v1/admin/certificates')->assertForbidden();
        $this->actingAs($student)->getJson('/api/v1/admin/certificates/eligibility')->assertForbidden();
    }

    public function test_instructor_can_list_completions_and_certificate_eligibility(): void
    {
        $instructor = $this->makeUser(User::ROLE_INSTRUCTOR);
        $student = $this->makeUser(User::ROLE_STUDENT);
        $this->makeProductionPathModules();
        $this->completeModuleFor($student, 'hygiene');

        $this->actingAs($instructor)
            ->getJson('/api/v1/admin/completions')
            ->assertOk()
            ->assertJsonPath('success', true);

        $this->actingAs($instructor)
            ->getJson('/api/v1/admin/certificates/eligibility')
            ->assertOk()
            ->assertJsonPath('success', true);
    }

    public function test_admin_can_list_completions_with_certificate_status(): void
    {
        $admin = $this->makeUser(User::ROLE_ADMIN);
        $student = $this->makeUser(User::ROLE_STUDENT);
        $this->makeProductionPathModules();
        $this->completeModuleFor($student, 'hygiene');

        $this->actingAs($admin)
            ->getJson('/api/v1/admin/completions?search=' . urlencode($student->email))
            ->assertOk()
            ->assertJsonPath('message', 'Completions retrieved.')
            ->assertJsonPath('data.0.user_id', $student->id)
            ->assertJsonPath('data.0.completed_modules', 1)
            ->assertJsonPath('data.0.required_modules', 2)
            ->assertJsonPath('data.0.completed_scenes', 1)
            ->assertJsonPath('data.0.required_scenes', 2)
            ->assertJsonPath('data.0.completion_rate', 50)
            ->assertJsonPath('data.0.certificate_eligible', false)
            ->assertJsonPath('data.0.certificate_issued', false);
    }

    public function test_admin_can_view_completion_detail_for_student(): void
    {
        $admin = $this->makeUser(User::ROLE_ADMIN);
        $student = $this->makeUser(User::ROLE_STUDENT);
        $this->makeProductionPathModules();
        $this->completeModuleFor($student, 'hygiene');
        $this->completeModuleFor($student, 'gowning', score: 92);
        $certificate = $this->issueCertificate($student);

        $this->actingAs($admin)
            ->getJson("/api/v1/admin/completions/{$student->id}")
            ->assertOk()
            ->assertJsonPath('message', 'Completion detail retrieved.')
            ->assertJsonPath('data.user.id', $student->id)
            ->assertJsonPath('data.summary.completed_modules', 2)
            ->assertJsonPath('data.summary.required_modules', 2)
            ->assertJsonPath('data.summary.certificate_eligible', true)
            ->assertJsonPath('data.summary.certificate_issued', true)
            ->assertJsonPath('data.modules.0.pretest_completed', true)
            ->assertJsonPath('data.modules.0.pretest_passed', true)
            ->assertJsonPath('data.modules.0.vr_completed', true)
            ->assertJsonPath('data.modules.0.posttest_completed', true)
            ->assertJsonPath('data.modules.0.posttest_passed', true)
            ->assertJsonPath('data.modules.0.is_completed', true)
            ->assertJsonPath('data.certificate.id', $certificate->id)
            ->assertJsonPath('data.certificate.number', $certificate->certificate_id)
            ->assertJsonPath('data.certificate.download_url', null);
    }

    public function test_admin_can_view_certificate_eligibility(): void
    {
        $admin = $this->makeUser(User::ROLE_ADMIN);
        $student = $this->makeUser(User::ROLE_STUDENT);
        $this->makeProductionPathModules();
        $this->completeModuleFor($student, 'hygiene');

        $this->actingAs($admin)
            ->getJson('/api/v1/admin/certificates/eligibility?eligible=0&search=' . urlencode($student->email))
            ->assertOk()
            ->assertJsonPath('message', 'Certificate eligibility retrieved.')
            ->assertJsonPath('data.0.user_id', $student->id)
            ->assertJsonPath('data.0.eligible', false)
            ->assertJsonPath('data.0.completed_required_modules', 1)
            ->assertJsonPath('data.0.total_required_modules', 2)
            ->assertJsonPath('data.0.missing_modules.0', 'gowning')
            ->assertJsonPath('data.0.certificate_issued', false);
    }

    public function test_admin_can_list_existing_certificates(): void
    {
        $admin = $this->makeUser(User::ROLE_ADMIN);
        $student = $this->makeUser(User::ROLE_STUDENT);
        $certificate = $this->issueCertificate($student);

        $this->actingAs($admin)
            ->getJson('/api/v1/admin/certificates?issued=1')
            ->assertOk()
            ->assertJsonPath('message', 'Certificates retrieved.')
            ->assertJsonPath('data.0.id', $certificate->id)
            ->assertJsonPath('data.0.number', $certificate->certificate_id)
            ->assertJsonPath('data.0.user.id', $student->id)
            ->assertJsonPath('data.0.download_url', null);
    }

    public function test_filters_return_success_and_do_not_crash(): void
    {
        $admin = $this->makeUser(User::ROLE_ADMIN);
        $student = $this->makeUser(User::ROLE_STUDENT);
        $modules = $this->makeProductionPathModules();
        $scene = $modules['hygiene']->scenes()->first();
        $this->completeModuleFor($student, 'hygiene');

        $query = http_build_query([
            'search' => $student->name,
            'user_id' => $student->id,
            'module_id' => $modules['hygiene']->id,
            'scene_id' => $scene->id,
            'status' => 'incomplete',
            'eligible' => '0',
            'issued' => '0',
            'date_from' => now()->subDay()->toDateString(),
            'date_to' => now()->addDay()->toDateString(),
            'page' => 1,
            'per_page' => 10,
        ]);

        $this->actingAs($admin)
            ->getJson("/api/v1/admin/completions?{$query}")
            ->assertOk()
            ->assertJsonPath('meta.total', 1);

        $this->actingAs($admin)
            ->getJson("/api/v1/admin/certificates/eligibility?{$query}")
            ->assertOk()
            ->assertJsonPath('meta.total', 1);
    }

    public function test_completion_and_certificate_endpoints_do_not_mutate_records(): void
    {
        $admin = $this->makeUser(User::ROLE_ADMIN);
        $student = $this->makeUser(User::ROLE_STUDENT);
        $this->makeProductionPathModules();
        $attempt = $this->completeModuleFor($student, 'hygiene');
        $certificate = $this->issueCertificate($student);
        $session = VrSession::where('user_id', $student->id)->latest('id')->first();

        $beforeAttempt = $attempt->fresh()->only(['score', 'passed', 'status', 'started_at', 'completed_at', 'updated_at']);
        $beforeSession = $session->fresh()->only(['session_status', 'progress_percentage', 'completed_at', 'updated_at']);
        $beforeCertificate = $certificate->fresh()->only(['status', 'issued_at', 'updated_at']);

        $this->actingAs($admin)->getJson('/api/v1/admin/completions')->assertOk();
        $this->actingAs($admin)->getJson("/api/v1/admin/completions/{$student->id}")->assertOk();
        $this->actingAs($admin)->getJson('/api/v1/admin/certificates')->assertOk();
        $this->actingAs($admin)->getJson('/api/v1/admin/certificates/eligibility')->assertOk();

        $this->assertEquals($beforeAttempt, $attempt->fresh()->only(['score', 'passed', 'status', 'started_at', 'completed_at', 'updated_at']));
        $this->assertEquals($beforeSession, $session->fresh()->only(['session_status', 'progress_percentage', 'completed_at', 'updated_at']));
        $this->assertEquals($beforeCertificate, $certificate->fresh()->only(['status', 'issued_at', 'updated_at']));
    }

    public function test_sensitive_fields_are_not_exposed(): void
    {
        $admin = $this->makeUser(User::ROLE_ADMIN);
        $student = $this->makeUser(User::ROLE_STUDENT);
        $this->makeProductionPathModules();
        $this->completeModuleFor($student, 'hygiene');
        $this->issueCertificate($student);

        $responses = [
            $this->actingAs($admin)->getJson('/api/v1/admin/completions')->assertOk(),
            $this->actingAs($admin)->getJson("/api/v1/admin/completions/{$student->id}")->assertOk(),
            $this->actingAs($admin)->getJson('/api/v1/admin/certificates')->assertOk(),
            $this->actingAs($admin)->getJson('/api/v1/admin/certificates/eligibility')->assertOk(),
        ];

        foreach ($responses as $response) {
            $json = json_encode($response->json());

            foreach (['password', 'remember_token', 'token', 'correct_answer', 'is_correct'] as $field) {
                $this->assertStringNotContainsString($field, $json);
            }
        }
    }

    private function makeUser(string $role): User
    {
        return User::factory()->create([
            'role' => $role,
            'status' => User::STATUS_ACTIVE,
        ]);
    }

    /**
     * @return array<string, TrainingModule>
     */
    private function makeProductionPathModules(): array
    {
        return [
            'hygiene' => $this->makeModule('hygiene', 1),
            'gowning' => $this->makeModule('gowning', 2),
        ];
    }

    private function makeModule(string $slug, int $order): TrainingModule
    {
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
            'learning_objectives' => ['Complete GMP workflow'],
            'order_index' => $order,
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
                'description' => 'Assessment description.',
                'status' => AssessmentStatus::ACTIVE->value,
                'number_of_questions_to_take' => 1,
                'randomize_questions' => true,
                'randomize_options' => true,
                'passing_score' => 70,
                'time_limit_minutes' => 10,
            ]);
        }

        return $module->load(['scenes', 'assessments']);
    }

    private function completeModuleFor(User $student, string $moduleSlug, int $score = 85): AssessmentAttempt
    {
        $module = TrainingModule::where('slug', $moduleSlug)->with('scenes', 'assessments')->firstOrFail();
        $scene = $module->scenes->first();

        foreach ([AssessmentType::PRETEST, AssessmentType::POSTTEST] as $type) {
            $assessment = $module->assessments->first(fn (Assessment $assessment) => $assessment->type->value === $type->value);

            AssessmentAttempt::create([
                'user_id' => $student->id,
                'assessment_id' => $assessment->id,
                'score' => $score,
                'passed' => true,
                'status' => 'completed',
                'started_at' => now()->subMinutes(20),
                'completed_at' => now()->subMinutes(10),
            ]);
        }

        VrSession::factory()
            ->completed()
            ->create([
                'user_id' => $student->id,
                'training_module_id' => $module->id,
                'scene_id' => $scene->id,
                'session_status' => 'completed',
                'progress_percentage' => 100,
                'completed_at' => now()->subMinutes(5),
                'last_activity_at' => now()->subMinutes(5),
            ]);

        return AssessmentAttempt::where('user_id', $student->id)
            ->whereHas('assessment', fn ($query) => $query
                ->where('module_id', $module->id)
                ->where('type', AssessmentType::POSTTEST->value))
            ->latest('id')
            ->firstOrFail();
    }

    private function issueCertificate(User $student): Certificate
    {
        return Certificate::create([
            'user_id' => $student->id,
            'certificate_type' => 'production_path_completed',
            'certificate_id' => 'PHARMVR-PROD-TEST-' . $student->id,
            'title' => 'Production Path Completed',
            'status' => 'issued',
            'issued_at' => now(),
            'metadata_json' => ['source' => 'feature-test'],
        ]);
    }
}
