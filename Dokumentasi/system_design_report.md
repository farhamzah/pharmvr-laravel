# Desain Sistem: Flowchart dan Entity Relationship Diagram (ERD)

Dokumen ini melengkapi laporan ilmiah dengan detail teknis mengenai alur kerja sistem (Flowchart) dan struktur data (ERD) dari platform **PharmVR Pro**.

---

## 1. Flowchart: Siklus Pembelajaran VR (VR Learning Lifecycle)
Flowchart ini menggambarkan alur kerja pengguna (mahasiswa) mulai dari persiapan materi hingga penyelesaian simulasi VR dan evaluasi.

```mermaid
flowchart TD
    Start([Mulai]) --> Login[Login Akun Mahasiswa]
    Login --> Dashboard{Pilih Modul Pelatihan}
    
    subgraph "Tahap Persiapan (Aplikasi Mobile/Web)"
        Dashboard --> Education[Baca Materi PharmEdukasi]
        Education --> Readiness[Cek Kesiapan Perangkat VR]
        Readiness --> Pairing[Proses Pairing Device]
    end
    
    subgraph "Tahap Simulasi (VR Environment)"
        Pairing --> InitVR[Inisialisasi Sesi VR]
        InitVR --> Task[Mengerjakan Tugas Simulasi]
        Task --> AI_Hint{Butuh Bantuan?}
        AI_Hint -- Ya --> PharmaiAssistant[AI Memberikan Hint/Instruksi]
        PharmaiAssistant --> Task
        AI_Hint -- Tidak --> Task
        Task --> FinishTask[Selesaikan Tugas Simulasi]
    end
    
    subgraph "Tahap Evaluasi & Analitik"
        FinishTask --> Sync[Sinkronisasi Data Sesi ke Server]
        Sync --> Assessment[Pengerjaan Kuis/Assessment]
        Assessment --> Analytics[Update Progres & Leaderboard]
    end
    
    Analytics --> End([Selesai])
```

---

## 2. Entity Relationship Diagram (ERD)
ERD ini merepresentasikan struktur penyimpanan data inti yang saling berhubungan di dalam database PharmVR Pro.

```mermaid
erDiagram
    USERS ||--|| USER_PROFILES : "has one"
    USERS ||--o{ VR_PAIRINGS : "manages"
    USERS ||--o{ VR_SESSIONS : "performs"
    USERS ||--o{ PHARMAI_CONVERSATIONS : "interacts"
    USERS ||--o{ ASSESSMENT_ATTEMPTS : "takes"
    
    ROLES ||--o{ USERS : "assigned to"
    
    TRAINING_MODULES ||--o{ EDUCATION_CONTENTS : "contains"
    TRAINING_MODULES ||--o{ VR_SESSIONS : "associated with"
    TRAINING_MODULES ||--o{ ASSESSMENTS : "evaluated by"
    TRAINING_MODULES ||--o{ USER_TRAINING_PROGRESS : "tracks"
    
    VR_SESSIONS ||--o{ VR_SESSION_EVENTS : "logs"
    VR_SESSIONS ||--o{ VR_SESSION_HINTS : "recorded"
    VR_SESSIONS ||--o{ VR_SESSION_STAGE_RESULTS : "scores"
    VR_SESSIONS ||--|| SESSION_ANALYTICS : "generates"
    
    PHARMAI_CONVERSATIONS ||--o{ PHARMAI_MESSAGES : "contains"
    
    AI_KNOWLEDGE_SOURCES ||--o{ AI_KNOWLEDGE_CHUNKS : "digitized into"
    
    NEWS_SOURCES ||--o{ NEWS : "publishes"

    USERS {
        int id PK
        string email
        string role
        string status
    }
    
    VR_SESSIONS {
        int id PK
        int user_id FK
        int module_id FK
        timestamp start_time
        string status
    }
    
    TRAINING_MODULES {
        int id PK
        string title
        string slug
        string difficulty
    }
    
    AI_KNOWLEDGE_SOURCES {
        int id PK
        string source_name
        string filename
        boolean is_active
    }
```

---

## 3. Penjelasan Detail Komponen

### A. Komponen Flowchart
*   **Pairing Device**: Langkah krusial untuk menghubungkan identitas digital pengguna dengan perangkat keras VR yang digunakan.
*   **AI Hints**: Sistem memantau jeda aktivitas pengguna; jika terdeteksi kesulitan, AI akan mengintervensi dengan data dari *Knowledge Base*.
*   **Sinkronisasi**: Data dari Headset (perangkat otonom) dikirim kembali ke aplikasi mobile/web untuk visualisasi kemajuan.

### B. Relasi Database (ERD)
*   **User Centrality**: User menjadi pusat data utama yang menghubungkan riwayat pelatihan (VR Sessions), percakapan AI, dan hasil asesmen.
*   **Module Hierarchy**: Modul pelatihan menjadi wadah yang mengikat materi edukasi, simulasi VR, dan penilaian terkait.
*   **Detailed Logging**: Tabel `VR_SESSION_EVENTS` dan `VR_SESSION_HINTS` memungkinkan audit pengerjaan tugas secara mendalam untuk kebutuhan laporan ilmiah (reabilitas data).

---
*Dokumen ini disusun untuk mendukung bab Perancangan Sistem pada Laporan Ilmiah.*
