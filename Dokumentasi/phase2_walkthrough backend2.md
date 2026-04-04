# Walkthrough: PharmVR Phase 2 (Content & Home Hub)

Phase 2 introduces the content-driven modules and the VR-centric Home hub.

## 1. Home Hub API (`GET /api/v1/home`)
The Home Hub provides a consolidated response for the VR landing screen.
- **VR Status**: Placeholders for `is_connected`, `device_name`, etc.
- **Hero Module**: Automatically identifies the user's current "In Progress" training module.
- **Recommended**: Lists other available VR training modules.
- **User Stats**: Displays XP, Rank, and total completed modules.

## 2. Edukasi API (`GET /api/v1/education`)
Designed to support the 3-tab layout in Flutter.
- **Tabs**: Filterable via `?type=module`, `?type=video`, or `?type=document`.
- **Detail**: Accessible via `/api/v1/education/{slug}`.
- **Fields**: Includes `duration_minutes`, `pages_count`, and `thumbnail_url`.

## 3. News API (`GET /api/v1/news`)
Pharmaceutical industry news feed.
- **Categorization**: Filterable via `?category=Industry`.
- **Ordering**: Automatically sorted by `latest` published date.
- **Detail**: Accessible via `/api/v1/news/{slug}`.

---

## Technical Enhancements
- **Seeders**: Use `php artisan db:seed --class=ContentSeeder` to populate the database with demonstration data.
- **Relationships**: Integrated [User](file:///e:/Flutter/pharmvrpro/backend/app/Models/User.php#16-80) -> [UserTrainingProgress](file:///e:/Flutter/pharmvrpro/backend/app/Models/UserTrainingProgress.php#8-42) -> [TrainingModule](file:///e:/Flutter/pharmvrpro/backend/app/Models/TrainingModule.php#9-35) for real-time progress tracking.
- **Resources**: Leveraged [TrainingModuleResource](file:///e:/Flutter/pharmvrpro/backend/app/Http/Resources/Api/V1/Content/TrainingModuleResource.php#8-34) to ensure consistent difficulty and status labels.

---

## Testing Checklist
- [/] **Home API**: Verify `hero_module` changes when a user starts a new training module. [/]
- [/] **Edukasi Filter**: Verify `type=video` only returns video contents. [/]
- [/] **News Pagination**: Verify `per_page` parameter works correctly. [/]
