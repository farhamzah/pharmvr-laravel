# PharmVR Seeder Strategy - Phase 2 (Home, Edukasi, News)

Strategi seeder ini dirancang untuk memberikan data yang realistis dan bervolume cukup agar frontend Flutter dapat diuji secara maksimal (pagination, filtering, dan UI rendering).

## 1. Data Distribution Plan

| Module | Core Manual Data (Anchors) | Factory Data (Bulk) | Total Items |
| :--- | :--- | :--- | :--- |
| **News** | 3 Featured/Hero News | 12 Pagination Items | 15 Items |
| **Edukasi** | 3 Type Anchors (Module/Video/Doc) | 20 Pagination Items | 23 Items |
| **VR Modules** | 2 Simulations (Lab, Gowning) | - | 2 Items |

---

## 2. Realistic Themes
Data yang di-seed mencakup tema klinis dan industri farmasi yang spesifik:
- **News**: Regulasi Kemenkes, Teknologi CPOB 4.0, Riset Vaksin, Digitalisasi Pharma.
- **Edukasi**: Protokol Gowning, Sanitasi Area Steril, Lab Safety, Validasi GMP.
- **VR Integration**: Modul pelatihan "Dasar Ruang Steril" sudah terhubung ke simulasi "Pengenalan Lab Steril (VR)".

---

## 3. Factory Recommendations
Untuk pengembangan ke depan, gunakan factory berikut untuk menambah volume data:
- `News::factory()->count(10)->create()`
- `EducationContent::factory()->count(10)->create()`

---

## 4. Frontend Testing Usefulness
Dengan seeder ini, tim frontend dapat menguji:
1. **Vertical Scrolling**: Mencoba load more atau pagination pada tab News dan Edukasi.
2. **Category Switch**: Memastikan filter kategori (CPOB, GMP, Sanitasi) mengembalikan data yang benar.
3. **Empty States**: Mencoba mencari kata kunci sembarang untuk melihat UI "No Data Found".
4. **Action Triggers**: Menguji tombol "Mulai Belajar", "Tonton Video", dan "Tanya PharmAI" dengan data yang bervariasi.

---

## 5. Verification Checklist
- [ ] Jalankan `php artisan db:seed --class=ContentSeeder`.
- [ ] Cek endpoint `/api/v1/home` untuk melihat data hero yang muncul.
- [ ] Cek endpoint `/api/v1/edukasi?content_type=module&page=1` untuk verifikasi pagination.
