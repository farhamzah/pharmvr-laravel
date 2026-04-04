# PharmVR Frontend Product-Readiness Audit

> Audit of all 10 flows for production-grade UX quality, backend integration readiness, and premium product feel.

---

## 1. Splash Screen

**✅ Good:** Fade+scale animation, logo with errorBuilder fallback, tagline copy.

**⚠️ Generic / Must Fix:**

| Issue | Priority | Fix |
|-------|----------|-----|
| **White background** — rest of app is dark (#0A0F14). Creates jarring flash on transition | **Critical** | Change `Colors.white` → `PharmColors.background` on line 69. Update logo/text colors to work on dark bg |
| **Loader doesn't loop** — `TweenAnimationBuilder` runs once then stops. Looks broken after 2s | **Critical** | Replace with `AnimationController..repeat()` for infinite rotation |
| **Hardcoded `isAuthenticated = false`** — no real auth check | **Major** | Wire to `ref.read(authProvider).isAuthenticated` + `SharedPreferences` token check |
| **No PharmResponsiveWrapper** applied | **Minor** | Wrap with [PharmResponsiveWrapper](file:///e:/Flutter/pharmvrpro/lib/core/widgets/pharm_responsive_wrapper.dart#7-68) |

---

## 2. Login / Register / Forgot Password

**✅ Good:** Background glow orbs, fade+slide entry animations, proper [ValidatorBuilder](file:///e:/Flutter/pharmvrpro/lib/core/utils/validators.dart#3-131) usage, `autovalidateMode` toggling, loading state on CTA, clear error display via `PharmErrorHandler`, consistent input decoration helper, `autofillHints`, `textInputAction.next` chaining, success state on forgot-password with resend+login CTAs.

**⚠️ Generic / Must Fix:**

| Issue | Priority | Fix |
|-------|----------|-----|
| **Register: no success feedback** — goes straight to dashboard | **Major** | Add brief success toast/snackbar "Account created!" before redirect |
| **Register: no Terms of Service checkbox** | **Major** | Add checkbox + link row before CTA button |
| **Register: password strength indicator** missing | **Major** | Add real-time password strength bar below password field |
| **Login: no "Remember Me" checkbox UI** — param exists in code but no toggle shown | **Major** | Add Remember Me toggle in the login form |
| **Register: not wrapped with PharmResponsiveWrapper** | **Minor** | Wrap like login screen |
| **Forgot Password: not wrapped with PharmResponsiveWrapper** | **Minor** | Wrap like login screen |

---

## 3. Dashboard

**✅ Good:** Hero section with greeting + VR status badge, progress grid with stat cards (gradient icons, colored glow), Training Journey widget, Quick Actions grid, Continue Learning horizontal list with VR icon badges, News card, AI suggestion card, `RefreshIndicator` with pull-to-refresh, proper Riverpod `AsyncValue.when()` with loading/error/data.

**⚠️ Generic / Must Fix:**

| Issue | Priority | Fix |
|-------|----------|-----|
| **Hero greeting is static** — always "Good morning" regardless of time | **Major** | Use `DateTime.now().hour` to show Pagi/Siang/Sore/Malam |
| **Quick Actions don't navigate** | **Major** | Wire VR Connect → `/vr/connect`, View Modules → `/education`, etc. |
| **News card not tappable** → doesn't navigate to news detail | **Major** | Add `onTap: () => context.push('/news')` |
| **AI card not tappable** → doesn't navigate to AI assistant | **Major** | Add `onTap: () => context.push('/ai-assistant/chat/new')` |
| **Continue Learning cards not tappable** | **Major** | Add [onTap](file:///e:/Flutter/pharmvrpro/lib/features/dashboard/presentation/screens/main_shell_screen.dart#17-23) to navigate to module detail/assessment intro |
| **All mock data hardcoded** in widgets — not from provider | **Minor** | Move mock data to [DashboardData](file:///e:/Flutter/pharmvrpro/lib/features/dashboard/domain/models/dashboard_data.dart#1-52) model (already partially done) |

---

## 4. News / News Detail

**✅ Good:** `PharmListScaffold` with empty state, news card navigation to detail (fixed), detail screen with skeleton loading, hero/body/related content split, bookmark/share actions, error state with retry.

**⚠️ Generic / Must Fix:**

| Issue | Priority | Fix |
|-------|----------|-----|
| **News detail widget imports were broken** — fixed but verify the 3 widget files exist and compile | **Major** | Already fixed import paths. Verify [news_article_hero.dart](file:///e:/Flutter/pharmvrpro/lib/features/news/presentation/widgets/news_article_hero.dart), [news_article_body.dart](file:///e:/Flutter/pharmvrpro/lib/features/news/presentation/widgets/news_article_body.dart), [news_related_content.dart](file:///e:/Flutter/pharmvrpro/lib/features/news/presentation/widgets/news_related_content.dart) compile |
| **No "pull to refresh" on news list** | **Minor** | Already via `PharmListScaffold` — verify `onRefresh` works |

---

## 5. Edukasi / Edukasi Detail

**✅ Good:** 3-tab layout (Modul/Video/Dokumen), per-type filtering, empty state per tab in Indonesian, card navigation to detail, `RefreshIndicator`, Riverpod async state.

**⚠️ Generic / Must Fix:**

| Issue | Priority | Fix |
|-------|----------|-----|
| **AppBar title "Education Center"** — should be Indonesian "Pusat Edukasi" for consistency | **Major** | Change title string |
| **Modul tab: "Mulai Belajar" button** — should navigate to Pre-Test intro for that module | **Critical** | Wire [onTap](file:///e:/Flutter/pharmvrpro/lib/features/dashboard/presentation/screens/main_shell_screen.dart#17-23) → `context.push('/assessment/intro/${module.id}/pre')` |
| **Education detail screen** — verify [education_detail_screen.dart](file:///e:/Flutter/pharmvrpro/lib/features/education/presentation/screens/education_detail_screen.dart) has proper content structure | **Major** | Review the detail screen widget structure |

---

## 6. AI Assistant / Chat

**✅ Good:** Empty state with suggestion chips, typing indicator with animated dots, chat bubbles with proper alignment, `AnimatedSwitcher` on status text, back navigation, session-based chat via Riverpod, quick chat modal via FAB.

**⚠️ Generic / Must Fix:**

| Issue | Priority | Fix |
|-------|----------|-----|
| **Chat uses `AnimatedBuilder`** — deprecated naming. Should be `AnimatedBuilder` (it still works but is a yellow flag) | **Minor** | It's actually `AnimatedBuilder` which is fine |
| **No message persistence** — chat resets on restart | **Minor** | This is expected pre-backend, just note for integration |
| **Quick Chat modal suggestion chips** — emoji prefix stripping regex may fail | **Minor** | Test and fix regex `replaceAll(RegExp(r'^[^\s]+ '), '')` |

---

## 7. Profile / Settings

**✅ Good:** Hero avatar with [Hero](file:///e:/Flutter/pharmvrpro/lib/features/dashboard/presentation/screens/dashboard_screen.dart#103-167) tag, gradient header, section groups with dividers, logout dialog with warning, version display, menu items with trailing values, icons in containers.

**⚠️ Generic / Must Fix:**

| Issue | Priority | Fix |
|-------|----------|-----|
| **Profile data is hardcoded** ("PharmVR User") | **Major** | Wire to `profileProvider` user data from auth state |
| **Settings screens** (notifications, language, appearance, help, about) — need verification | **Major** | Verify all settings screens render correctly |
| **No profile loading state** — immediately shows mock data | **Minor** | Add skeleton loader when `profileState.isLoading` |

---

## 8. Pre-Test / Post-Test

**✅ Good:** Module badge with VR icon, pre/post differentiation (colors, copy), timer with low-time warning styling, progress bar, option cards with `AnimatedContainer`, quit dialog with warning icon, previous/next navigation, review screen flow.

**⚠️ Generic / Must Fix:**

| Issue | Priority | Fix |
|-------|----------|-----|
| **Timer auto-submit** — timer counts but doesn't auto-submit when reaching 0 | **Critical** | Add `if (remaining <= 0) autoSubmit()` in timer tick handler |
| **Result screen** — need to verify it correctly differentiates pre vs post test results and routes accordingly | **Major** | Review [assessment_result_screen.dart](file:///e:/Flutter/pharmvrpro/lib/features/assessment/presentation/screens/assessment_result_screen.dart) routing logic |
| **No "time's up" modal** when timer expires | **Major** | Show dialog "Waktu Habis!" then auto-navigate to review |

---

## 9. VR Connection / Pairing

**✅ Good:** QR code generation with session token, numbered Indonesian instructions, animated QR border, "Simulate Connection" button, auto-navigate on connect, connection states.

**⚠️ Generic / Must Fix:**

| Issue | Priority | Fix |
|-------|----------|-----|
| **No module context** — QR screen doesn't show which module is being connected | **Major** | Accept `moduleId` param and display module name from provider |
| **No timeout/retry** — if scan never happens, user is stuck | **Major** | Add 5-minute timeout with "Try Again" option |

---

## 10. VR Launch / Session Status

**✅ Good:** Animated pulsing background, status-based color changes, session progress tracking, auto-navigate to post-test on completion, simulation controls.

**⚠️ Generic / Must Fix:**

| Issue | Priority | Fix |
|-------|----------|-----|
| **No "session lost" recovery** — if connection drops during VR, no reconnect flow | **Major** | Add error state with "Reconnect" button |
| **Progress updates are simulated** — expected pre-backend | **Minor** | Mark as integration-ready |

---

## Architecture / Cross-Cutting Issues

| Issue | Priority | Fix |
|-------|----------|-----|
| **Splash auth check** — needs `SharedPreferences` or secure storage token check | **Critical** | Add token persistence in `AuthProvider` |
| **No Dio interceptor** for auth token injection | **Critical** | Create `DioProvider` with auth token interceptor |
| **[AuthState](file:///e:/Flutter/pharmvrpro/lib/features/auth/presentation/providers/auth_provider.dart#3-27) has no `user` field** — login doesn't store user data | **Critical** | Add [User](file:///e:/Flutter/pharmvrpro/lib/features/ai_assistant/presentation/widgets/chat_bubble.dart#28-60) model to [AuthState](file:///e:/Flutter/pharmvrpro/lib/features/auth/presentation/providers/auth_provider.dart#3-27), populate on login |
| **No token refresh/expire handling** | **Major** | Add 401 interceptor to auto-logout |
| **Dashboard quick actions / cards not navigable** | **Major** | Wire all [onTap](file:///e:/Flutter/pharmvrpro/lib/features/dashboard/presentation/screens/main_shell_screen.dart#17-23) handlers |
| **Splash + Register + ForgotPassword not responsive-wrapped** | **Minor** | Apply [PharmResponsiveWrapper](file:///e:/Flutter/pharmvrpro/lib/core/widgets/pharm_responsive_wrapper.dart#7-68) |

---

## Summary by Priority

| Priority | Count | Key Items |
|----------|-------|-----------|
| **Critical** | 5 | Splash bg + loader, modul "Mulai Belajar" routing, timer auto-submit, auth token storage, Dio interceptor |
| **Major** | 15 | Dashboard interactivity, greeting time, registration UX, profile data, VR context, education title |
| **Minor** | 7 | Responsive wrappers, mock data cleanup, regex fix |
