## 1. Directory Structure (Modular AI Design)
- `app/Services/Ai/`
  - `PharmAIService.php` (Primary interface for Controllers)
  - `AiOrchestrator.php` (Logic for role-based system prompts & context building)
- `app/Services/Ai/Providers/`
  - `AiProviderInterface.php`
  - `OpenAIProvider.php` (Future)
  - `MockAiProvider.php` (For development/testing)
- `app/Services/Ai/Agents/`
  - `AppLearningAgent.php`: Specialized in long-form pharmaceutical tutoring.
  - `VrContextAgent.php`: Specialized in concise, actionable XR hints.

## 2. Schema Design
- `pharmai_conversations`: Chat sessions for the App.
- `pharmai_messages`: Individual chat entries.
- `vr_ai_interactions`: Log of AI-generated hints/feedback in VR.

## 3. AI Orchestration Design
- **App AI (PharmAI Tab)**:
  - System Prompt: "You are a senior Pharmacist and GMP auditor..."
  - Context: Last 10 messages + User's current training progress.
  - Objective: Answer domain questions, explain concepts from `training_modules`.
- **VR AI (Guide Avatar)**:
  - System Prompt: "You are a cleanroom supervisor. Be brief, stern but helpful..."
  - Context: Current [vr_session](file:///e:/Flutter/pharmvrpro/backend/tests/Feature/Api/V1/Vr/VrPhase4Test.php#201-233) state + Last 3 VR events (e.g., "Touched non-sterile surface").
  - Objective: Return text < 30 words suitable for Voice/TTS.

## 4. Proposed Changes

### Database
#### [NEW] [2026_03_11_134258_create_pharmai_tables.php](file:///e:/Flutter/pharmvrpro/backend/database/migrations/2026_03_11_134258_create_pharmai_tables.php)
#### [NEW] [2026_03_11_134318_create_vr_ai_interactions_table.php](file:///e:/Flutter/pharmvrpro/backend/database/migrations/2026_03_11_134318_create_vr_ai_interactions_table.php)

### Services & Logic
#### [NEW] `app/Services/Ai/PharmAIService.php`
- `chat(string $message, int $conversationId)`
- `generateVrHint(int $sessionId, array $recentEvents)`

### Controllers
#### [NEW] `Api/V1/Ai/PharmaiChatController.php`
- `POST /v1/pharmai/messages` (Send message and get AI response).
#### [NEW] `Api/V1/Vr/VrAiController.php`
- `POST /v1/vr/ai/hint` (Headset triggers a hint generation).

## 5. Verification Plan
- **Automated Tests**:
  - `PharmaiChatTest`: Verify multi-turn conversation storage.
  - `VrAiHintTest`: Verify length and context-sensitivity of VR hints.
  - `DomainRestrictionTest`: Ensure the AI refuses to talk about unrelated topics (e.g., movies/politics).
