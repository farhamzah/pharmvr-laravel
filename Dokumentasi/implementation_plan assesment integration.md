# Phase 3: Assessment Integration

Integrate the Assessment module with the Laravel backend. This includes fetching assessment metadata, starting attempts, loading questions, and submitting results.

## Proposed Changes

### Domain Layer (Models)
#### [MODIFY] [assessment_models.dart](file:///e:/Flutter/pharmvrpro/lib/features/assessment/domain/models/assessment_models.dart)
- Update [Assessment](file:///e:/Flutter/pharmvrpro/lib/features/assessment/domain/models/assessment_models.dart#5-62) model to match [AssessmentResource](file:///e:/Flutter/pharmvrpro/backend/app/Http/Resources/Api/V1/Assessment/AssessmentResource.php#8-80).
- Update [Question](file:///e:/Flutter/pharmvrpro/lib/features/assessment/domain/models/assessment_models.dart#64-79) model to match [QuestionResource](file:///e:/Flutter/pharmvrpro/backend/app/Http/Resources/Api/V1/Assessment/QuestionResource.php#8-36).
- Add `AssessmentOption` model.
- Update [AssessmentResult](file:///e:/Flutter/pharmvrpro/lib/features/assessment/domain/models/assessment_models.dart#81-104) to match [AttemptResource](file:///e:/Flutter/pharmvrpro/backend/app/Http/Resources/Api/V1/Assessment/AttemptResource.php#8-63).
- Add `fromJson` factories to all models.

### Data Layer (Repositories)
#### [NEW] [assessment_repository.dart](file:///e:/Flutter/pharmvrpro/lib/features/assessment/data/repositories/assessment_repository.dart)
- Implement `AssessmentRepository` handle:
    - `getAssessmentIntro(moduleSlug, type)`
    - `startAttempt(assessmentId)`
    - `getQuestions(attemptId)`
    - `submitAttempt(attemptId, Map<int, int> answers)`
    - `getResults(attemptId)`

### Presentation Layer (Providers & Screens)
#### [MODIFY] [assessment_provider.dart](file:///e:/Flutter/pharmvrpro/lib/features/assessment/presentation/providers/assessment_provider.dart)
- Update [AssessmentNotifier](file:///e:/Flutter/pharmvrpro/lib/features/assessment/presentation/providers/assessment_provider.dart#51-124) to use the repository.
- Add `assessmentIntroProvider` to fetch metadata for the Intro screen.
- Manage `attemptId` in the state.

#### [MODIFY] [assessment_intro_screen.dart](file:///e:/Flutter/pharmvrpro/lib/features/assessment/presentation/screens/assessment_intro_screen.dart)
- Use `assessmentIntroProvider` for real data fetching.
- Handle loading/error states.

#### [MODIFY] [assessment_question_screen.dart](file:///e:/Flutter/pharmvrpro/lib/features/assessment/presentation/screens/assessment_question_screen.dart)
- Ensure compatibility with real question models.

#### [MODIFY] [assessment_result_screen.dart](file:///e:/Flutter/pharmvrpro/lib/features/assessment/presentation/screens/assessment_result_screen.dart)
- Display full result summary from backend.

## Verification Plan
### Automated Tests
- Verify repository calls with mock [Dio](file:///e:/Flutter/pharmvrpro/lib/core/network/dio_client.dart#3-36).
- Check JSON parsing for all assessment-related models.
### Manual Verification
- Test full flow: Intro -> Start -> Answer -> Submit -> Result.
- Verify "can_start" logic for post-tests (blocked until VR completion).
