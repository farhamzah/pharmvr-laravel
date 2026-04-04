# Implementation Plan - AI Logging & Usage Tracking

Standardize how we track AI costs, latency, and effectiveness across PharmVR.

## User Review Required

> [!NOTE]
> - **Centralized Logging**: We are introducing an `ai_usage_logs` table. This decouples the "Interaction history" (which the user sees) from "Usage Telemetry" (which admins see).
> - **Source Mapping**: The log will link back to the primary record (`pharmai_messages` or `vr_ai_interactions`).

## Proposed Changes

### [Component] Database
#### [NEW] `2026_03_12_045000_create_ai_usage_logs_table.php`
- Fields: `user_id`, `interaction_type`, `source_type`, `source_id`, `provider_name`, `model_name`, `latency_ms`, `tokens`, `domain_mode`, `is_safe`, `metadata`.

### [Component] Models
#### [NEW] `app/Models/AiUsageLog.php`
- Standard Eloquent model with casts for JSON metadata.

### [Component] Services
#### [MODIFY] [AiChatService.php](file:///e:/Flutter/pharmvrpro/backend/app/Services/Ai/AiChatService.php)
- Add a `logUsage` private method.
- Update [sendMessage](file:///e:/Flutter/pharmvrpro/backend/app/Http/Controllers/Api/V1/Ai/PharmaiChatController.php#65-80) to trigger logging upon response.

#### [MODIFY] [VrAiGuideService.php](file:///e:/Flutter/pharmvrpro/backend/app/Services/Ai/VrAiGuideService.php)
- Inject usage logging into [generateHint](file:///e:/Flutter/pharmvrpro/backend/app/Http/Controllers/Api/V1/Vr/VrAiController.php#22-48), [generateReminder](file:///e:/Flutter/pharmvrpro/backend/app/Http/Controllers/Api/V1/Vr/VrAiController.php#49-71), and [generateFeedback](file:///e:/Flutter/pharmvrpro/backend/app/Http/Controllers/Api/V1/Vr/VrAiController.php#72-96).

## Verification Plan

### Automated Tests
- `AiUsageTrackingTest`: Verify that sending an AI message creates an entry in `ai_usage_logs` with correct latency and provider data.
- Verify that VR interactions also populate the same table.

### Manual Verification
- Run a test session and query the `ai_usage_logs` table via Tinker to confirm data richness.
