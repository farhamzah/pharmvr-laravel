# PharmVR Modul Pelatihan API Specification

Modul Pelatihan adalah komponen inti dari layar Edukasi yang menghubungkan materi teori dengan simulasi VR.

## 1. List Response Structure (`GET /api/v1/edukasi?content_type=module`)
Setiap card modul pelatihan akan menerima data berikut:
- **`code`**: Kode modul (misal: `MP-001`).
- **`title`**: Judul modul.
- **`thumbnail_url`**: Gambar cover.
- **`duration_minutes`**: Estimasi waktu pengerjaan.
- **`learning_path`**: Status jalur belajar:
    - `has_pre_test`: boolean
    - `has_vr_sim`: boolean
    - `has_post_test`: boolean
- **`cta_label`**: Teks tombol (default: "Mulai Belajar").

## 2. Detail Response Structure (`GET /api/v1/edukasi/{slug}`)
Selain data di atas, detail response menyertakan:
- **`training_module_id`**: ID untuk meluncurkan simulasi VR jika user menekan "Mulai Simulasi".
- **`description`**: Konten teori lengkap atau instruksi pengerjaan.
- **`category` & `level`**: Informasi topik CPOB dan tingkat kesulitan.

---

## 3. Field Strategy for Training Path
Saat ini, jalur belajar (`learning_path`) bersifat statis di database untuk memberikan feedback visual pada frontend:
- **Progress UI**: Flutter dapat menggunakan `learning_path` untuk menampilkan progress bar atau step-by-step indicator.
- **Interactive Check**: Di masa depan, field ini akan di-update secara dinamis berdasarkan tabel `assessment_results`.

---

## 4. Future Extensibility
- **VR Integration**: Field `training_module_id` memungkinkan frontend untuk langsung memanggil API `/home` atau API session VR spesifik tanpa mencari ulang data.
- **Assessment Linkage**: Modul ini dirancang untuk otomatis membuka layar kuis (Assessment) setelah teori selesai dibaca.

---

## 5. Testing Checklist
- [ ] **Card Data**: Verifikasi field `code` dan `duration_minutes` muncul pada response list.
- [ ] **VR Link**: Pastikan `training_module_id` tidak null untuk modul yang memiliki simulasi VR.
- [ ] **Tab Aligned**: Pastikan data ini hanya muncul jika `type` adalah `module`.
