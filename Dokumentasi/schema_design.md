# PharmVR Phase 1: Database Schema Design

This document provides a clean, extensible, and production-ready database schema for the first phase of the PharmVR project using Laravel and MySQL/MariaDB.

## 1. Table List
- `users`: Core account and auth data.
- `personal_access_tokens`: API tokens (Sanctum).
- `user_profiles`: Editable user details.
- `user_preferences`: (Recommended) JSON-based store for flexible user settings.

---

## 2. Key Columns per Table

### Table: `users`
| Column | Type | Attributes | Description |
| :--- | :--- | :--- | :--- |
| `id` | BIGINT | PRIMARY, UNSIGNED, AUTO_INC | Unique identifier |
| `email` | VARCHAR(255) | UNIQUE, INDEX | Login credential |
| `password` | VARCHAR(255) | | Hashed password |
| `role` | ENUM('user', 'admin') | DEFAULT 'user' | Basic role readiness |
| `email_verified_at` | TIMESTAMP | NULLABLE | For verification flow |
| `remember_token` | VARCHAR(100) | NULLABLE | Standard Laravel auth |
| `created_at` | TIMESTAMP | | Record creation time |
| `updated_at` | TIMESTAMP | | Record update time |

### Table: `user_profiles`
| Column | Type | Attributes | Description |
| :--- | :--- | :--- | :--- |
| `id` | BIGINT | PRIMARY, UNSIGNED, AUTO_INC | Unique identifier |
| `user_id` | BIGINT | FOREIGN KEY, UNIQUE | Link to `users` |
| `first_name` | VARCHAR(100) | | |
| `last_name` | VARCHAR(100) | | |
| `phone` | VARCHAR(20) | NULLABLE | |
| `avatar_url` | VARCHAR(255) | NULLABLE | URL to cloud storage |
| `bio` | TEXT | NULLABLE | Short biography |
| `birth_date` | DATE | NULLABLE | For clinical age data |
| `gender` | ENUM('male', 'female', 'other') | NULLABLE | |
| `created_at` | TIMESTAMP | | |
| `updated_at` | TIMESTAMP | | |

### Table: `user_preferences` (Recommended)
| Column | Type | Attributes | Description |
| :--- | :--- | :--- | :--- |
| `id` | BIGINT | PRIMARY, UNSIGNED, AUTO_INC | |
| `user_id` | BIGINT | FOREIGN KEY, UNIQUE | Link to `users` |
| `settings` | JSON | | Flexible metadata |
| `created_at` | TIMESTAMP | | |
| `updated_at` | TIMESTAMP | | |

---

## 3. Relationships
- **users (1) ↔ (1) user_profiles**: One-to-one relationship.
- **users (1) ↔ (1) user_preferences**: One-to-one relationship.
- **users (1) ↔ (M) personal_access_tokens**: One-to-many (handled by Sanctum).

---

## 4. Migration Order
1. `create_users_table`
2. `create_password_reset_tokens_table` (Laravel default)
3. `create_sessions_table` (Laravel default)
4. `create_personal_access_tokens_table` (Sanctum)
5. `create_user_profiles_table` (Depends on `users`)
6. `create_user_preferences_table` (Depends on `users`)

---

## 5. Indexing Recommendations
- **`users.email`**: Already unique, used for login (essential).
- **`user_profiles.user_id`**: Foreign key and unique; speeds up eager loading (`with('profile')`).
- **`user_preferences.user_id`**: Foreign key and unique; speeds up loading application settings.
- **`personal_access_tokens.token`**: Unique (standard for fast lookup).

---

## 6. Future Extension Notes
- **Role Strategy**: For Phase 1, a `role` column on `users` is sufficient. If complex permissions (e.g., manager, trainer, student) arise later, integration with a package like `spatie/laravel-permission` is recommended.
- **Preferences**: The `JSON` column in `user_preferences` allows the frontend to save things like `dark_mode`, `language`, or `vr_sensitivity` without needing a database migration for every new setting.
- **Clinical Data**: Future medical records or VR training results should be placed in separate tables (e.g., `vr_sessions`, `medical_records`) linked to `user_id`.
