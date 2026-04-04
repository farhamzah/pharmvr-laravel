# Goal Description
Build the frontend architecture for PharmVR, a cross-platform Flutter application tailored for VR learning and pharmaceutical training targeting Gen Z and Gen Alpha. The app will feature a premium, dark, futuristic visual style with teal/cyan highlights and clear user flows. The architecture uses a feature-first approach with Riverpod, GoRouter, and Dio to ensure readiness for a future Laravel backend integration.

## Proposed Changes
We will bootstrap and organize the `lib/` directory into a clean, feature-first structure into the workspace `e:\Flutter\pharmvrpro`.

### Project Foundation
- **[NEW] `lib/core/`**: Core utilities, app-wide constants, design system, routing, network layer, and generic error handling.
- **[NEW] `lib/features/`**: Individual feature modules encompassing data, domain, and presentation layers.

### Folder Structure
```text
lib/
├── core/
│   ├── constants/             # App-wide constants
│   ├── error/                 # Failure models, exceptions
│   ├── network/               # Dio client, interceptors
│   ├── router/                # GoRouter configuration
│   ├── theme/                 # Design system (Colors, Typography)
│   ├── utils/                 # Helpers, ValidatorBuilder
│   └── widgets/               # Reusable global widgets
├── features/
│   ├── auth/                  # Login, Register, Forgot Password
│   ├── dashboard/             # Home / Dashboard
│   ├── news/                  # News Feed
│   ├── education/             # Learning Content List/Details
│   ├── ai_assistant/          # AI Chat tailored for GMP/CPOB
│   ├── profile/               # User Profile & Stats
│   └── vr_experience/         # Pre-test, Post-test, VR Launch, Progress
└── main.dart                  # Entry point
```

### Design System Structure
- **Theme Mode**: Enforced Dark Theme.
- **Colors**:
  - `primary`: Teal/Cyan (e.g., `#00E5FF`)
  - `background`: Deep Dark Gray/Black (e.g., `#0A0F14`)
  - `surface`: Slightly lighter, glassmorphic dark grayish-blue panels (e.g., `#151E27`)
  - `error`: Subdued red, `success`: Neon green
- **Typography**: Modern typography (e.g., `Orbitron` or `Inter` for headings, `Roboto` for body text) ensuring high readability.
- **Decorations**: Subtle glows, soft gradients on primary actions, rounded borders (16px to 24px), avoiding visual clutter.

### Routes (GoRouter)
- `/splash` -> `SplashScreen`
- `/auth/login` -> `LoginScreen`
- `/auth/register` -> `RegisterScreen`
- `/auth/forgot-password` -> `ForgotPasswordScreen`
- `/dashboard` -> `DashboardScreen` (Central hub)
- `/news` -> `NewsScreen`
- `/education` -> `EducationScreen`
- `/ai-assistant` -> `AiAssistantScreen`
- `/profile` -> `ProfileScreen`
- `/vr/pre-test` -> `VrPreTestScreen`
- `/vr/launch` -> `VrLaunchScreen`
- `/vr/post-test` -> `VrPostTestScreen`
- `/vr/progress` -> `TrainingProgressScreen`

### Screens to Build
1. **SplashScreen**: Futuristic logo animation.
2. **Auth Screens**: Login, Registration, Forgot Password.
3. **DashboardScreen**: Primary navigation hub showcasing VR prominently.
4. **NewsScreen**: Grid/list of news/updates.
5. **EducationScreen**: Modules for GMP/CPOB learning.
6. **AiAssistantScreen**: Chat interface for the AI assistant.
7. **ProfileScreen**: User stats and settings.
8. **VR Supporting Screens**: Pre-test, VR connection/launch, Post-test, and Training progress analytics.

### Reusable Widgets
1. `PharmPrimaryButton`: Call to action with subtle glowing teal gradient.
2. `PharmTextField`: Form input with dark styling and seamless validation feedback.
3. `PharmGlassCard`: Semi-transparent panel for content grouping.
4. `PharmLoadingIndicator`: Custom futuristic loader.
5. `PharmErrorMessage`: Snackbar or inline error text.
6. `PharmEmptyState`: Consistent empty list placeholder.

### State Management Approach
- **Tool**: `flutter_riverpod` (specifically async code generators).
- **Strategy**: Passive UI with business logic inside `Notifier` or `AsyncNotifier` classes.
- UI will handle `AsyncValue` extensions gracefully, showing loading indicators or error messages based on state.

### Validation Approach
- Centralized `ValidatorBuilder` inside `lib/core/utils/validators.dart`.
- Uses regex and custom constraints (e.g., email format, min password length).
- Integrated with standard Flutter `Form` wrapping `PharmTextField` widgets.

### Notes for Backend Readiness
- **Clean Architecture Contracts**: Features use `Repositories` (interfaces in `domain/`, implementations in [data/](file:///e:/Flutter/pharmvrpro/.metadata)).
- While strictly frontend for now, mocked responses will emulate standard Laravel API shapes (e.g., `{ "data": {...}, "message": "Success", "status": 200 }`).
- The Dio network client configuration will support interceptors for dynamic Auth Bearer Tokens and error handling.

## Verification Plan

### Automated Tests
- **Unit Tests**: run `flutter test` for `ValidatorBuilder` utility logic to ensure form validation robustness.
- **Widget Tests**: Basic rendering tests for `PharmPrimaryButton` and `PharmTextField` handling error vs valid states.
- **Golden Tests**: To confirm specific UI layouts (like `PharmGlassCard`) correctly render the premium dark theme across target devices.

### Manual Verification
- Run the app via `flutter run -d chrome` or on an Android emulator.
- Manually navigate through the routes: Splash -> Login -> Dashboard -> VR Launch -> Post-test.
- Trigger validation errors on the Login and Registration screens and verify UI responds gracefully.
- Verify the futuristic aesthetic matches expectations (dark themes, glowing teals).
