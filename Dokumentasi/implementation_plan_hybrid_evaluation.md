# Implementation Plan - Hybrid Evaluation System (Rules + AI)

Build the deterministic core for evaluating VR actions and connect it to the AI mentor for natural language explanations.

## User Review Required

> [!IMPORTANT]
> - **Deterministic Rules**: We will implement a `VrRuleService` that provides the absolute source of truth for "Correct vs Incorrect" actions. AI will never decide the score in this phase.
> - **Event Mapping**: We need to define a few initial rule mappings (e.g., `sterile_breach`, `wrong_equipment`, `quiz_answer`).

## Proposed Changes

### [Component] Evaluation Engine
#### [NEW] `app/Services/VrRuleService.php`
- Logic to evaluate common VR event types.
- Method: [evaluate(string $eventType, array $payload): array](file:///e:/Flutter/pharmvrpro/backend/app/Services/Analytics/AchievementService.php#12-42)
- Returns: `is_correct`, `rule_id`, `factual_description`, `severity`.

### [Component] AI Integration
#### [MODIFY] [VrAiGuideService.php](file:///e:/Flutter/pharmvrpro/backend/app/Services/Ai/VrAiGuideService.php)
- Integrate `VrRuleService` into [generateFeedback](file:///e:/Flutter/pharmvrpro/backend/app/Services/Ai/VrAiGuideService.php#81-110).
- Pass rule-derived facts into the AI prompt builder.
#### [MODIFY] [AiPromptBuilder.php](file:///e:/Flutter/pharmvrpro/backend/app/Services/Ai/AiPromptBuilder.php)
- Add support for "Ground Truth Facts" in VR context building.

### [Component] API & Controllers
#### [MODIFY] [VrAiController.php](file:///e:/Flutter/pharmvrpro/backend/app/Http/Controllers/Api/V1/Vr/VrAiController.php)
- Ensure the controller passes the necessary raw event data for the Rule Engine to process.

## Verification Plan

### Automated Tests
- `HybridEvaluationTest`: Verify that if `VrRuleService` says "Incorrect", the AI response also explains it as "Incorrect" (checking for keyword alignment).
- Verify that critical breaches trigger the correct severity in the API response.

### Manual Verification
- Execute a "sterile breach" event via Tinker and check that the AI summarizes the specific GMP rule linked to that breach.
