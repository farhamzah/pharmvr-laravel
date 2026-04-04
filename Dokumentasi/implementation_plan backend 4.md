# Phase 4: VR Backend (Meta Quest 3 First) Implementation Plan

Build the VR backend infrastructure for Meta Quest 3, focusing on secure pairing, session management, and frontend synchronization.

## Proposed Changes

### Database and Models

#### [NEW] [VrDevice.php](file:///e:/Flutter/pharmvrpro/backend/app/Models/VrDevice.php)
Model to represent a paired VR headset.
- Fields: `id`, `user_id`, `device_name`, `internal_id`, `last_seen_at`, `is_active`.
- Relationship: `belongsTo(User::class)`.

#### [NEW] [VrPairingSession.php](file:///e:/Flutter/pharmvrpro/backend/app/Models/VrPairingSession.php)
Model to handle short-lived pairing attempts.
- Fields: `id`, `user_id`, `pairing_code` (6-digit), `pairing_token` (UUID), `status` (pending, confirmed, expired), `expires_at`.

#### [NEW] [VrSession.php](file:///e:/Flutter/pharmvrpro/backend/app/Models/VrSession.php)
Model to track individual VR training sessions.
- Fields: `id`, `user_id`, `vr_device_id`, `training_module_id`, `status` (starting, playing, completed, interrupted, failed), `progress` (int), `metadata` (json), `started_at`, `finished_at`.

### Controllers and Services

#### [NEW] [VrPairingController.php](file:///e:/Flutter/pharmvrpro/backend/app/Http/Controllers/Api/V1/Vr/VrPairingController.php)
Handles pairing logic for both Mobile and Headset.
- `requestPairing`: Mobile app generates a code.
- `confirmPairing`: Headset app inputs code to bind device.

#### [NEW] [VrSessionController.php](file:///e:/Flutter/pharmvrpro/backend/app/Http/Controllers/Api/V1/Vr/VrSessionController.php)
Handles VR session lifecycle.
- `start`: Initialize session.
- `update`: Heartbeat and progress update.
- `complete`: Finalize session.
- `fail`: Handle interruptions.

#### [NEW] [VrStatusController.php](file:///e:/Flutter/pharmvrpro/backend/app/Http/Controllers/Api/V1/Vr/VrStatusController.php)
Handles connection status and launch readiness.
- `status`: Current device and session status.
- `readiness`: Check if a module is ready to launch (from Phase 3 logic).

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

### Manual Verification
- Verify response structure matches the Quest 3 Unity integration requirements.
- Test heartbeat logic by checking `last_seen_at` after heartbeat calls.
