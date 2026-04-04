# PharmVR Frontend Architecture Review

Pre-backend-integration audit across clean architecture, state management, routing, validation, UX polish, and scalability.

---

## Project Snapshot

| Metric | Value |
|---|---|
| Feature modules | 8 (`auth`, `dashboard`, `education`, `news`, `ai_assistant`, `profile`, `vr_experience`, `assessment`) |
| Core modules | 5 (`network`, `router`, `theme`, `utils`, `widgets`) |
| Total Dart files | 49 feature + ~22 core = **~71 files** |
| State management | Riverpod ([Notifier](file:///e:/Flutter/pharmvrpro/lib/features/auth/presentation/providers/auth_provider.dart#28-109), `AsyncNotifier`, `StateNotifier`) |
| Routing | GoRouter + `StatefulShellRoute` |
| HTTP client | Dio ([DioClient](file:///e:/Flutter/pharmvrpro/lib/core/network/dio_client.dart#3-36)) — placeholder only |
| Design tokens | [PharmColors](file:///e:/Flutter/pharmvrpro/lib/core/theme/pharm_colors.dart#3-30) (14), [PharmTextStyles](file:///e:/Flutter/pharmvrpro/lib/core/theme/pharm_text_styles.dart#8-90) (13), [PharmSpacing](file:///e:/Flutter/pharmvrpro/lib/core/theme/pharm_spacing.dart#3-22), [PharmRadius](file:///e:/Flutter/pharmvrpro/lib/core/theme/pharm_radius.dart#3-16) |

---

## 🔴 Critical Issues (Fix Before Backend)

### 1. No Repository / Data-Source Layer
Every provider contains mock data and business logic inline. When the Laravel backend connects, there's no interface to swap.

**Impact**: Every provider will need rewriting simultaneously.

**Fix**: Add a `data/` layer per feature:
```
feature/
  data/
    repositories/   ← abstractions
    datasources/     ← Dio implementations
  domain/models/
  presentation/
```

### 2. Legacy Files Still on Disk
[vr_pre_test_screen.dart](file:///e:/Flutter/pharmvrpro/lib/features/vr_experience/presentation/screens/vr_pre_test_screen.dart) and [vr_post_test_screen.dart](file:///e:/Flutter/pharmvrpro/lib/features/vr_experience/presentation/screens/vr_post_test_screen.dart) still exist under `vr_experience/presentation/screens/` despite being removed from the router. They are dead code and could cause confusion.

**Fix**: Delete both files.

### 3. Auth Token Not Persisted or Injected
[DioClient](file:///e:/Flutter/pharmvrpro/lib/core/network/dio_client.dart#3-36) has a JWT interceptor placeholder but no actual token injection mechanism. No `SharedPreferences` / `flutter_secure_storage` integration exists. Session management is completely absent.

**Fix**: Add `AuthInterceptor` that reads token from secure storage and injects it. Add 401 response interceptor to trigger session expiry flow.

---

## 🟡 Major Issues

### 4. Inconsistent Provider Patterns
Three different Riverpod patterns are used:
- `Notifier<T>` in [auth_provider.dart](file:///e:/Flutter/pharmvrpro/lib/features/auth/presentation/providers/auth_provider.dart), [profile_provider.dart](file:///e:/Flutter/pharmvrpro/lib/features/profile/presentation/providers/profile_provider.dart)
- `AsyncNotifier<T>` in [dashboard_provider.dart](file:///e:/Flutter/pharmvrpro/lib/features/dashboard/presentation/providers/dashboard_provider.dart)  
- `StateNotifier<Map<K,V>>` in [chat_detail_provider.dart](file:///e:/Flutter/pharmvrpro/lib/features/ai_assistant/presentation/providers/chat_detail_provider.dart)

**Recommendation**: Standardize on [Notifier](file:///e:/Flutter/pharmvrpro/lib/features/auth/presentation/providers/auth_provider.dart#28-109) (sync state) and `AsyncNotifier` (async/API state). Migrate `chat_detail_provider` away from `StateNotifier<Map>`.

### 5. Login Screen Bypasses Theme System
[login_screen.dart](file:///e:/Flutter/pharmvrpro/lib/features/auth/presentation/screens/login_screen.dart) uses inline [_buildStyledTextField()](file:///e:/Flutter/pharmvrpro/lib/features/auth/presentation/screens/login_screen.dart#230-284) with hardcoded [Color(0xFF...)](file:///e:/Flutter/pharmvrpro/lib/core/theme/pharm_colors.dart#3-30) values instead of [PharmTextField](file:///e:/Flutter/pharmvrpro/lib/core/widgets/pharm_text_field.dart#3-48) + [PharmColors](file:///e:/Flutter/pharmvrpro/lib/core/theme/pharm_colors.dart#3-30). This creates two parallel styling paths.

**Fix**: Refactor login to use [PharmTextField](file:///e:/Flutter/pharmvrpro/lib/core/widgets/pharm_text_field.dart#3-48) and [PharmColors](file:///e:/Flutter/pharmvrpro/lib/core/theme/pharm_colors.dart#3-30) tokens consistently.

### 6. No Data Model for API Responses
Models exist ([User](file:///e:/Flutter/pharmvrpro/lib/features/profile/domain/models/user.dart#1-50), [NewsArticle](file:///e:/Flutter/pharmvrpro/lib/features/news/domain/models/news_article.dart#1-75), [EdukasiItem](file:///e:/Flutter/pharmvrpro/lib/features/education/domain/models/edukasi_item.dart#3-94), [DashboardData](file:///e:/Flutter/pharmvrpro/lib/features/dashboard/domain/models/dashboard_data.dart#1-40)) but none have `fromJson` / [toJson](file:///e:/Flutter/pharmvrpro/lib/features/profile/domain/models/user.dart#29-39) serialization. Backend integration will need every model updated.

**Fix**: Add `json_serializable` or manual factory constructors to all domain models.

### 7. Assessment Provider Not Connected to Assessment Data
[AssessmentNotifier](file:///e:/Flutter/pharmvrpro/lib/features/assessment/presentation/providers/assessment_provider.dart#51-124) loads mock data directly in [loadAssessment()](file:///e:/Flutter/pharmvrpro/lib/features/assessment/presentation/providers/assessment_provider.dart#58-65). No mechanism to switch between mock and API data.

**Fix**: Define `AssessmentRepository` interface, implement `MockAssessmentRepository` and future `ApiAssessmentRepository`.

---

## 🟢 Minor Issues

### 8. [fix_with_values.dart](file:///e:/Flutter/pharmvrpro/fix_with_values.dart) Left in Project Root
Temporary utility script still present.

### 9. [SplashScreen](file:///e:/Flutter/pharmvrpro/lib/features/auth/presentation/screens/splash_screen.dart#7-13) Has Hardcoded Auth Check
Uses `const bool isAuthenticated = false` instead of reading from Riverpod. When backend auth is added, this needs to check stored JWT validity.

### 10. [PharmGlassCard](file:///e:/Flutter/pharmvrpro/lib/core/widgets/pharm_glass_card.dart#5-37) Used Inconsistently
Some screens use it for all cards, others use raw `Container` with manual decoration. Should standardize all elevated content surfaces.

### 11. `PharmPrimaryButton.onPressed` Has `VoidCallback?`
This is correctly nullable for disabled state, but some callers pass `isLoading ? null : _handler` **and** the button internally already guards with `isLoading ? null : onPressed`. The double-guard is redundant but harmless.

### 12. Route Parameters Are Unvalidated
Assessment routes accept `moduleId` and `type` from URL. No guard validates that `type` is [pre](file:///e:/Flutter/pharmvrpro/lib/features/assessment/presentation/providers/assessment_provider.dart#80-85) or `post`, or that `moduleId` matches existing data. A malformed deep-link could crash.

---

## ✅ Strengths

| Area | Assessment |
|---|---|
| Feature-first folder structure | ✅ Clean and consistent across all 8 features |
| Navigation architecture | ✅ GoRouter with `StatefulShellRoute` — proper indexed-stack for bottom nav |
| Design token system | ✅ [PharmColors](file:///e:/Flutter/pharmvrpro/lib/core/theme/pharm_colors.dart#3-30), [PharmTextStyles](file:///e:/Flutter/pharmvrpro/lib/core/theme/pharm_text_styles.dart#8-90), [PharmSpacing](file:///e:/Flutter/pharmvrpro/lib/core/theme/pharm_spacing.dart#3-22), [PharmRadius](file:///e:/Flutter/pharmvrpro/lib/core/theme/pharm_radius.dart#3-16) — comprehensive |
| Widget reuse | ✅ Strong library: [PharmPrimaryButton](file:///e:/Flutter/pharmvrpro/lib/core/widgets/pharm_primary_button.dart#4-92), [PharmGlassCard](file:///e:/Flutter/pharmvrpro/lib/core/widgets/pharm_glass_card.dart#5-37), [PharmTextField](file:///e:/Flutter/pharmvrpro/lib/core/widgets/pharm_text_field.dart#3-48), [PharmPasswordField](file:///e:/Flutter/pharmvrpro/lib/core/widgets/pharm_password_field.dart#3-20), 4 scaffold variants, 3 state widgets |
| Validation | ✅ Centralized [ValidatorBuilder](file:///e:/Flutter/pharmvrpro/lib/core/utils/validators.dart#3-131) with 10 validators |
| Error handling | ✅ [PharmErrorHandler](file:///e:/Flutter/pharmvrpro/lib/core/utils/error_handler.dart#7-130) with sanitized messaging and session expired dialog |
| VR-first alignment | ✅ Dashboard hero card, VR status in AppBar, assessment gates connected to VR flow |
| Assessment UX | ✅ Full lifecycle: intro → questions → review → results, parameterized for pre/post |
| AI assistant | ✅ Multi-session support, suggestion chips, typing indicator |

---

## Refactor Recommendations (Priority Order)

### Before Backend Integration
1. **Add repository layer** — Create abstract `Repository` per feature + `MockRepository` implementations
2. **Add JSON serialization** to all domain models (`fromJson`, [toJson](file:///e:/Flutter/pharmvrpro/lib/features/profile/domain/models/user.dart#29-39))
3. **Implement `AuthInterceptor`** — JWT injection + 401 session expiry handling
4. **Add `flutter_secure_storage`** — for token persistence
5. **Delete legacy VR test screens** — [vr_pre_test_screen.dart](file:///e:/Flutter/pharmvrpro/lib/features/vr_experience/presentation/screens/vr_pre_test_screen.dart), [vr_post_test_screen.dart](file:///e:/Flutter/pharmvrpro/lib/features/vr_experience/presentation/screens/vr_post_test_screen.dart)

### UX Polish Pass
6. **Refactor login screen** to use theme tokens instead of inline colors
7. **Standardize card usage** — [PharmGlassCard](file:///e:/Flutter/pharmvrpro/lib/core/widgets/pharm_glass_card.dart#5-37) for interactive, `Container(color: surfaceLight)` for static
8. **Add route guards** — Redirect to login if unauthenticated, validate route params

### Future Scalability
9. **Add `freezed` + `json_serializable`** for immutable models with serialization
10. **Add environment config** — separate `dev` / `staging` / `prod` base URLs

---

## Architecture Recommendation

```
lib/
├── core/
│   ├── network/          ← DioClient + AuthInterceptor + BaseResponse
│   ├── router/           ← GoRouter + route guards
│   ├── theme/            ← PharmColors, PharmTextStyles, PharmTheme
│   ├── utils/            ← ValidatorBuilder, PharmErrorHandler
│   └── widgets/          ← Reusable UI components
├── features/
│   └── [feature]/
│       ├── data/
│       │   ├── datasources/  ← API calls (Dio)
│       │   └── repositories/ ← Interface implementations
│       ├── domain/
│       │   ├── models/       ← Data classes
│       │   └── repositories/ ← Abstract interfaces
│       └── presentation/
│           ├── providers/    ← Riverpod state
│           ├── screens/      ← Pages
│           └── widgets/      ← Feature-specific widgets
└── main.dart
```

> [!TIP]
> The current architecture is **80% ready** for backend integration. The critical missing piece is the **repository abstraction layer** — once that's in place, swapping mock data for API calls becomes a one-file-per-feature change.
