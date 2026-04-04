# VR Phase 4 Testing Strategy

This document outlines the testing strategy for the PharmVR Phase 4 VR Backend, focused on Meta Quest 3 standalone integration.

## 1. Test Categories

| Category | Focus |
| :--- | :--- |
| **Auth & Security** | Sanctum-bound mobile APIs and custom DeviceToken-bound headset APIs. |
| **Pairing Lifecycle** | The flow from pairing code generation to headset confirmation and token issuance. |
| **Session Management** | Lifecycle of a VR training session (Start -> Progress -> Complete/Interrupt). |
| **Data Integrity** | Validation of unified learning events (quizzes, telemetry) and summary JSON. |
| **Sync & UI Support** | Accuracy of Home API responses and launch readiness checks for Flutter. |

---

## 2. Must-Test Flows

### Flow A: The Pairing Handshake
1. **Mobile**: Generates 6-digit code.
2. **Headset**: Submits code + hardware ID.
3. **Backend**: Verifies code, links device to user, issues `device_token`.
4. **Mobile**: Polls/Checks status to see "Confirmed".

### Flow B: Session Lifecycle
1. **Mobile**: Starts session (POST `/sessions/start`).
2. **Headset**: Updates progress (PUT `/progress`) with telemetry.
3. **Headset**: Completes session (POST `/complete`) with final results.
4. **Mobile**: Displays summary (GET `/sessions/{id}`).

### Flow C: Home Sync
1. Call `/api/v1/home` after pairing.
2. Verify `vr_status_header` contains correct `device_type`, `connection_status`, and `ready_to_enter`.

---

## 3. Edge Cases & Error Handling

- **Pairing Expiry**: Headset submits code after 10-minute timeout -> `422 Unprocessable Content`.
- **Invalid Hardware ID**: Attempting to pair an unknown device type.
- **Session Stealing**: User A attempts to update progress for User B's session -> `403 Forbidden`.
- **Interrupted Flow**: Headset crashes/closes; verify `interrupted_at` is set and Home screen shows "Resume" button.
- **Re-pairing**: Pairing a new headset should deactivate/cleanup old tokens if applicable.
- **Malformed Telemetry**: Sending invalid JSON or exceeding payload limits in the Multiplexer.

---

## 4. Auth & Security Checklist

- [ ] Mobile endpoints require `auth:sanctum`.
- [ ] Headset endpoints require `X-VR-Device-Token`.
- [ ] Pairing code is never returned in plain text after generation (stored as hash).
- [ ] Device tokens are scoped to VR operations only.
- [ ] Rate limiting applied to pairing attempts (prevent brute force).
- [ ] Ownership check: `VrSession->user_id == Auth::id()`.

---

## 5. Done Criteria (Phase 4)

- [ ] **100% Core Flow Coverage**: All "Must-Test Flows" pass in automated feature tests.
- [ ] **Contract Compliance**: All JSON responses match the [vr_api_contract.md](file:///C:/Users/farha/.gemini/antigravity/brain/61f2e3ea-38c1-4c88-bb1d-a7fd5b161c0e/vr_api_contract.md).
- [ ] **Home Hub Ready**: Home API provides sufficient data to drive the Quest 3 status UI without 404s/Nulls.
- [ ] **Seeder Stability**: `php artisan db:seed --class=VrSeeder` runs without errors in empty databases.
- [ ] **Security Verified**: Unauthorized attempts to access sessions or pairing codes are blocked.
