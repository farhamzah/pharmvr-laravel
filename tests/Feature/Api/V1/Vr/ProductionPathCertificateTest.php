<?php

namespace Tests\Feature\Api\V1\Vr;

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

class ProductionPathCertificateTest extends TestCase
{
    use RefreshDatabase;

    private const PRODUCTION_PATH_SCENES = [
        'hygiene', 'gowning', 'airlock', 'production_corridor',
        'weighing', 'granulation', 'final_mixing', 'tabletting',
        'coating', 'blistering', 'secondary_packing',
    ];

    // ─────────────────────────────────────────────────────────────────────
    // Helper: create a user with all 11 scenes completed
    // ─────────────────────────────────────────────────────────────────────
    private function createCompletedUser(): User
    {
        $user = User::factory()->create();

        foreach (self::PRODUCTION_PATH_SCENES as $index => $slug) {
            $module = TrainingModule::create([
                'title' => str_replace('_', ' ', $slug),
                'slug' => $slug,
                'description' => 'Test module',
                'is_active' => true,
            ]);

            $scene = Scene::create([
                'training_module_id' => $module->id,
                'slug' => $slug,
                'title' => str_replace('_', ' ', $slug),
                'description' => 'Test scene',
                'order_index' => $index + 1,
                'priority' => 'P0',
                'difficulty' => 'beginner',
                'estimated_minutes' => 10,
                'environment_asset' => $slug,
                'is_active' => true,
            ]);

            VrSession::create([
                'user_id' => $user->id,
                'training_module_id' => $module->id,
                'scene_id' => $scene->id,
                'session_status' => 'completed',
                'platform' => 'webxr',
                'progress_percentage' => 100,
                'total_score' => 100,
                'started_at' => now()->subMinutes(10),
                'completed_at' => now(),
                'last_activity_at' => now(),
                'summary_json' => [
                    'completion_summary' => [
                        'scene_slug' => $slug,
                        'completion_type' => $slug . '_test_completion',
                    ],
                ],
            ]);

            $assessment = Assessment::create([
                'module_id' => $module->id,
                'type' => AssessmentType::POSTTEST->value,
                'title' => str_replace('_', ' ', $slug) . ' Post-Test',
                'status' => AssessmentStatus::ACTIVE->value,
                'number_of_questions_to_take' => 5,
                'randomize_questions' => false,
                'randomize_options' => false,
                'passing_score' => 70,
                'time_limit_minutes' => 10,
            ]);

            AssessmentAttempt::create([
                'user_id' => $user->id,
                'assessment_id' => $assessment->id,
                'score' => 100,
                'passed' => true,
                'status' => 'completed',
                'started_at' => now()->subMinutes(5),
                'completed_at' => now(),
            ]);
        }

        return $user;
    }

    private function createVrCompletedUserWithoutFinalPosttest(): User
    {
        $user = $this->createCompletedUser();

        $secondaryAssessment = Assessment::whereHas('trainingModule', function ($query) {
            $query->where('slug', 'secondary_packing');
        })->where('type', AssessmentType::POSTTEST->value)->firstOrFail();

        AssessmentAttempt::where('user_id', $user->id)
            ->where('assessment_id', $secondaryAssessment->id)
            ->delete();

        return $user;
    }

    private function createVrCompletedUserWithFailedFinalPosttest(): User
    {
        $user = $this->createCompletedUser();

        $secondaryAssessment = Assessment::whereHas('trainingModule', function ($query) {
            $query->where('slug', 'secondary_packing');
        })->where('type', AssessmentType::POSTTEST->value)->firstOrFail();

        AssessmentAttempt::where('user_id', $user->id)
            ->where('assessment_id', $secondaryAssessment->id)
            ->delete();

        AssessmentAttempt::create([
            'user_id' => $user->id,
            'assessment_id' => $secondaryAssessment->id,
            'score' => 0,
            'passed' => false,
            'status' => 'completed',
            'started_at' => now()->subMinutes(5),
            'completed_at' => now(),
        ]);

        return $user;
    }

