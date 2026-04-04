# Walkthrough: Implementation of Phase 1 Database Schema

Saya telah selesai mengimplementasikan skema database untuk PharmVR Phase 1 di Laravel backend.

## Perubahan yang Dilakukan

### 1. Migrasi Database
- **Refactor `users` table**: Menghapus field profil (`phone`, `avatar`, `bio`) yang sebelumnya ada di tabel user untuk memisahkan data autentikasi dan data profil. Menambahkan kolom `role`.
- **New `user_profiles` table**: Menyimpan detail profil seperti nama depan, nama belakang, telepon, avatar, bio, tanggal lahir, dan jenis kelamin.
- **New `user_preferences` table**: Menggunakan kolom **JSON** `settings` untuk menyimpan preferensi pengguna yang fleksibel di masa depan.

**File Migrasi:**
- [0001_01_01_000000_create_users_table.php](file:///e:/Flutter/pharmvrpro/backend/database/migrations/0001_01_01_000000_create_users_table.php)
- [2026_03_11_031522_create_user_profiles_table.php](file:///e:/Flutter/pharmvrpro/backend/database/migrations/2026_03_11_031522_create_user_profiles_table.php)
- [2026_03_11_031523_create_user_preferences_table.php](file:///e:/Flutter/pharmvrpro/backend/database/migrations/2026_03_11_031523_create_user_preferences_table.php)

### 2. Model Eloquent
- **User Model**: Ditambahkan trait `HasApiTokens` (Sanctum) dan relasi `hasOne` ke [UserProfile](file:///e:/Flutter/pharmvrpro/backend/app/Models/UserProfile.php#8-31) dan [UserPreference](file:///e:/Flutter/pharmvrpro/backend/app/Models/UserPreference.php#8-29).
- **UserProfile Model**: Definisi mass assignment dan relasi `belongsTo` ke [User](file:///e:/Flutter/pharmvrpro/backend/app/Models/User.php#12-68).
- **UserPreference Model**: Definisi mass assignment, relasi `belongsTo` ke [User](file:///e:/Flutter/pharmvrpro/backend/app/Models/User.php#12-68), dan **casting JSON** untuk kolom `settings`.

---

## Langkah Selanjutnya untuk Anda

Untuk menerapkan perubahan ini di database MySQL/MariaDB Anda, silakan jalankan perintah berikut di terminal backend:

```powershell
php artisan migrate:fresh
```
*(Catatan: `migrate:fresh` akan menghapus data lama. Jika Anda ingin mempertahankan data, gunakan `php artisan migrate` setelah menyesuaikan data yang ada).*

## Verifikasi
Anda bisa memverifikasi relasi di Laravel Tinker:
```powershell
php artisan tinker
$user = App\Models\User::first();
$user->profile;
$user->preferences;
```
