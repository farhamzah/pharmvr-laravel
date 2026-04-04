# PharmVR Pro 🥽

PharmVR Pro is an immersive educational platform combining Virtual Reality (VR), an AI Assistant, and a Flutter Mobile Application. It is designed to train users in pharmacy procedures via interactive VR simulations and intelligent assessments.

## Architecture

This project is a multi-component mono-repo:
- **`lib/`**: Flutter Mobile & Web Application.
- **`backend/`**: Laravel 12 API Backend **(Note: this subdirectory manages its own Git history)**.
- **`admin/`**: Next.js Admin Panel (Temporarily dormant; administration is currently handled via Laravel Blade views).
- **`Dokumentasi/`**: Contains comprehensive Markdown architecture documents and walkthroughs.

## Getting Started

### Flutter App
1. Ensure you have Flutter SDK `^3.8.1` installed.
2. Run `flutter pub get` to install dependencies.
3. The API connection is auto-configured in `lib/core/config/network_constants.dart` (automatically switches between localhost, emulator `10.0.2.2`, and production based on debug mode).
4. Run the app: `flutter run`

### Backend (Laravel API & Admin Panel)
Please refer to the [Backend README](backend/README.md) for database setup, migrations, Nginx configuration, and VPS deployment instructions.
