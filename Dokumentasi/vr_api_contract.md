# PharmVR Phase 4: VR API Contract (Quest-3-First)

This document provides the unified technical specification for all Phase 4 VR APIs, covering the synchronization between the Flutter mobile app and the Meta Quest 3 Unity app.

## 1. Shared Conventions

- **Base URL**: `/api/v1`
- **Response Format**: 
  ```json
  {
    "success": true,
    "message": "...",
    "data": { ... }
  }
  ```
- **Error Format**: Standard HTTP Status Codes (401, 403, 422, 429) with descriptive error messages.

---

## 2. Mobile APIs (Flutter)
**Authentication**: `Authorization: Bearer <Sanctum_Token>`

### A. Pairing Flow
| Endpoint | Method | Purpose |
| :--- | :--- | :--- |
| `/vr/pairings/start` | `POST` | Generates a 6-digit pairing code. |
| `/vr/pairings/current` | `GET` | Checks if the user has an active/pending pairing. |
| `/vr/pairings/{id}/cancel` | `POST` | Revokes a pairing or deactivates a device. |

### B. Status & Readiness
| Endpoint | Method | Purpose |
| :--- | :--- | :--- |
| `/home` | `GET` | Returns VR-synced status header and next-action hints. |
| `/vr/status` | `GET` | Detailed connection and headset status. |
| `/vr/modules/{slug}/launch-readiness` | `GET` | Full checklist (Pre-test, Connection, Battery). |

### C. Session Management
| Endpoint | Method | Purpose |
| :--- | :--- | :--- |
| `/vr/sessions/start` | `POST` | Initiates a VR session for a specific module. |
| `/vr/sessions/current` | `GET` | Returns active or most recently finished session. |
| `/vr/sessions/{session}` | `GET` | Full telemetry and results for a specific session ID. |

---

## 3. Headset APIs (Unity)
**Authentication**: `device_access_token` inside the JSON payload.

### A. Identity & Heartbeat
| Endpoint | Method | Payload | Purpose |
| :--- | :--- | :--- | :--- |
| `/vr/headset/pair` | `POST` | `pairing_code`, `headset_identifier`, `device_name` | Consumes code to get `device_access_token`. |
| `/vr/headset/heartbeat` | `POST` | `device_access_token` | Updates `last_seen_at` (every 60s). |

### B. Session Control
| Endpoint | Method | Payload | Purpose |
| :--- | :--- | :--- | :--- |
| `/vr/headset/sessions/{id}/progress` | `PUT` | `current_step`, `progress_percentage`, [status](file:///e:/Flutter/pharmvrpro/backend/app/Http/Controllers/Api/V1/Vr/VrStatusController.php#17-82) | Updates training telemetry. |
| `/vr/headset/sessions/{id}/complete` | `POST` | `final_progress`, `completion_summary` | Marks session as finished. |
| `/vr/headset/sessions/{id}/interrupt` | `POST` | `reason`, `error_code`, `reconnect` | Logs unexpected breaks. |

### C. Learning Events (Multiplexer)
- **Endpoint**: `POST /vr/headset/sessions/{id}/unified-events`
- **Payload**: See [Learning Event Contract](file:///C:/Users/farha/.gemini/antigravity/brain/61f2e3ea-38c1-4c88-bb1d-a7fd5b161c0e/learning_event_contract.md).
- **Purpose**: Single endpoint for telemetry, quizzes, stage results, and AI hint logs.

---

## 4. Key Request/Response Examples

### Mobile: Launch Readiness (`GET .../launch-readiness`)
```json
{
  "success": true,
  "data": {
    "eligible_to_launch": false,
    "checklist": [
      { "label": "Lulus Pre-test", "status": true },
      { "label": "Meta Quest 3 Terhubung", "status": false }
    ],
    "recommended_next_action": "Hubungkan Quest 3",
    "recommended_next_route": "vr_connect"
  }
}
```

### Headset: Pair Device (`POST /vr/headset/pair`)
```json
{
  "success": true,
  "data": {
    "device_access_token": "q3_7f28...z9x",
    "device_name": "Farhan's Quest 3"
  }
}
```

---

## 5. Security & Ownership Model

1. **Strict Binding**: Once a session is started on mobile, only the **authenticated device** bound to that user can update it.
2. **State Enforcement**: If a session is `completed` or `interrupted`, any subsequent telemetry to that session ID will be rejected with `403 Forbidden`.
3. **Sensitive Fields**: Passwords/Codes are never returned in plain text. Pairing codes are hashed on the server.

---
*PharmVR Backend Engineering - Quest-3-First Strategy (Phase 4)*
