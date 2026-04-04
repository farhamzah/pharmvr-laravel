# Phase 4 VR Seeder Strategy

This document outlines the strategy for seeding realistic VR data to support Flutter and Unity development for PharmVR Phase 4.

## 1. Seeder Plan

The goal is to provide a "dev-ready" database environment where multiple users exist in different stages of the VR lifecycle.

### Core Users to Seed
| User Persona | Key Data State | Purpose |
| :--- | :--- | :--- |
| `vr_user_new` | No pairings or devices. | Test first-time pairing UI. |
| `vr_user_pending` | Active [VrPairing](file:///e:/Flutter/pharmvrpro/backend/app/Models/VrPairing.php#8-52) (code: `123456`). | Test "Enter Pairing Code" flow on Unity. |
| `vr_user_ready` | [VrDevice](file:///e:/Flutter/pharmvrpro/backend/app/Models/VrDevice.php#8-44) (Quest 3) paired & active. | Test Launch VR / Module entry. |
| `vr_user_playing` | Active [VrSession](file:///e:/Flutter/pharmvrpro/backend/app/Models/VrSession.php#8-70) in `playing` state. | Test Home Status / Progress bar sync. |
| `vr_user_done` | Recent `completed` session. | Test Summary screen and post-test logic. |
| `vr_user_interrupted` | `interrupted` session (reason: `ACCEL_WAKE`). | Test reconnect prompts or resume logic. |

---

## 2. Factory Recommendations

### A. VrDeviceFactory
- `device_type`: `meta_quest_3` (90%), `pico_4` (10%)
- [status](file:///e:/Flutter/pharmvrpro/backend/app/Http/Controllers/Api/V1/Vr/VrStatusController.php#17-82): `active`, `inactive`
- `last_seen_at`: `now()` for connected users, `3 days ago` for disconnected.
- `app_version`: `1.0.5`

### B. VrPairingFactory
- [status](file:///e:/Flutter/pharmvrpro/backend/app/Http/Controllers/Api/V1/Vr/VrStatusController.php#17-82): [pending](file:///e:/Flutter/pharmvrpro/backend/tests/Feature/Api/V1/Vr/VrPhase4Test.php#76-92), `confirmed`, `expired`
- `expires_at`: `now()->addMinutes(10)`
- `pairing_code_hash`: `Hash::make('123456')`

### C. VrSessionFactory
- [session_status](file:///e:/Flutter/pharmvrpro/backend/tests/Feature/Api/V1/Vr/VrPhase4Test.php#631-672): `starting`, `playing`, `completed`, `interrupted`
- `progress_percentage`: `rand(0, 100)`
- `current_step`: Real step IDs like `sterile_area_gate`, `gowning_station`.
- `summary_json`: `{ "score": 85, "errors": 2, "time_taken": 340 }`

---

## 3. Sample State Distribution (Standard Seed)

When running `php artisan db:seed --class=VrSeeder`, the following should be created:

1.  **5 Active Devices**: Spread across different users.
2.  **3 Active Sessions**: 2 `playing` at 45% and 80%, 1 `starting`.
3.  **10 Historical Sessions**: 7 `completed`, 3 `interrupted`.
4.  **2 Pending Pairings**: For testing the manual code entry flow.

---

## 4. Frontend Testing Usefulness

| Feature | Seeded Advantage |
| :--- | :--- |
| **Home Screen** | Instantly shows "Lanjutkan Sesi" if a `playing` session is seeded. |
| **Launch VR** | Shows "Quest 3 Connected" badge if `last_seen_at` is fresh. |
| **Session Status** | Real-time progress percentage binding. |
| **Completion Summary** | Validates that summary JSON fields (score/errors) bind correctly to the Flutter card. |

---
*PharmVR Backend Team - Phase 4 Seeder Strategy*
