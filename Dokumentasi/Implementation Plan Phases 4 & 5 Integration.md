# Implementation Plan: Phases 4 & 5 Integration

This plan covers the integration of VR functionality (Phase 4) and the AI Assistant (Phase 5).

## Phase 4: VR Integration

Goal: Replace simulation logic with real-time backend synchronization for VR pairing and sessions.

### [NEW] [vr_repository.dart](file:///e:/Flutter/pharmvrpro/lib/features/vr_experience/data/repositories/vr_repository.dart)
Connect to the following VR-specific endpoints:
- `POST /v1/vr/pairings/start`: Initiate pairing and receive a 6-digit code.
- `GET /v1/vr/status`: Monitor device connectivity and heartbeat.
- `GET /v1/vr/modules/{slug}/launch-readiness`: Comprehensive status check (Pre-test passed, VR on).
- `POST /v1/vr/sessions/start`: Trigger a new session from mobile.
- `GET /v1/vr/sessions/current`: Poll for session progress/telemetry.

### [MODIFY] [vr_connection_provider.dart](file:///e:/Flutter/pharmvrpro/lib/features/vr_experience/presentation/providers/vr_connection_provider.dart)
- Replace random token generation with `VrRepository` calls.
- Implement polling for pairing confirmation and heartbeat-based connectivity states.
- Track active sessions and their progress (percentage, current step).

### [MODIFY] VR Screens
- **VrConnectScreen**: Integrated with `VrRepository` to display the actual 6-digit pairing code.
- **VrLaunchScreen**: Use real readiness data. Only allow launch if pre-test is passed and headset is connected.
- **VrLaunchSessionScreen**: Manage the real "Starting" state and session creation.

---

## Phase 5: PharmAI Integration

Goal: Connect the AI Assistant and VR AI Guide intelligence.

### [MODIFY] AI Providers
- Connect to `GET /v1/ai/conversations` and `POST /v1/ai/chat`.
- Implement message history and real-time response parsing.
- Synchronize hint logs between the app and the VR headset.

## Verification Plan

### Automated Tests
- Mocked repository unit tests for all new VR and AI endpoints.

### Manual Verification
1. Verify pairing code matches backend state.
2. Confirm readiness checklist correctly identifies missing pre-tests.
3. Test session launching and verify data propagation to the headset.
