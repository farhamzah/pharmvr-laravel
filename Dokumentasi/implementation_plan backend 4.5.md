# Phase 4: VR Backend (Meta Quest 3 First) Implementation Plan

Build the VR backend infrastructure for Meta Quest 3, focusing on secure pairing, session management, and frontend synchronization.

## Proposed Changes

### Database and Models

#### [NEW] [VrDevice.php](file:///e:/Flutter/pharmvrpro/backend/app/Models/VrDevice.php)
Model to represent a paired VR headset.
- Fields: [id](file:///e:/Flutter/pharmvrpro/backend/app/Http/Controllers/Api/V1/Vr/VrSessionController.php#18-33), `user_id`, `device_name`, `internal_id`, `last_seen_at`, `is_active`.
- Relationship: `belongsTo(User::class)`.

#### [NEW] [VrPairingSession.php](file:///e:/Flutter/pharmvrpro/backend/app/Models/VrPairingSession.php)
Model to handle short-lived pairing attempts.
- Fields: [id](file:///e:/Flutter/pharmvrpro/backend/app/Http/Controllers/Api/V1/Vr/VrSessionController.php#18-33), `user_id`, `pairing_code` (6-digit), `pairing_token` (UUID), [status](file:///e:/Flutter/pharmvrpro/backend/app/Http/Controllers/Api/V1/Vr/VrStatusController.php#17-51) (pending, confirmed, expired), `expires_at`.

#### [NEW] [VrSession.php](file:///e:/Flutter/pharmvrpro/backend/app/Models/VrSession.php)
Model to track individual VR training sessions.
- Fields: [id](file:///e:/Flutter/pharmvrpro/backend/app/Http/Controllers/Api/V1/Vr/VrSessionController.php#18-33), `user_id`, `vr_device_id`, `training_module_id`, [status](file:///e:/Flutter/pharmvrpro/backend/app/Http/Controllers/Api/V1/Vr/VrStatusController.php#17-51) (starting, playing, completed, interrupted, failed), `progress` (int), `metadata` (json), `started_at`, `finished_at`.

### VR Headset Module (Unity Side)

#### [MODIFY] [api.php](file:///e:/Flutter/pharmvrpro/backend/routes/api.php)
- Add `/v1/vr/headset/pair` and `/v1/vr/headset/heartbeat` routes.
- Apply rate-limiting middleware to `headset/pair`.

#### [MODIFY] [VrPairingController.php](file:///e:/Flutter/pharmvrpro/backend/app/Http/Controllers/Api/V1/Vr/VrPairingController.php)
- Implement [pair()](file:///e:/Flutter/pharmvrpro/backend/app/Models/VrSession.php#50-54): Validates pairing code, creates/updates device, and issues a secure `device_access_token`.
- Refactor existing [confirmPairing](file:///e:/Flutter/pharmvrpro/backend/app/Http/Controllers/Api/V1/Vr/VrPairingController.php#104-174) into [pair](file:///e:/Flutter/pharmvrpro/backend/app/Models/VrSession.php#50-54) based on user requirements.

#### [NEW] [VrDeviceController.php](file:///e:/Flutter/pharmvrpro/backend/app/Http/Controllers/Api/V1/Vr/VrDeviceController.php)
- Implement `heartbeat()`: Updates `last_seen_at` using device token authentication.
- Implement `unpair()`: Soft-deletes or deactivates the device record.

## Verification Plan

### Automated Tests
- Test brute force protection on `headset/pair`.
- Verify `device_access_token` issuance and subsequent usage in `heartbeat`.
- Verify hardware binding via `headset_identifier`.

### Controllers and Services

#### [NEW] [VrPairingController.php](file:///e:/Flutter/pharmvrpro/backend/app/Http/Controllers/Api/V1/Vr/VrPairingController.php)
Handles pairing logic for both Mobile and Headset.
- `requestPairing`: Mobile app generates a code.
- [confirmPairing](file:///e:/Flutter/pharmvrpro/backend/app/Http/Controllers/Api/V1/Vr/VrPairingController.php#104-174): Headset app inputs code to bind device.

#### [NEW] [VrSessionController.php](file:///e:/Flutter/pharmvrpro/backend/app/Http/Controllers/Api/V1/Vr/VrSessionController.php)
Handles VR session lifecycle.
- [start](file:///e:/Flutter/pharmvrpro/backend/app/Http/Controllers/Api/V1/Vr/VrSessionController.php#34-74): Initialize session.
- [update](file:///e:/Flutter/pharmvrpro/backend/app/Http/Controllers/Api/V1/Vr/VrSessionController.php#75-117): Heartbeat and progress update.
- [complete](file:///e:/Flutter/pharmvrpro/backend/app/Http/Controllers/Api/V1/Vr/VrSessionController.php#118-137): Finalize session.
- [fail](file:///e:/Flutter/pharmvrpro/backend/app/Http/Controllers/Api/V1/Vr/VrSessionController.php#138-156): Handle interruptions.

#### [NEW] [VrStatusController.php](file:///e:/Flutter/pharmvrpro/backend/app/Http/Controllers/Api/V1/Vr/VrStatusController.php)
Handles connection status and launch readiness.
- [status](file:///e:/Flutter/pharmvrpro/backend/app/Http/Controllers/Api/V1/Vr/VrStatusController.php#17-51): Current device and session status.
- [readiness](file:///e:/Flutter/pharmvrpro/backend/app/Http/Controllers/Api/V1/Vr/VrStatusController.php#52-76): Check if a module is ready to launch (from Phase 3 logic).

#### [MODIFY] [HomeController.php](file:///e:/Flutter/pharmvrpro/backend/app/Http/Controllers/Api/V1/Content/HomeController.php)
- Include VR device status and active session in the home response.

### Routes

#### [MODIFY] [api.php](file:///e:/Flutter/pharmvrpro/backend/routes/api.php)
Add new routes under `v1` prefix and `auth:sanctum` middleware.

## Verification Plan

### Automated Tests
1.  **Pairing Flow Test**: 
    - Mock mobile request -> Get code.
    - Mock headset confirmation -> Get device token.
    - Verify device is bound to user.
2.  **Session Lifecycle Test**:
    - Start session -> Status 'playing'.
    - Update progress -> Verify progress changes.
    - Complete session -> Status 'completed', set `finished_at`.
3.  **Readiness Test**:
    - Check readiness for a locked module (should fail).
    - Check readiness for an available module (should pass).
- Update [VrPhase4Test.php](file:///e:/Flutter/pharmvrpro/backend/tests/Feature/Api/V1/Vr/VrPhase4Test.php) to test the new mobile-specific pairing routes and ensure payload structure matches requirements.
- Verify that only one active pairing is allowed or properly managed.
- Verify cancellation logic.

### Manual Verification
- Verify response structure matches the Quest 3 Unity integration requirements.
- Test heartbeat logic by checking `last_seen_at` after heartbeat calls.
