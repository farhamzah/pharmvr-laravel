# Phase 5: AI Testing Strategy

This strategy ensures the reliability, safety, and performance of the PharmAI and VR Guide components.

## 1. Test Categories

-   **Feature (Integration) Tests**: Verify end-to-end API flows from request to DB persistence.
-   **Unit Tests**: Verify isolated logic in [VrRuleService](file:///e:/Flutter/pharmvrpro/backend/app/Services/VrRuleService.php#5-96), [AiPromptBuilder](file:///e:/Flutter/pharmvrpro/backend/app/Services/Ai/AiPromptBuilder.php#5-91), and [MockAiProvider](file:///e:/Flutter/pharmvrpro/backend/app/Services/Ai/Providers/MockAiProvider.php#7-38).
-   **Contract Tests**: Ensure JSON structures remain stable for Flutter/Unity consumption.
-   **Security Tests**: Verify multi-tenant isolation and domain guardrails.

---

## 2. Must-Test Flows

| Flow | Description | Expectation |
| :--- | :--- | :--- |
| **Auth Access** | Guest attempts to list or create conversations. | Returns `401 Unauthorized`. |
| **Ownership** | User A attempts to read User B's conversation. | Returns `403 Forbidden`. |
| **History Logic** | Sending multiple messages in one conversation. | Database contains correct user/assistant sequence. |
| **VR Feedback** | Triggering AI feedback for a [sterile_breach](file:///e:/Flutter/pharmvrpro/backend/tests/Unit/VrRuleSanityTest.php#10-20). | Response contains `warning` severity and grounded explanation. |
| **Telemetry Sync** | Every AI interaction (App or VR). | Corresponding entry exists in `ai_usage_logs` with valid token counts. |

---

## 3. Edge Cases

-   **Empty Input**: Sending a `null` or empty string message (Assert: `422 Unprocessable Content`).
-   **Malformed VR Event**: Sending a `recent_events` array with missing required keys.
-   **Provider Timeout**: Simulating a slow AI response (Assert: Graceful error or handled latency logging).
-   **Non-Pharma Queries**: Asking about politics, movies, or general programming. (Assert: Response triggers "Maaf, saya hanya dapat membantu..." guardrail).

---

## 4. Security Checklist

- [ ] **Instruction Injection**: Test if user input can override system prompts (e.g., "Ignore previous instructions").
- [ ] **PII Leaks**: Ensure prompt builder strips sensitive user data (like passwords/emails) before sending to LLM.
- [ ] **Rate Limiting**: Verify that VR AI endpoints are limited to 60 requests/min to prevent cost spikes from buggy Unity loops.
- [ ] **Secret Scrubbing**: Confirm that no API keys or internal logic paths are stored in `ai_usage_logs`.

---

## 5. Done Criteria (Phase 5)

Phase 5 is considered **COMPLETE** when:
1.  All API endpoints defined in the App and VR contracts return `success: true` for valid inputs.
2.  `DbSeeders` successfully populate a realistic pharma-themed environment.
3.  [PharmAiTest](file:///e:/Flutter/pharmvrpro/backend/tests/Feature/Api/V1/Ai/PharmAiTest.php#14-189) and `VrAiHintTest` suites pass with 0 failures on local/CI.
4.  Every AI interaction is successfully logged to `ai_usage_logs` with >90% token/latency capture accuracy.
5.  Instruction guardrails successfully block at least 3 distinct "Off-topic" categories.

---
*PharmVR Backend Team - Phase 5 AI Testing Strategy*
