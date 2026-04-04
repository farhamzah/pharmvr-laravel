# Database Schema Design: PharmVR Phase 4

This design optimizes for the **Meta Quest 3** standalone headset experience while providing a structured path for future smartphone VR integration.

## 1. Table Entities

### A. `vr_devices`
Represents physical hardware (Headset or Smartphone).
- **ID**: `bigint` (PK)
- **user_id**: `bigint` (FK, nullable) – Links device to an authenticated user.
- **device_type**: `enum` ('meta_quest_3', 'smartphone_vr', 'other') - Default: 'meta_quest_3'.
- **device_name**: `string` – User-facing name (e.g., "Lab Headset A").
- **headset_identifier**: `string` (Unique) – Hardware Serial or Android System ID.
- **platform_name**: `string` – e.g., "Android", "Meta Quest OS".
- **app_version**: `string` – Tracks the version of the Unity app installed.
- **device_token_hash**: `string` (Unique, index) – Hashed bearer token for API access.
- **status**: `enum` ('active', 'inactive', 'unlinked').
- **last_seen_at**: `timestamp` – Heartbeat tracker.
- **current_pairing_id**: `bigint` (FK, nullable) – Circular reference to active pairing session.

### B. `vr_pairings`
Manages the short-lived link between Mobile and VR.
- **ID**: `bigint` (PK)
- **user_id**: `bigint` (FK) – The mobile user requesting the link.
- **device_id**: `bigint` (FK, nullable) – Becomes required once confirmed.
- **pairing_code**: `string` (Index) – The 6-digit visual code (stored hashed in prod).
- **pairing_token**: `string` (UUID, Unique) – UUID for QR or background sync.
- **status**: `enum` ('pending', 'confirmed', 'expired', 'cancelled', 'failed').
- **expires_at**: `timestamp` – Expiry (default: 10 mins).
- **confirmed_at**: `timestamp` (Nullable).
- **cancelled_at**: `timestamp` (Nullable).
- **failed_at**: `timestamp` (Nullable).
- **requested_module_id**: `bigint` (FK, nullable) – Prefills the VR app with a specific training.

### C. `vr_sessions`
Tracks individual training engagements in XR.
- **ID**: `bigint` (PK)
- **user_id**: `bigint` (FK).
- **device_id**: `bigint` (FK).
- **training_module_id**: `bigint` (FK).
- **pairing_id**: `bigint` (FK, nullable).
- **session_status**: `enum` ('starting', 'playing', 'completed', 'interrupted', 'failed').
- **started_at**: `timestamp`.
- **last_activity_at**: `timestamp` – Heartbeat for the specific session.
- **completed_at**: `timestamp` (Nullable).
- **interrupted_at**: `timestamp` (Nullable).
- **current_step**: `string` (Nullable) – Logical checkpoint in Unity.
- **progress_percentage**: `integer` (0-100).
- **summary_json**: [json](file:///e:/Flutter/pharmvrpro/backend/package.json) (Nullable) – Post-session analytics placeholder.

### D. `vr_session_events` (Optional/Future)
Granular tracking of user actions in VR.
- **session_id**: `bigint` (FK).
- **event_type**: `string`.
- **event_data**: [json](file:///e:/Flutter/pharmvrpro/backend/package.json).
- **timestamp**: `timestamp`.

---

## 2. Relationships

| Entity | Relation | Target | Description |
| :--- | :--- | :--- | :--- |
| [User](file:///e:/Flutter/pharmvrpro/backend/app/Models/User.php#16-80) | 1:M | `vr_devices` | A user can own multiple headsets. |
| [User](file:///e:/Flutter/pharmvrpro/backend/app/Models/User.php#16-80) | 1:M | `vr_pairings` | A user can initiate many pairing attempts. |
| `vr_devices` | 1:M | `vr_sessions` | A device can record many training sessions. |
| [TrainingModule](file:///e:/Flutter/pharmvrpro/backend/app/Models/TrainingModule.php#9-35) | 1:M | `vr_sessions` | Tracks sessions per pharmaceutical module. |

---

## 3. Migration Order
1. Users/TrainingModules (Phase 1-3)
2. `vr_devices`
3. `vr_pairings` (Requires devices for confirmation)
4. `vr_sessions` (Requires user, device, module)

---

## 4. Indexing Recommendations
- `vr_devices`: `headset_identifier`, `device_token_hash`.
- `vr_pairings`: [pairing_code](file:///e:/Flutter/pharmvrpro/backend/tests/Feature/Api/V1/Vr/VrPhase4Test.php#32-53), `pairing_token`, [status](file:///e:/Flutter/pharmvrpro/backend/app/Http/Controllers/Api/V1/Vr/VrStatusController.php#17-51).
- `vr_sessions`: `session_status`, `user_id`.

---

## 5. Why this Schema is "Quest-3-First" but "Future-Ready"
1. **Device Type Enum**: Optimized for Quest 3 now, but allows easy addition of 'smartphone_vr' or even 'apple_vision_pro' later without schema migration.
2. **Flexible Identity**: `headset_identifier` is generic enough to store any unique hardware ID.
3. **Audit-Ready Pairings**: Using specific timestamps (`confirmed_at`, `failed_at`) instead of just a status string allows for deep analytics on pairing friction.
4. **JSON Summary**: The `summary_json` in `vr_sessions` allows Unity developers to send ad-hoc telemetry without waiting for backend schema updates.