    // ─────────────────────────────────────────────────────────────────────
    // Helper: create an issued certificate directly
    // ─────────────────────────────────────────────────────────────────────
    private function createIssuedCertificate(User $user, string $certId = 'PHARMVR-PROD-2026-1-TEST1234'): Certificate
    {
        return Certificate::create([
            'user_id' => $user->id,
            'certificate_type' => 'production_path_completed',
            'certificate_id' => $certId,
            'title' => 'Production Path Completed',
            'status' => 'issued',
            'issued_at' => now(),
            'metadata_json' => [
                'production_path_title' => 'Non-Sterile Solid Dosage Production Path',
                'completed_scenes' => 11,
                'total_scenes' => 11,
            ],
        ]);
    }

    // =========================================================================
    // A. DOWNLOAD TESTS
    // =========================================================================

    public function test_incomplete_user_cannot_download_production_path_certificate(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->getJson('/api/v1/vr/certificates/production-path/download')
            ->assertStatus(422)
            ->assertJsonPath('message', 'Production path is not completed.');
    }

    public function test_issued_certificate_downloads_as_pdf(): void
    {
        $user = User::factory()->create(['name' => 'QA Learner']);
        $certificate = $this->createIssuedCertificate($user);

        $response = $this->actingAs($user)
            ->get('/api/v1/vr/certificates/production-path/download');

        $response->assertStatus(200);

        // Assert PDF content type
        $contentType = $response->headers->get('Content-Type') ?? '';
        $this->assertStringContainsString('application/pdf', $contentType);

        // Assert filename contains certificate_id and .pdf extension
        $disposition = $response->headers->get('Content-Disposition') ?? '';
        $this->assertStringContainsString($certificate->certificate_id, $disposition);
        $this->assertStringContainsString('.pdf', $disposition);

        // Assert PDF binary signature (%PDF-)
        $content = $response->getContent();
        $this->assertStringStartsWith('%PDF-', $content);
    }

    public function test_completed_user_can_download_pdf_without_prior_generate(): void
    {
        $user = $this->createCompletedUser();

        $response = $this->actingAs($user)
            ->get('/api/v1/vr/certificates/production-path/download');

        $response->assertStatus(200);

        $contentType = $response->headers->get('Content-Type') ?? '';
        $this->assertStringContainsString('application/pdf', $contentType);

        $content = $response->getContent();
        $this->assertStringStartsWith('%PDF-', $content);
    }

    // =========================================================================
    // B. GENERATE TESTS
    // =========================================================================

    public function test_generate_certificate_returns_download_url_when_path_is_completed(): void
    {
        $user = $this->createCompletedUser();

        $this->actingAs($user)
            ->postJson('/api/v1/vr/certificates/production-path/generate')
            ->assertStatus(201)
            ->assertJsonPath('data.status', 'issued')
            ->assertJsonPath('data.download_url', url('/api/v1/vr/certificates/production-path/download'))
            ->assertJsonStructure([
                'data' => ['certificate_id', 'download_url'],
            ]);
    }

    public function test_final_vr_completion_without_secondary_posttest_does_not_unlock_certificate(): void
    {
        $user = $this->createVrCompletedUserWithoutFinalPosttest();

        $this->actingAs($user)
            ->getJson('/api/v1/vr/reports/production-path')
            ->assertStatus(200)
            ->assertJsonPath('data.completed_scenes', 10)
            ->assertJsonPath('data.production_path_completed', false)
            ->assertJsonPath('data.certificate.eligible', false);

        $this->actingAs($user)
            ->postJson('/api/v1/vr/certificates/production-path/generate')
            ->assertStatus(422)
            ->assertJsonPath('message', 'Production path is not completed.');
    }

