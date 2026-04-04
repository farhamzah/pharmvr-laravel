<?php

namespace Tests\Feature\Api\V1\Vr;

use App\Models\User;
use App\Models\TrainingModule;
use App\Models\VrDevice;
use App\Models\VrPairing;
use App\Models\VrSession;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class VrPhase4Test extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $module;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->module = TrainingModule::create([
            'title' => 'GMP Basics',
            'slug' => 'gmp-basics',
            'description' => 'Test description',
            'is_active' => true,
        ]);
    }

    /** @test */
    public function a_user_can_start_a_pairing_session()
    {
        $response = $this->actingAs($this->user)->postJson('/api/v1/vr/pairings/start');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'pairing_id',
                    'pairing_code',
                    'pairing_token',
                    'expires_at',
                    'status',
                    'instructions',
                    'device_type_target'
                ]
            ]);

        $this->assertDatabaseHas('vr_pairings', [
            'user_id' => $this->user->id,
            'status' => 'pending',
        ]);
    }

    /** @test */
    public function a_user_can_check_current_pairing_status()
    {
        $pairing = VrPairing::create([
            'user_id' => $this->user->id,
            'pairing_code_hash' => Hash::make('123456'),
            'pairing_token_hash' => Hash::make('token'),
            'status' => 'pending',
            'expires_at' => now()->addMinutes(10),
        ]);

        $response = $this->actingAs($this->user)->getJson('/api/v1/vr/pairings/current');

        $response->assertStatus(200)
            ->assertJsonPath('data.pairing_id', $pairing->id)
            ->assertJsonPath('data.status', 'pending');
    }

    /** @test */
    public function a_user_can_cancel_a_pending_pairing()
    {
        $pairing = VrPairing::create([
            'user_id' => $this->user->id,
            'pairing_code_hash' => Hash::make('123456'),
            'pairing_token_hash' => Hash::make('token'),
            'status' => 'pending',
            'expires_at' => now()->addMinutes(10),
        ]);

        $response = $this->actingAs($this->user)->postJson("/api/v1/vr/pairings/{$pairing->id}/cancel");

        $response->assertStatus(200);
        $this->assertEquals('cancelled', $pairing->fresh()->status);
    }

    /** @test */
    public function a_headset_can_pair_with_specific_response_structure()
    {
        $pairing = VrPairing::create([
            'user_id' => $this->user->id,
            'pairing_code_hash' => Hash::make('654321'),
            'pairing_token_hash' => Hash::make('token'),
            'status' => 'pending',
            'expires_at' => now()->addMinutes(10),
        ]);

        $response = $this->postJson('/api/v1/vr/headset/pair', [
            'pairing_code' => '654321',
            'headset_identifier' => 'QUEST3-UNITY-ID',
            'device_name' => 'Meta Quest 3 Pro',
            'platform_name' => 'Quest OS 62',
            'device_type' => 'meta_quest_3',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'pairing_id',
                    'device_id',
                    'device_access_token',
                    'device_summary' => ['name', 'user_name', 'platform'],
                    'pairing_status',
                    'notes',
                ]
            ]);

        $this->assertEquals('confirmed', $pairing->fresh()->status);
    }

    /** @test */
    public function a_headset_can_send_heartbeat_with_issued_token()
    {
        $rawToken = 'secret-headset-token';
        $device = VrDevice::create([
            'user_id' => $this->user->id,
            'headset_identifier' => 'QUEST3-HEARTBEAT-ID',
            'device_token_hash' => Hash::make($rawToken),
            'status' => 'active',
            'last_seen_at' => now()->subMinutes(10),
        ]);

        $response = $this->withHeaders(['X-VR-Headset-ID' => 'QUEST3-HEARTBEAT-ID'])
            ->postJson('/api/v1/vr/headset/heartbeat', [
            'device_access_token' => $rawToken,
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.device_id', $device->id);

        $this->assertTrue($device->fresh()->last_seen_at->gt(now()->subMinute()));
    }

    /** @test */
    /** @test */
    public function a_headset_can_start_update_and_complete_a_vr_session()
    {
        $rawDeviceToken = 'test-device-token';
        $device = VrDevice::create([
            'user_id' => $this->user->id,
            'headset_identifier' => 'QUEST3-SESSION-TEST-ID',
            'device_token_hash' => Hash::make($rawDeviceToken),
            'status' => 'active',
        ]);

        // 1. Start Session
        $response = $this->withHeaders(['X-VR-Headset-ID' => 'QUEST3-SESSION-TEST-ID'])
            ->postJson('/api/v1/vr/headset/sessions/start', [
            'training_module_id' => $this->module->id,
            'device_access_token' => $rawDeviceToken,
        ]);

        $response->assertStatus(200);
        $sessionId = $response->json('data.session_id');

        // 2. Update Progress
        $updateResponse = $this->withHeaders(['X-VR-Headset-ID' => 'QUEST3-SESSION-TEST-ID'])
            ->putJson("/api/v1/vr/headset/sessions/{$sessionId}/progress", [
            'device_access_token' => $rawDeviceToken,
            'progress_percentage' => 50,
            'status' => 'playing',
            'current_step' => 'step_2_gowning',
        ]);

        $updateResponse->assertStatus(200)
            ->assertJsonPath('data.progress_percentage', 50);

        // 3. Complete Session
        $completeResponse = $this->withHeaders(['X-VR-Headset-ID' => 'QUEST3-SESSION-TEST-ID'])
            ->postJson("/api/v1/vr/headset/sessions/{$sessionId}/complete", [
            'device_access_token' => $rawDeviceToken,
            'final_progress' => 100,
        ]);
        $completeResponse->assertStatus(200)
            ->assertJsonPath('data.session_status', 'completed');

        $this->assertDatabaseHas('vr_sessions', [
            'id' => $sessionId,
            'session_status' => 'completed',
            'progress_percentage' => 100,
        ]);
    }

    /** @test */
    public function a_headset_can_interrupt_a_vr_session()
    {
        $rawDeviceToken = 'interrupt-test-token';
        $device = VrDevice::create([
            'user_id' => $this->user->id,
            'headset_identifier' => 'QUEST3-INTERRUPT-ID',
            'device_token_hash' => Hash::make($rawDeviceToken),
            'status' => 'active',
        ]);

        $session = VrSession::create([
            'user_id' => $this->user->id,
            'device_id' => $device->id,
            'training_module_id' => $this->module->id,
            'session_status' => 'playing',
        ]);

        $response = $this->withHeaders(['X-VR-Headset-ID' => 'QUEST3-INTERRUPT-ID'])
            ->postJson("/api/v1/vr/headset/sessions/{$session->id}/interrupt", [
            'device_access_token' => $rawDeviceToken,
            'reason' => 'user_removed_headset',
            'error_code' => 'ACCEL_WAKE',
            'reconnect_recommended' => true,
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('vr_sessions', [
            'id' => $session->id,
            'session_status' => 'interrupted',
        ]);
    }

    /** @test */
    public function a_headset_can_record_session_events()
    {
        $rawDeviceToken = 'event-test-token';
        $device = VrDevice::create([
            'user_id' => $this->user->id,
            'headset_identifier' => 'QUEST3-EVENT-TEST-ID',
            'device_token_hash' => Hash::make($rawDeviceToken),
            'status' => 'active',
        ]);

        $session = VrSession::create([
            'user_id' => $this->user->id,
            'device_id' => $device->id,
            'training_module_id' => $this->module->id,
            'session_status' => 'playing',
        ]);

        // Record Event
        $response = $this->withHeaders(['X-VR-Headset-ID' => 'QUEST3-EVENT-TEST-ID'])
            ->postJson("/api/v1/vr/headset/sessions/{$session->id}/events", [
            'device_access_token' => $rawDeviceToken,
            'event_type' => 'checkpoint_reached',
            'event_timestamp' => now()->toDateTimeString(),
            'event_payload' => [
                'checkpoint_id' => 'gate_1',
                'accuracy' => 95.5
            ],
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.event_type', 'checkpoint_reached');

        $this->assertDatabaseHas('vr_session_events', [
            'vr_session_id' => $session->id,
            'event_type' => 'checkpoint_reached',
            'user_id' => $this->user->id,
        ]);
    }

    /** @test */
    public function a_headset_can_record_in_vr_quiz_events()
    {
        $rawDeviceToken = 'quiz-test-token';
        $device = VrDevice::create([
            'user_id' => $this->user->id,
            'headset_identifier' => 'QUEST3-QUIZ-TEST-ID',
            'device_token_hash' => Hash::make($rawDeviceToken),
            'status' => 'active',
        ]);

        $session = VrSession::create([
            'user_id' => $this->user->id,
            'device_id' => $device->id,
            'training_module_id' => $this->module->id,
            'session_status' => 'playing',
        ]);

        // Record Quiz Event
        $response = $this->withHeaders(['X-VR-Headset-ID' => 'QUEST3-QUIZ-TEST-ID'])
            ->postJson("/api/v1/vr/headset/sessions/{$session->id}/quiz-events", [
            'device_access_token' => $rawDeviceToken,
            'event_type' => 'quiz_submitted',
            'event_timestamp' => now()->toDateTimeString(),
            'event_payload' => [
                'quiz_id' => 'safety_protocol_1',
                'question_id' => 'q_05',
                'answer_id' => 'ans_c',
                'is_correct' => true,
                'score_value' => 10
            ],
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.event_type', 'quiz_submitted')
            ->assertJsonPath('data.quiz_id', 'safety_protocol_1');

        $this->assertDatabaseHas('vr_session_events', [
            'vr_session_id' => $session->id,
            'event_type' => 'quiz_submitted',
            'user_id' => $this->user->id,
        ]);
    }

    /** @test */
    public function a_headset_can_record_staged_post_test_results()
    {
        $rawDeviceToken = 'stage-test-token';
        $device = VrDevice::create([
            'user_id' => $this->user->id,
            'headset_identifier' => 'QUEST3-STAGE-TEST-ID',
            'device_token_hash' => Hash::make($rawDeviceToken),
            'status' => 'active',
        ]);

        $session = VrSession::create([
            'user_id' => $this->user->id,
            'device_id' => $device->id,
            'training_module_id' => $this->module->id,
            'session_status' => 'playing',
        ]);

        // Record Stage Result
        $response = $this->withHeaders(['X-VR-Headset-ID' => 'QUEST3-STAGE-TEST-ID'])
            ->postJson("/api/v1/vr/headset/sessions/{$session->id}/stage-results", [
            'device_access_token' => $rawDeviceToken,
            'stage_name' => 'Sterile Gowning Protocol',
            'stage_score' => 85.50,
            'passed' => true,
            'submitted_at' => now()->toDateTimeString(),
            'metadata' => [
                'errors' => ['forgot_gloves_initially'],
                'time_taken' => 180
            ],
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.stage_name', 'Sterile Gowning Protocol')
            ->assertJsonPath('data.passed', true);

        $this->assertDatabaseHas('vr_session_stage_results', [
            'vr_session_id' => $session->id,
            'stage_name' => 'Sterile Gowning Protocol',
            'passed' => 1,
            'stage_score' => 85.50,
        ]);
    }

    /** @test */
    public function a_headset_can_record_ai_hint_logs()
    {
        $rawDeviceToken = 'hint-test-token';
        $device = VrDevice::create([
            'user_id' => $this->user->id,
            'headset_identifier' => 'QUEST3-HINT-TEST-ID',
            'device_token_hash' => Hash::make($rawDeviceToken),
            'status' => 'active',
        ]);

        $session = VrSession::create([
            'user_id' => $this->user->id,
            'device_id' => $device->id,
            'training_module_id' => $this->module->id,
            'session_status' => 'playing',
        ]);

        // Record Hint Log
        $response = $this->withHeaders(['X-VR-Headset-ID' => 'QUEST3-HINT-TEST-ID'])
            ->postJson("/api/v1/vr/headset/sessions/{$session->id}/hint-logs", [
            'device_access_token' => $rawDeviceToken,
            'hint_type' => 'warning',
            'trigger_reason' => 'sterile_breach',
            'related_step' => 'gowning_stage_2',
            'displayed_text' => 'Warning: Your hands touched the outer surface of the sterile gown.',
            'displayed_at' => now()->toDateTimeString(),
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.hint_type', 'warning');

        $this->assertDatabaseHas('vr_session_hints', [
            'vr_session_id' => $session->id,
            'hint_type' => 'warning',
            'trigger_reason' => 'sterile_breach',
        ]);
    }

    /** @test */
    public function a_headset_can_send_unified_learning_events()
    {
        $rawDeviceToken = 'unified-test-token';
        $device = VrDevice::create([
            'user_id' => $this->user->id,
            'headset_identifier' => 'QUEST3-UNIFIED-ID',
            'device_token_hash' => Hash::make($rawDeviceToken),
            'status' => 'active',
        ]);

        $session = VrSession::create([
            'user_id' => $this->user->id,
            'device_id' => $device->id,
            'training_module_id' => $this->module->id,
            'session_status' => 'playing',
        ]);

        // 1. Send Unified Quiz Event
        $response = $this->withHeaders(['X-VR-Headset-ID' => 'QUEST3-UNIFIED-ID'])
            ->postJson("/api/v1/vr/headset/sessions/{$session->id}/unified-events", [
            'device_access_token' => $rawDeviceToken,
            'occurred_at' => now()->toDateTimeString(),
            'category' => 'quiz_event',
            'type' => 'quiz_submitted',
            'related_step' => 'step_1',
            'payload' => [
                'quiz_id' => 'q1',
                'question_id' => 'qq1',
                'is_correct' => true
            ],
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('vr_session_events', ['event_type' => 'quiz_submitted']);

        // 2. Send Unified Stage Result
        $response = $this->withHeaders(['X-VR-Headset-ID' => 'QUEST3-UNIFIED-ID'])
            ->postJson("/api/v1/vr/headset/sessions/{$session->id}/unified-events", [
            'device_access_token' => $rawDeviceToken,
            'occurred_at' => now()->toDateTimeString(),
            'category' => 'stage_result',
            'type' => 'Gowning Final Result',
            'payload' => [
                'stage_score' => 99.0,
                'passed' => true
            ],
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('vr_session_stage_results', [
            'stage_name' => 'Gowning Final Result',
            'stage_score' => 99.0
        ]);
    }

    /** @test */
    public function v1_vr_status_returns_refined_response_structure()
    {
        // 1. Unpaired status
        $response = $this->actingAs($this->user)->getJson('/api/v1/vr/status');
        $response->assertStatus(200)
            ->assertJsonPath('data.paired', false)
            ->assertJsonPath('data.connection_status', 'offline')
            ->assertJsonPath('data.recommended_next_action', 'Mulai Pairing Perangkat');

        // 2. Paired but offline
        $device = VrDevice::create([
            'user_id' => $this->user->id,
            'headset_identifier' => 'QUEST3-STATUS-TEST',
            'device_token_hash' => Hash::make('token'),
            'status' => 'active',
            'last_seen_at' => now()->subMinutes(60),
            'device_type' => 'meta_quest_3',
            'device_name' => 'My Quest 3',
        ]);

        $response = $this->actingAs($this->user)->getJson('/api/v1/vr/status');
        $response->assertStatus(200)
            ->assertJsonPath('data.paired', true)
            ->assertJsonPath('data.connection_status', 'offline')
            ->assertJsonPath('data.headset_name', 'My Quest 3');

        // 3. Standby
        $device->update(['last_seen_at' => now()->subMinutes(5)]);
        $response = $this->actingAs($this->user)->getJson('/api/v1/vr/status');
        $response->assertJsonPath('data.connection_status', 'standby')
            ->assertJsonPath('data.ready', true);

        // 4. Connected
        $device->update(['last_seen_at' => now()->subMinute()]);
        $response = $this->actingAs($this->user)->getJson('/api/v1/vr/status');
        $response->assertJsonPath('data.connection_status', 'connected')
            ->assertJsonPath('data.ready', true);
            
        // 5. Active Session
        $session = VrSession::create([
            'user_id' => $this->user->id,
            'device_id' => $device->id,
            'training_module_id' => $this->module->id,
            'session_status' => 'playing',
            'progress_percentage' => 45,
            'started_at' => now(),
        ]);
        
        $response = $this->actingAs($this->user)->getJson('/api/v1/vr/status');
        $response->assertJsonPath('data.active_session_id', $session->id)
            ->assertJsonPath('data.active_module_summary.title', 'GMP Basics')
            ->assertJsonPath('data.recommended_next_action', 'Lanjutkan Sesi Pelatihan');
    }

    /** @test */
    public function v1_vr_launch_readiness_checks_all_requirements()
    {
        // 1. Initial State: Unpaired, No Pre-test
        $response = $this->actingAs($this->user)->getJson("/api/v1/vr/modules/{$this->module->slug}/launch-readiness");
        
        $response->assertStatus(200)
            ->assertJsonPath('data.eligible_to_launch', false)
            ->assertJsonPath('data.pre_test_passed', false)
            ->assertJsonPath('data.quest3_paired', false)
            ->assertSee('Anda harus lulus Pre-test terlebih dahulu')
            ->assertSee('Headset Meta Quest 3 belum dipasangkan');

        // 2. Setup Assessment (Pre-test)
        $assessment = \App\Models\Assessment::create([
            'training_module_id' => $this->module->id,
            'type' => 'pre_test',
            'title' => 'GMP Pre-test',
            'min_score' => 80,
            'is_active' => true,
        ]);

        // 3. Complete Pre-test
        \App\Models\AssessmentAttempt::create([
            'user_id' => $this->user->id,
            'assessment_id' => $assessment->id,
            'score' => 85,
            'status' => 'passed',
            'completed_at' => now(),
        ]);

        // 4. Pair and Connect Device
        VrDevice::create([
            'user_id' => $this->user->id,
            'headset_identifier' => 'QUEST3-READINESS-TEST',
            'device_token_hash' => Hash::make('token'),
            'status' => 'active',
            'last_seen_at' => now(),
            'device_type' => 'meta_quest_3',
        ]);

        $response = $this->actingAs($this->user)->getJson("/api/v1/vr/modules/{$this->module->slug}/launch-readiness");
        
        $response->assertStatus(200)
            ->assertJsonPath('data.eligible_to_launch', true)
            ->assertJsonPath('data.pre_test_passed', true)
            ->assertJsonPath('data.quest3_connected', true)
            ->assertJsonPath('data.recommended_next_action', 'Luncurkan Pelatihan VR');
    }

    /** @test */
    public function a_mobile_user_can_start_a_vr_session()
    {
        // 1. Setup readiness (Pre-test passed)
        $assessment = \App\Models\Assessment::create([
            'training_module_id' => $this->module->id,
            'type' => 'pre_test',
            'title' => 'GMP Pre-test',
            'min_score' => 80,
            'is_active' => true,
        ]);

        \App\Models\AssessmentAttempt::create([
            'user_id' => $this->user->id,
            'assessment_id' => $assessment->id,
            'score' => 90,
            'status' => 'passed',
            'completed_at' => now(),
        ]);

        // 2. Setup device (Paired and Online)
        VrDevice::create([
            'user_id' => $this->user->id,
            'headset_identifier' => 'MOBILE-START-TEST-ID',
            'device_token_hash' => Hash::make('token'),
            'status' => 'active',
            'last_seen_at' => now(),
            'device_type' => 'meta_quest_3',
        ]);

        // 3. Start Session via Mobile
        $response = $this->actingAs($this->user)->postJson('/api/v1/vr/sessions/start', [
            'module_slug' => $this->module->slug,
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.session_status', 'starting')
            ->assertJsonPath('data.module.slug', $this->module->slug)
            ->assertJsonPath('data.recommended_frontend_state', 'vr_session_active');

        $this->assertDatabaseHas('vr_sessions', [
            'user_id' => $this->user->id,
            'training_module_id' => $this->module->id,
            'session_status' => 'starting',
        ]);
    }

    /** @test */
    public function home_api_returns_correct_vr_status()
    {
        // 1. Initially no device
        $response = $this->actingAs($this->user)->getJson('/api/v1/home');
        $response->assertJsonPath('data.vr_status_header.is_paired', false);

            // 2. Create device
            $device = VrDevice::create([
                'user_id' => $this->user->id,
                'headset_identifier' => 'HARDWARE-ID',
                'device_token_hash' => Hash::make('TOKEN'),
                'last_seen_at' => now(),
                'status' => 'active',
            ]);

            $response = $this->actingAs($this->user)->getJson('/api/v1/home');
            $response->assertJsonPath('data.vr_status_header.is_paired', true)
                ->assertJsonPath('data.vr_status_header.connection_status', 'connected');

            // 3. Create active session
            $session = VrSession::create([
                'user_id' => $this->user->id,
                'device_id' => $device->id,
                'training_module_id' => $this->module->id,
                'session_status' => 'playing',
                'progress_percentage' => 33,
            ]);

            $response = $this->actingAs($this->user)->getJson('/api/v1/home');
            $response->assertJsonPath('data.vr_status_header.ready_to_enter', true)
                ->assertJsonPath('data.vr_status_header.active_session.module_title', 'GMP Basics');
    }
    /** @test */
    public function a_mobile_user_can_fetch_current_session_status()
    {
        // 1. No sessions
        $response = $this->actingAs($this->user)->getJson('/api/v1/vr/sessions/current');
        $response->assertStatus(200)
            ->assertJsonPath('data', null);

        // 2. An active session
        $device = VrDevice::create([
            'user_id' => $this->user->id,
            'headset_identifier' => 'D1',
            'status' => 'active',
            'device_token_hash' => Hash::make('token')
        ]);

        $session = VrSession::create([
            'user_id' => $this->user->id,
            'device_id' => $device->id,
            'training_module_id' => $this->module->id,
            'session_status' => 'playing',
            'progress_percentage' => 45,
            'started_at' => now(),
            'last_activity_at' => now(),
        ]);

        $response = $this->actingAs($this->user)->getJson('/api/v1/vr/sessions/current');
        $response->assertStatus(200)
            ->assertJsonPath('data.session_id', $session->id)
            ->assertJsonPath('data.recommended_next_action', 'Lanjutkan di Headset')
            ->assertJsonPath('data.recommended_next_route', 'vr_session_status');

        // 3. A completed session (should return as fallback if no active)
        $session->update(['session_status' => 'completed', 'completed_at' => now()]);

        $response = $this->actingAs($this->user)->getJson('/api/v1/vr/sessions/current');
        $response->assertStatus(200)
            ->assertJsonPath('data.session_id', $session->id)
            ->assertJsonPath('data.recommended_next_action', 'Lihat Ringkasan')
            ->assertJsonPath('data.recommended_next_route', 'vr_session_summary');
    }

    /** @test */
    public function a_mobile_user_can_show_specific_session_details()
    {
        $device = VrDevice::create([
            'user_id' => $this->user->id,
            'headset_identifier' => 'D2',
            'status' => 'active',
            'device_token_hash' => Hash::make('token')
        ]);

        $session = VrSession::create([
            'user_id' => $this->user->id,
            'device_id' => $device->id,
            'training_module_id' => $this->module->id,
            'session_status' => 'interrupted',
            'started_at' => now(),
            'last_activity_at' => now(),
        ]);

        $response = $this->actingAs($this->user)->getJson("/api/v1/vr/sessions/{$session->id}");
        $response->assertStatus(200)
            ->assertJsonPath('data.session_id', $session->id)
            ->assertJsonPath('data.recommended_next_action', 'Mulai Ulang Sesi');
    }
}
