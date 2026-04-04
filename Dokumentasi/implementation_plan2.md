# Pre-Test & Post-Test Assessment UX

Build a focused, VR-connected assessment experience for PharmVR. Assessments are not standalone — they are gates that bookend the VR simulation lifecycle.

## Proposed Changes

### Assessment Domain Models

#### [NEW] [assessment_models.dart](file:///e:/Flutter/pharmvrpro/lib/features/assessment/domain/models/assessment_models.dart)

Data models for the assessment system:
- `Assessment` — metadata (id, moduleId, type [pre/post], title, totalQuestions, timeLimit, attemptNumber)
- `Question` — question text, options list, correctAnswer index, explanation
- `AssessmentResult` — score, timeTaken, answers map, passed flag, xpEarned

---

### Assessment State Management

#### [NEW] [assessment_provider.dart](file:///e:/Flutter/pharmvrpro/lib/features/assessment/presentation/providers/assessment_provider.dart)

Riverpod [Notifier](file:///e:/Flutter/pharmvrpro/lib/features/auth/presentation/providers/auth_provider.dart#28-80) managing:
- `AssessmentState` — current question index, selected answers map, timer value, submission status, result
- Methods: `selectAnswer()`, `nextQuestion()`, `previousQuestion()`, `submitAssessment()`
- Mock data source with 5 cleanroom GMP questions

---

### Assessment Screens (5 screens)

#### [NEW] [assessment_intro_screen.dart](file:///e:/Flutter/pharmvrpro/lib/features/assessment/presentation/screens/assessment_intro_screen.dart)

Briefing screen before starting. Shows:
- Module context badge (linked to VR module)
- Assessment type (Pre-Test / Post-Test)
- Question count, time limit, attempt info placeholders
- "Begin Assessment" CTA

#### [NEW] [assessment_question_screen.dart](file:///e:/Flutter/pharmvrpro/lib/features/assessment/presentation/screens/assessment_question_screen.dart)

Core question-answering interface:
- Top: timer placeholder + progress bar (e.g., "3 of 5")
- Center: question text + multiple-choice option cards (using [PharmGlassCard](file:///e:/Flutter/pharmvrpro/lib/core/widgets/pharm_glass_card.dart#5-37))
- Bottom: Previous / Next navigation, Submit on last question
- Clean, calm, minimal distractions

#### [NEW] [assessment_review_screen.dart](file:///e:/Flutter/pharmvrpro/lib/features/assessment/presentation/screens/assessment_review_screen.dart)

Submission confirmation before finalizing:
- Summary of answered/unanswered questions
- Option to go back and review
- "Submit Assessment" CTA

#### [NEW] [assessment_result_screen.dart](file:///e:/Flutter/pharmvrpro/lib/features/assessment/presentation/screens/assessment_result_screen.dart)

Score and feedback display:
- Circular score indicator with pass/fail visual
- Time taken, XP earned, correct/incorrect breakdown
- CTA: "Launch VR Simulation" (pre-test) or "View Summary" (post-test)
- VR module context always visible

#### [NEW] [assessment_option_card.dart](file:///e:/Flutter/pharmvrpro/lib/features/assessment/presentation/widgets/assessment_option_card.dart)

Reusable answer option widget with selected/unselected/correct/incorrect states.

---

### Routing Updates

#### [MODIFY] [app_router.dart](file:///e:/Flutter/pharmvrpro/lib/core/router/app_router.dart)

Replace current `/vr/pre-test` and `/vr/post-test` routes with new assessment routes:
- `/assessment/intro/:moduleId/:type` — intro screen (type = pre|post)
- `/assessment/question/:moduleId/:type` — question screen
- `/assessment/review/:moduleId/:type` — review screen
- `/assessment/result/:moduleId/:type` — result screen

Update Dashboard and VrPostTestScreen CTAs to use new routes.

---

### Legacy Cleanup

#### [DELETE] [vr_pre_test_screen.dart](file:///e:/Flutter/pharmvrpro/lib/features/vr_experience/presentation/screens/vr_pre_test_screen.dart)
#### [DELETE] [vr_post_test_screen.dart](file:///e:/Flutter/pharmvrpro/lib/features/vr_experience/presentation/screens/vr_post_test_screen.dart)

Replaced by the new assessment feature module.

---

## Verification Plan

### Automated
- `dart analyze` on all new files — zero errors
- `flutter run` — verify navigation from Dashboard → Assessment Intro → Questions → Review → Result → VR Launch

### Manual
- Confirm timer placeholder and attempt info are visible
- Confirm VR module context badge on every assessment screen
- Confirm progress bar updates correctly across questions
