# PharmVR Premium UX States Guide

A comprehensive guide for implementing loading, empty, and error states across the PharmVR frontend. These states have been consolidated into three core, reusable widgets under `lib/core/widgets/states/`.

## 1. Reusable State Widgets

We transitioned from generic placeholders to scenario-specific named constructors that bundle the correct icon, color glow, and microcopy.

### PharmEmptyState
Used when data is missing or a list is empty. Features a gentle background glow and an outline CTA button.
* `PharmEmptyState.noData()`: Generic fallback.
* `PharmEmptyState.noConversation(onStartChat: ...)`: For the AI assistant when history is blank.
* `PharmEmptyState.noNews()`: When the education hub / news feed is empty.
* `PharmEmptyState.noTraining(onExplore: ...)`: When a user has 0 modules on their dashboard.
* `PharmEmptyState.noAssessments(onGoToTraining: ...)`: When the tests tab is empty.

### PharmErrorState
Used when a failure prevents content from loading. Features a tinted error box and a solid "Retry" button.
* `PharmErrorState.generic(onRetry: ...)`: For unexpected app/logic errors.
* `PharmErrorState.network(onRetry: ...)`: For backend timeouts or 500s.
* `PharmErrorState.offline(onRetry: ...)`: When the device has explicitly lost connection (replaces `pharm_offline_state.dart`).

### PharmLoadingState
Used for full-screen loading sequences. Features an animated spinner over a glowing core. (For inline buttons or small card areas, use the existing `PharmLoadingIndicator`).
* `PharmLoadingState(title: ..., subtitle: ...)`: Custom loading
* `PharmLoadingState.vrConnecting()`: establishing local socket with headset.
* `PharmLoadingState.assessment()`: calculating complex assessment scores.
* `PharmLoadingState.aiThinking()`: waiting for AI stream/response.

---

## 2. Design Rules

* **Never Use Default Placeholders:** Avoid plain text "No items" or generic grey `CircularProgressIndicator` centered on a black screen.
* **Glow & Shadow (The PharmVR Look):** State widgets must feel physical. Empty states use a 100x100 `BoxShape.circle` with a 30px `blurRadius` glow. Error states use a `borderRadius: 24` box with a 20% opacity border to feel "alert" but not "broken".
* **Color Exclusivity:**
  * Cyan/Teal (`PharmColors.primary`): Training, VR, neutral loading
  * Neon Green (`PharmColors.success`): Completions, success states
  * Amber/Orange (`PharmColors.warning`): Assessments, network issues, pending
  * Coral/Red (`PharmColors.error`): Hard failures, generic errors
  * Cool Blue (`PharmColors.info`): AI Assistant, general news
* **Hierarchy:** Every state must have an Icon -> `h3/h4` Heading -> `bodyMedium` Context -> (Optional) Action.

---

## 3. Copywriting Tone Suggestions

* **Calm & Professional:** The tone should be similar to an expert trainer. Never panic the user.
* **Error States:** Use "We couldn't connect..." instead of "Failed to connect to server." Blame the connection or the app, not the user.
* **Empty States:** Frame empty spaces as *opportunities*, not dead ends. Provide a CTA whenever possible.
  * *Bad:* No VR training started.
  * *Premium:* Your Journey Starts Here. You haven't started any VR training modules yet.
* **Actionable CTAs:** Use verbs. "Retry Connection", "Explore Modules", "Start New Chat" instead of just "Retry" or "OK".

---

## 4. Screen Usage Recommendations

| Screen | Target State Widget | Placement / Context |
| :--- | :--- | :--- |
| **VR Launch** | `PharmLoadingState.vrConnecting()` | Full screen swap when user clicks "Launch VR" and we await the headset handshake. |
| **Dashboard** | `PharmEmptyState.noTraining()` | Rendered inside the "Your Progress" section if `totalModules == 0`. Keeps the hero header intact. |
| **AI Assistant** | `PharmEmptyState.noConversation()`| Rendered in the center of the screen when `chatHistory.isEmpty`. |
| **Assessments** | `PharmLoadingState.assessment()` | Shown exclusively between clicking "Submit Assessment" on the Review screen and pushing the Result screen. |
| **News / Edu** | `PharmErrorState.network()` | Rendered full screen if the API call to fetch industry news times out. |
