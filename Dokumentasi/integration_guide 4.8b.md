# PharmVR Phase 4: VR Integration Guide

This guide provides the technical specifications for integrating the Meta Quest 3 "Quest-3-first" ecosystem with the PharmVR backend.

## 1. Device Pairing Flow (Headset)

### Step 1: Request Pairing Code (Mobile)
Mobile app calls `POST /api/v1/vr/pairings/start` to get a 6-digit code.

### Step 2: Confirm Pairing (Unity)
Headset app displays a UI for the user to enter the 6-digit code.
- **Endpoint**: `POST /api/v1/vr/headset/pair`
- **Payload**:
  ```json
  {
    "pairing_code": "123456",
    "headset_identifier": "UNIQUE-HW-ID",
    "device_name": "Farhan's Quest 3",
    "platform_name": "Meta Quest OS",
    "app_version": "1.0.2",
    "device_type": "meta_quest_3"
  }
  ```
- **Response**: Returns `device_access_token`. **Store this securely in the headset.**

## 2. Session Management

### Step 1: Start Session (Mobile-Side Priority)
The Flutter app should be the primary initiator via the Launch Readiness screen.
- **Endpoint**: `POST /api/v1/vr/sessions/start`
- **Payload**: `{"module_slug": "gmp-basics"}`

### Step 2: Sync Session (Unity)
The headset should poll or be notified to start. Unity app can call `GET /api/v1/vr/status` (if authenticated via token) or simply wait for the user to start the module.
If Unity needs to start it directly:
- **Endpoint**: `POST /api/v1/vr/headset/sessions/start`
- **Token**: Use `device_access_token`.

### Step 3: Heartbeat & Progress (Unity)
Keep the session alive and update progress.
- **Endpoint**: `PUT /api/v1/vr/headset/sessions/{id}`
- **Payload**:
  ```json
  {
    "device_access_token": "YOUR_TOKEN",
    "progress_percentage": 45,
    "session_status": "playing",
    "current_step": "Sterile Gowning Area"
  }
  ```

## 3. Telemetry & Learning Events (Unity)

Record granular actions inside the VR environment.
- **Endpoint**: `POST /api/v1/vr/headset/sessions/{id}/events`
- **Payload**:
  ```json
  {
    "device_access_token": "YOUR_TOKEN",
    "event_type": "checkpoint_reached",
    "event_timestamp": "2026-03-11T15:15:00Z",
    "event_payload": {
      "checkpoint_id": "gate_1",
      "completion_time": 120,
      "errors_made": 0
    }
  }
  ```

### Recommended Event Types:
- `checkpoint_reached`
- `stage_started` / `stage_completed`
- `object_interacted` (e.g., "vial_picked_up")
- `quiz_submitted` (with answer details in payload)

## 4. Error Handling
- **401 Unauthorized**: Device token invalid. Force re-pairing.
- **422 Unprocessable**: Invalid code or session mismatch.
- **429 Too Many Requests**: Pairing rate limit reached (5 attempts/min).

---
*PharmVR Backend Team - Phase 4 Completion*
