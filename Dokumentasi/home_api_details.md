# PharmVR Home Hub API Specification

The Home Hub API is the central landing point for the PharmVR application, providing a high-octane, VR-centric data structure tailored for the Flutter frontend.

## 1. Route Definition
- **Endpoint**: `GET /api/v1/home`
- **Method**: `GET`
- **Auth**: `Bearer Token (Sanctum)`
- **Controller**: `HomeController@index`

---

## 2. Response Contract (Success Example)
```json
{
    "success": true,
    "message": "Home hub data successfully retrieved.",
    "data": {
        "user_greeting": {
            "full_name": "Farhan Al-Farisi",
            "academic_summary": "Universitas Indonesia"
        },
        "vr_status_header": {
            "connection_status": "connected",
            "headset_name": "Meta Quest 2",
            "last_seen": "2026-03-11T04:25:00Z",
            "ready_to_enter": true
        },
        "hero_module_card": {
            "code": "pengenalan-lab-steril",
            "title": "Pengenalan Lab Steril (VR)",
            "description": "Tur virtual ke dalam fasilitas produksi steril CPOB.",
            "estimated_duration": "15 min",
            "difficulty": "Beginner",
            "actions": ["continue_training", "enter_simulation"]
        },
        "progress_summary": {
            "total_modules": 5,
            "completed_modules": 2,
            "progress_percentage": 40,
            "completed_simulations": 12
        },
        "featured_learning_preview": {
            "modul": { "title": "Dasar CPOB", "slug": "dasar-cpob" },
            "video": { "title": "Tutorial Gowning", "slug": "video-gowning" },
            "document": { "title": "SOP Sanitasi", "slug": "sop-sanitasi" }
        },
        "latest_news_preview": [
            { "title": "Update CPOB 2026", "slug": "update-cpob-2026" },
            { "title": "Limbah Farmasi Baru", "slug": "limbah-baru" }
        ],
        "smart_actions": [
            { "label": "Tanya PharmAI", "action": "open_ai", "icon": "auto_awesome" }
        ]
    }
}
```

---

## 3. Placeholder Strategy
- **VR Sync**: Currently hardcoded in [HomeController](file:///e:/Flutter/pharmvrpro/backend/app/Http/Controllers/Api/V1/Content/HomeController.php#13-114). In future phases, this will read from a `vr_sessions` or `headset_sync` table.
- **Academic Summary**: Falls back to "Lengkapi profil akademik Anda" if `user_profiles` is missing university data.
- **Smart Actions**: Managed in the controller to allow dynamic dynamic frontend triggers based on role or progress.

---

## 4. Performance Notes
- **Eager Loading**: The user profile is loaded efficiently to avoid N+1 queries during the greeting block.
- **Categorical Queries**: Previews are fetched using indexed `type` columns for sub-millisecond response times.
- **Future Caching**: This endpoint is a prime candidate for a 5-minute user-specific cache (except for VR status).

---

## 5. Testing Checklist
- [ ] **Auth Check**: Accessing without token returns 401.
- [ ] **Empty State**: Verify fallback module if `user_training_progress` is empty.
- [ ] **Featured Content**: Verify each object in `featured_learning_preview` matches the nearest DB record.
- [ ] **Response Speed**: Ensure JSON generation stays under 100ms.
