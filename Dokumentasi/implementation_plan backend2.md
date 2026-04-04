# Database Schema Design - PharmVR Phase 1

This plan outlines the database structure for the initial phase of PharmVR, focusing on authentication, user management, and profile extensibility.

## Proposed Schema

### 1. Users Table (`users`)
Core authentication and account identification.
- `id` (Primary Key)
- `email` (Unique, Index)
- `password`
- `role` (String/Enum: 'user', 'admin')
- `email_verified_at` (Nullable)
- `remember_token`
- `timestamps` (`created_at`, `updated_at`)

### 2. User Profiles Table (`user_profiles`)
Detailed user information separated from auth data for better performance and modularity.
- `id` (Primary Key)
- `user_id` (Foreign Key, Unique Index)
- `first_name`
- `last_name`
- `phone` (Nullable)
- `avatar_url` (Nullable)
- `bio` (Text, Nullable)
- `gender` (Nullable)
- `birth_date` (Date, Nullable)
- `timestamps`

### 3. User Preferences Table (`user_preferences`)
JSON-based storage for future-proofing settings without schema changes.
- `id` (Primary Key)
- `user_id` (Foreign Key, Unique Index)
- `settings` (JSON) - e.g., `{ "notifications": true, "theme": "dark" }`
- `timestamps`

### 4. Personal Access Tokens (`personal_access_tokens`)
Handled by Laravel Sanctum for mobile/web API auth.

## Relationships
- **User (1) <-> (1) UserProfile**: Each user has exactly one profile.
- **User (1) <-> (1) UserPreference**: Each user has one set of settings.

## Indexing Recommendations
- Unique index on `users.email`.
- Foreign key and Unique index on `user_profiles.user_id`.
- Foreign key and Unique index on `user_preferences.user_id`.

## Future Extensions
- **Roles**: Can be moved to a dedicated `roles` and `role_user` (pivot) table if complex RBAC is needed later.
- **Preferences**: The JSON column allows adding new settings from the frontend without backend schema migrations.

## Verification Plan
### Automated Tests
- I will check the existing migrations and ensure they align with this design.
- I will provide the migration logic as a design specification.

### Manual Verification
- Review the proposed schema against the Laravel project requirements.
