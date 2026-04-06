# Analisis dan Spesifikasi Use Case: Platform PharmVR Pro

![Visualisasi Ekosistem PharmVR Pro](/C:/Users/farha/.gemini/antigravity/brain/b7b3c7fa-79df-4375-bd6e-d56bb349136b/pharmvr_usecase_concept_1775483269035.png)

## 1. Pendahuluan
Dokumen ini menyajikan analisis *Use Case* untuk platform **PharmVR Pro**, sebuah sistem edukasi kefarmasian terintegrasi yang menggabungkan teknologi *Virtual Reality* (VR) dan *Artificial Intelligence* (AI). Analisis ini bertujuan untuk mendefinisikan interaksi antara aktor (pengguna/sistem eksternal) dengan fungsionalitas sistem.

---

## 2. Identifikasi Aktor (Actor Definitions)
Berikut adalah daftar aktor yang berinteraksi dengan sistem PharmVR Pro:

| No | Aktor | Deskripsi Peran |
| :--- | :--- | :--- |
| 1 | **Mahasiswa (Student)** | Pengguna utama yang mengonsumsi materi edukasi, berita, dan melakukan simulasi praktik kefarmasian di lingkungan VR. |
| 2 | **Instruktur (Instructor)** | Aktor yang memiliki otoritas untuk memantau progres belajar mahasiswa dan memberikan umpan balik (feedback). |
| 3 | **Administrator** | Penanggung jawab pengelolaan konten sistem, manajemen basis pengetahuan AI, dan konfigurasi teknis. |
| 4 | **VR Headset (Device)** | Aktor sistem berupa perangkat keras yang melakukan sinkronisasi data sensor dan progres sesi secara *real-time* ke server backend. |
| 5 | **PharmAI (System Actor)** | Entitas cerdas yang memberikan bimbingan interaktif, menjawab kueri materi, dan menghasilkan *hint* otomatis. |

---

## 3. Diagram Use Case (UML Use Case Diagram)

```mermaid
useCaseDiagram
    actor "Mahasiswa" as M
    actor "VR Headset" as V
    actor "PharmAI" as AI
    actor "Administrator" as Admin

    package "Sistem PharmVR Pro" {
        usecase "UC-01: Autentikasi & Kelola Profil" as UC1
        usecase "UC-02: Akses Edukasi & Berita" as UC2
        usecase "UC-03: Pairing & Sinkronisasi Device" as UC3
        usecase "UC-04: Menjalankan Simulasi VR" as UC4
        usecase "UC-05: Interaksi Asisten AI" as UC5
        usecase "UC-06: Evaluasi & Leaderboard" as UC6
        usecase "UC-07: Manajemen Pengetahuan AI" as UC7
    }

    M --> UC1
    M --> UC2
    M --> UC3
    M --> UC4
    M --> UC5
    M --> UC6

    V --> UC3
    V --> UC4

    AI --> UC4 : "<<assist>>"
    AI --> UC5

    Admin --> UC1
    Admin --> UC7
```

---

## 4. Spesifikasi Use Case (Detailed Specification)

Fokus pada Use Case inti: **Menjalankan Simulasi VR (UC-04)**.

| Elemen | Deskripsi |
| :--- | :--- |
| **Nama Use Case** | Menjalankan Simulasi VR (VR Simulation Execution) |
| **Aktor Utama** | Mahasiswa |
| **Aktor Pendukung** | VR Headset, PharmAI |
| **Deskripsi** | Menjelaskan proses mahasiswa memulai dan menjalankan tugas praktik di dalam lingkungan VR hingga data tersinkronisasi. |
| **Prasyarat (Pre-condition)** | 1. Mahasiswa telah login di aplikasi mobile.<br>2. Perangkat VR telah terhubung (*Paired*) dengan akun mahasiswa. |
| **Kondisi Akhir (Post-condition)** | Data progres, skor tugas, dan log aktivitas tersimpan secara permanen di database server. |

### Skenario Utama (Main Flow)
1. Mahasiswa memilih modul pembelajaran VR melalui aplikasi mobile/web.
2. Sistem melakukan validasi kesiapan perangkat (*Launch Readiness Check*).
3. Mahasiswa mengenakan VR Headset; sistem memuat lingkungan virtual sesuai modul yang dipilih.
4. PharmAI memberikan panduan awal (instruksi tugas) di dalam dunia VR.
5. Mahasiswa melakukan langkah-langkah praktik kefarmasian yang diminta.
6. VR Headset mengirimkan *Heartbeat* dan *Event Log* secara berkala ke server.
7. Mahasiswa menyelesaikan tugas; sistem menghitung kalkulasi performa.
8. Sistem mengirimkan ringkasan hasil sesi ke aplikasi mobile mahasiswa.

### Skenario Alternatif (Alternative Flow)
- **A1: Koneksi Terputus**: Jika sensor atau Headset kehilangan koneksi, sistem secara otomatis melakukan "Interrupt Session" dan menyimpan status terakhir agar bisa dilanjutkan kembali.
- **A2: Bantuan AI**: Jika mahasiswa diam lebih dari 30 detik pada langkah tertentu, PharmAI secara otomatis memberikan *Contextual Hint*.

---

## 5. Kebutuhan Fungsional Terkait
Sesuai dengan implementasi teknis, *Use Case* di atas didukung oleh:
*   **Media Proxy**: Untuk pengiriman aset visual (thumbnail/materi) yang efisien.
*   **Real-time Analytics**: Untuk pemrosesan data leaderboard dan pencapaian instan.
*   **AI Knowledge Reprocessing**: Memungkinkan Admin memperbarui logika jawaban AI tanpa *downtime*.

---
*Dokumen ini disusun untuk kebutuhan Laporan Ilmiah / Dokumentasi Teknis Formal.*
