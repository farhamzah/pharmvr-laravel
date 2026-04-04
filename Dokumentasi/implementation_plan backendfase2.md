# Implementation Plan: Phase 2 (Home, Edukasi, & News)

This plan covers the development of the content-driven modules and the VR-centric Home hub for the PharmVR backend.

## Proposed Changes

### 1. Database Schema (New Tables)
#### [NEW] `news`
- Table to store pharmaceutical industry news.
- Fields: [id](file:///e:/Flutter/pharmvrpro/lib/core/router/app_router.dart#279-303), `title`, `slug`, `content`, `image_url`, `category`, `published_at`, `is_active`.

#### [NEW] `education_contents`
- Table to store materials for the 3 Edukasi tabs.
- Fields: [id](file:///e:/Flutter/pharmvrpro/lib/core/router/app_router.dart#279-303), `title`, `slug`, `type` (module, video, document), `description`, `thumbnail_url`, `file_url`, `video_id`, `duration_minutes`, `pages_count`, `is_active`.

#### [NEW] `training_modules`
- Core VR training definitions for the Home Hub.
- Fields: [id](file:///e:/Flutter/pharmvrpro/lib/core/router/app_router.dart#279-303), `title`, `slug`, `description`, `cover_image_url`, `difficulty`, `estimated_duration`.

#### [NEW] `user_training_progress`
- Junction table to track user progress in VR modules.
- Fields: `user_id`, `training_module_id`, `last_accessed_at`, `completion_percentage`, `status`.

### 2. Controllers & Services
- **NewsController**: List (paginated) and Detail.
- **EducationController**: List with filtering by `type` (for the 3 tabs) and Detail.
- **HomeController**: Special "Hub" response combining VR status, Hero module, and active training progress.

### 3. API Resources
- `NewsResource`
- `EducationResource`
- `TrainingModuleResource`
- `HomeHubResource` (Custom composite resource)

## Verification Plan
### Automated Tests
- Syntax check for all new files.
- Manual verification of response shapes matching the Flutter contract.

### Manual Verification
- Seed data for each content type.
- Verify the Home API dynamically identifies the "Hero" module from training progress.
