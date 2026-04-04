# Implementation Plan - Phase 3 (Assessments & VR Readiness)

Implementasi modul Assessment (Pre-Test & Post-Test) untuk memvalidasi pemahaman user sebelum dan sesudah mengikuti materi/simulasi VR.

## Proposed Changes

### 1. Database Schema
- **`assessments`**: Menampung data header tes.
    - [id](file:///e:/Flutter/pharmvrpro/lib/core/router/app_router.dart#279-303), `training_module_id`, `type` (pre_test, post_test), `title`, `description`, `min_score`, `duration_minutes`.
- **`questions`**: Bank soal.
    - [id](file:///e:/Flutter/pharmvrpro/lib/core/router/app_router.dart#279-303), `assessment_id`, `question_text`, `image_url`, `explanation`.
- **`options`**: Pilihan jawaban.
    - [id](file:///e:/Flutter/pharmvrpro/lib/core/router/app_router.dart#279-303), `question_id`, `option_text`, `is_correct`.
- **`assessment_attempts`**: Sesi pengerjaan user.
    - [id](file:///e:/Flutter/pharmvrpro/lib/core/router/app_router.dart#279-303), `user_id`, `assessment_id`, `score`, `status` (passed, failed, in_progress), `started_at`, `completed_at`.
- **`user_answers`**: Jawaban yang diberikan user per soal.
    - [id](file:///e:/Flutter/pharmvrpro/lib/core/router/app_router.dart#279-303), `attempt_id`, `question_id`, `option_id`.

### 2. API Endpoints
- `GET /api/v1/assessments/intro/{type}/{module_id}`: Mendapatkan info sebelum memulai tes.
- `POST /api/v1/assessments/start`: Memulai attempt baru.
- `GET /api/v1/assessments/questions/{attempt_id}`: Mengambil daftar soal (shuffle option).
- `POST /api/v1/assessments/submit`: Mengirimkan jawaban dan mendapatkan hasil.
- `GET /api/v1/assessments/results/{attempt_id}`: Detail hasil pengerjaan.

### 3. Readiness Logic (VR Gatekeeping)
- User hanya bisa masuk ke VR Simulation (`training_modules`) jika telah Lulus (`status = passed`) pada **Pre-Test** terkait.
- API `/api/v1/home` akan di-update untuk merefleksikan status eligibility ini pada `hero_module_card`.

## Verification Plan

### Automated Tests
- Test pengerjaan Pre-Test: Start -> Submit -> Check Score.
- Test eligibility: User belum pre-test vs sudah lulus pre-test.
- Test scoring logic: Pastikan perhitungan benar jika ada jawaban salah.

### Manual Verification
- Cek respon API intro di Postman.
- Pastikan shuffle pilihan jawaban bekerja agar tidak monoton.
