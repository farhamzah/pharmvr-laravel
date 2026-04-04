# Flutter Integration Plan (Phases 1-5)

This plan outlines the systematic integration of the PharmVR Flutter frontend with the Laravel backend.

## Integration Order

1.  **Phase 1: Foundation (Auth & Profile)** - Essential for token-based authentication.
2.  **Phase 2: Content (Home, Edukasi, News)** - Basic application data.
3.  **Phase 3: Assessment** - Critical logic for training eligibility.
4.  **Phase 4: VR Management** - Pairing and session control.
5.  **Phase 5: PharmAI** - Context-aware AI chat.

## Proposed Changes

### [Core Network](file:///e:/Flutter/pharmvrpro/lib/core/network)
- [MODIFY] [dio_provider.dart](file:///e:/Flutter/pharmvrpro/lib/core/network/dio_provider.dart): Update `BaseOptions` with the correct local/production URL. Enhance error handling for common Laravel response structures.

### [Auth Feature](file:///e:/Flutter/pharmvrpro/lib/features/auth)
- [NEW] [auth_data_source.dart](file:///e:/Flutter/pharmvrpro/lib/features/auth/domain/data_sources/auth_data_source.dart): Implement [Dio](file:///e:/Flutter/pharmvrpro/lib/core/network/dio_client.dart#3-36)-based API calls.
- [NEW] [auth_repository.dart](file:///e:/Flutter/pharmvrpro/lib/features/auth/domain/repositories/auth_repository.dart): Logic for auth persistence and state mapping.
- [MODIFY] [auth_provider.dart](file:///e:/Flutter/pharmvrpro/lib/features/auth/presentation/providers/auth_provider.dart): Replace mocks with repository calls.

### [Home Feature](file:///e:/Flutter/pharmvrpro/lib/features/dashboard)
- [NEW] [home_repository.dart](file:///e:/Flutter/pharmvrpro/lib/features/dashboard/domain/repositories/home_repository.dart): Connect to `/v1/home`.
- [MODIFY] Update UI to handle `vr_status_header` and `hero_module_card` from the backend.

### [Content Features](file:///e:/Flutter/pharmvrpro/lib/features/education)
- [NEW] Repositories for Education and News.
- Implement loading/error states for list views.

## DTO/Model Mapping Strategy

- Use standard JSON data classes with `fromJson` and `toJson`.
- Backend standard response: `{ success: bool, message: string, data: T }`.
- Flutter models will reside in `lib/core/models` (shared) or `lib/features/[feature]/domain/models`.

## Token Handling Strategy

- Use `shared_preferences` for initial persistence (as per existing setup).
- Inject token via `dioProvider` interceptor.
- Auto-logout on 401 response.

## Loading/Error Handling Strategy

- Use `StateProvider` or specialized `Notifier` states to track `isLoading` and `error`.
- Implement common UI widgets for showing errors with "Retry" functionality.

## Verification Plan

### Manual Verification
- Login/Register with local Laravel server.
- Verify Home hub data syncs correctly with backend seeders.
- Test VR pairing flow using simulated headset events.
- Check PharmAI chat history persistence.
