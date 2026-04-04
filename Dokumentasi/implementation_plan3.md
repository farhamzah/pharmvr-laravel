# Design System Refinement Plan

Refine PharmVR's visual identity to be premium, futuristic, and consistent across all screens while remaining readable for academic and training contexts.

## Current State Summary

| Area | Status | Issue |
|---|---|---|
| Colors | 8 tokens | Missing `warning`, `info`, `accent` variants |
| Typography | Orbitron headings + Roboto body | Orbitron overused at small sizes, no `caption`/`overline`/`bodyBold` |
| Spacing | Clean [PharmSpacing](file:///e:/Flutter/pharmvrpro/lib/core/theme/pharm_spacing.dart#3-22) class | ✅ Consistent |
| Radius | Clean [PharmRadius](file:///e:/Flutter/pharmvrpro/lib/core/theme/pharm_radius.dart#3-16) class | ✅ Consistent |
| Cards | [PharmGlassCard](file:///e:/Flutter/pharmvrpro/lib/core/widgets/pharm_glass_card.dart#5-37) only | Glass card well-designed, but no variant for solid surfaces |
| Buttons | [PharmPrimaryButton](file:///e:/Flutter/pharmvrpro/lib/core/widgets/pharm_primary_button.dart#4-92) | ✅ Good gradient + loading + icon + outlined support |
| Login Screen | Custom inline widgets | Bypasses theme entirely — hardcoded [Color(0xFF...)](file:///e:/Flutter/pharmvrpro/lib/core/theme/pharm_colors.dart#3-14) values |
| `withValues` usage | ~26 occurrences | Deprecated API — should be `withOpacity` throughout |

---

## Proposed Changes

### 1. Expanded Color Palette

#### [MODIFY] [pharm_colors.dart](file:///e:/Flutter/pharmvrpro/lib/core/theme/pharm_colors.dart)

Add semantic colors and accent variants:
- `warning` — amber for caution states
- `info` — blue for informational banners
- `primaryLight` — softer cyan for hover/surface tints
- `surfaceLight` — elevated card surfaces
- `divider` — standardized separator color
- `cardBorder` — reusable border tint

### 2. Refined Typography

#### [MODIFY] [pharm_text_styles.dart](file:///e:/Flutter/pharmvrpro/lib/core/theme/pharm_text_styles.dart)

- Switch body font from Roboto to **Inter** (cleaner, more modern, better for Gen Z aesthetic)
- Keep **Orbitron** for `h1` and `h2` headings only
- Switch `h3`, `h4`, and `button` to **Inter** (bold) for better readability at smaller sizes
- Add `caption`, `overline`, `bodyBold`, and `subtitle` styles

### 3. Theme Integration

#### [MODIFY] [pharm_theme.dart](file:///e:/Flutter/pharmvrpro/lib/core/theme/pharm_theme.dart)

- Add `focusedErrorBorder` to `inputDecorationTheme`
- Add `snackBarTheme` for consistent floating rounded snackbars
- Add `dialogTheme` with dark glass background

### 4. Codebase-wide `withValues` → `withOpacity` fix

Fix all ~26 remaining `withValues` calls across the codebase to use `withOpacity`.

---

## Design Rules (What to Emphasize vs Reduce)

### ✅ Emphasize
- **Gradient CTAs**: Primary buttons keep the cyan → teal gradient glow
- **Glass cards**: Use [PharmGlassCard](file:///e:/Flutter/pharmvrpro/lib/core/widgets/pharm_glass_card.dart#5-37) for elevated, interactive content
- **Primary cyan accents**: Badge pills, progress bars, interactive highlights
- **Subtle motion**: Keep scale/fade transitions for screen entries
- **White text on dark**: High contrast for core content readability

### ❌ Reduce
- **Orbitron at small sizes**: Only use for hero titles (`h1`, `h2`), not for every section heading
- **Hardcoded inline colors**: Replace all [Color(0xFF...)](file:///e:/Flutter/pharmvrpro/lib/core/theme/pharm_colors.dart#3-14) with [PharmColors](file:///e:/Flutter/pharmvrpro/lib/core/theme/pharm_colors.dart#3-14) tokens
- **Raw `Colors.grey[600]`**: Replace with `PharmColors.textSecondary`
- **Excessive glow**: Reserve `accentGlow` shadow for primary CTAs only, not every card

---

## Verification Plan

### Automated
- `dart analyze` — zero errors after all changes
- Search for remaining `withValues` — zero occurrences

### Manual
- Visual review of splash, login, dashboard, and profile screens for consistency