    public function test_failed_secondary_posttest_does_not_unlock_certificate(): void
    {
        $user = $this->createVrCompletedUserWithFailedFinalPosttest();

        $this->actingAs($user)
            ->getJson('/api/v1/vr/reports/production-path')
            ->assertStatus(200)
            ->assertJsonPath('data.completed_scenes', 10)
            ->assertJsonPath('data.production_path_completed', false)
            ->assertJsonPath('data.certificate.eligible', false)
            ->assertJsonPath('data.scene_results.10.vr_completed', true)
            ->assertJsonPath('data.scene_results.10.post_test_passed', false);

        $this->actingAs($user)
            ->postJson('/api/v1/vr/certificates/production-path/generate')
            ->assertStatus(422)
            ->assertJsonPath('message', 'Production path is not completed.');
    }

    public function test_regenerate_certificate_does_not_create_duplicate(): void
    {
        $user = $this->createCompletedUser();

        $first = $this->actingAs($user)
            ->postJson('/api/v1/vr/certificates/production-path/generate')
            ->assertStatus(201)
            ->json('data.certificate_id');

        $second = $this->actingAs($user)
            ->postJson('/api/v1/vr/certificates/production-path/generate')
            ->assertStatus(201)
            ->json('data.certificate_id');

        $this->assertSame($first, $second, 'Re-generate should return the same certificate_id.');

        $this->assertSame(
            1,
            Certificate::where('user_id', $user->id)
                ->where('certificate_type', 'production_path_completed')
                ->count()
        );
    }

    // =========================================================================
    // C. PUBLIC VERIFICATION TESTS
    // =========================================================================

    public function test_issued_certificate_can_be_verified_publicly(): void
    {
        $user = User::factory()->create(['name' => 'Verified Learner']);
        $certificate = $this->createIssuedCertificate($user, 'PHARMVR-PROD-2026-99-VERIFY01');

        $this->getJson('/api/v1/public/certificates/' . $certificate->certificate_id . '/verify')
            ->assertStatus(200)
            ->assertJsonPath('data.valid', true)
            ->assertJsonPath('data.certificate_id', $certificate->certificate_id)
            ->assertJsonPath('data.status', 'issued')
            ->assertJsonPath('data.learner_name', 'Verified Learner')
            ->assertJsonPath('data.completed_scenes', 11)
            ->assertJsonPath('data.total_scenes', 11)
            ->assertJsonStructure([
                'data' => [
                    'valid', 'certificate_id', 'certificate_type',
                    'title', 'status', 'issued_at',
                    'learner_name', 'production_path_title',
                    'completed_scenes', 'total_scenes',
                ],
            ]);
    }

    public function test_public_verification_does_not_expose_sensitive_fields(): void
    {
        $user = User::factory()->create(['email' => 'secret@test.com']);
        $certificate = $this->createIssuedCertificate($user, 'PHARMVR-PROD-2026-99-NOSECRET');

        $response = $this->getJson('/api/v1/public/certificates/' . $certificate->certificate_id . '/verify')
            ->assertStatus(200);

        $data = $response->json('data');

        // Must NOT expose email or full metadata_json
        $this->assertArrayNotHasKey('email', $data);
        $this->assertArrayNotHasKey('metadata_json', $data);
        $this->assertArrayNotHasKey('user_id', $data);
    }

    public function test_invalid_certificate_id_returns_not_found(): void
    {
        $this->getJson('/api/v1/public/certificates/PHARMVR-INVALID-XXXX/verify')
            ->assertStatus(404)
            ->assertJsonPath('data.valid', false);
    }

    public function test_public_verification_does_not_require_authentication(): void
    {
        $user = User::factory()->create();
        $certificate = $this->createIssuedCertificate($user, 'PHARMVR-PROD-2026-99-NOAUTH01');

        // No actingAs — public endpoint
        $this->getJson('/api/v1/public/certificates/' . $certificate->certificate_id . '/verify')
            ->assertStatus(200)
            ->assertJsonPath('data.valid', true);
    }
}
