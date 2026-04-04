# PharmVR — Complete Training Journey Flow

## Deskripsi Masalah

User menjelaskan flow lengkap PharmVR yang harus tergambar jelas di frontend:

```
Register → Login → (Forgot Password) → Home
  ├── Update Profile
  ├── PharmAI
  ├── Edukasi (Modul / Video / Dokumen)
  ├── News
  └── Modul Pelatihan VR:
        Klik Modul → Mulai Belajar → Pre-Test → VR (Meta Quest 3 via QR Code) → Post-Test
```

### Alur VR Detail:
1. Mahasiswa klik modul di tab Edukasi
2. Klik **"Mulai Belajar"** → masuk Pre-Test
3. Selesai Pre-Test (lulus) → diarahkan ke **VR Connection**
4. Koneksi VR: **Aplikasi menampilkan QR Code** yang di-scan oleh Meta Quest 3
5. Setelah VR selesai → mahasiswa mengerjakan **Post-Test**
6. Seluruh journey ini tergambar di **Home** dashboard

## Status Saat Ini (Yang Sudah Ada)

| Komponen | Status | Catatan |
|---|---|---|
| Register / Login / Forgot Password | ✅ Lengkap | Sudah berfungsi |
| Home Dashboard | ✅ Ada | Training Journey widget sudah ada |
| Edukasi Tab + Module Cards | ✅ Baru ditambahkan | 3 tab: Modul, Video, Dokumen |
| Pre-Test / Post-Test | ✅ Ada | Intro → Question → Review → Result |
| VR Connect Screen | ⚠️ Ada tapi Bluetooth-style | Perlu diganti ke **QR Code** |
| VR Launch Screen | ✅ Ada | Checklist + Launch button |
| VR In-Session + End | ✅ Ada | Telemetry + End session |
| Assessment Result → Next Step routing | ✅ Ada | Pre-Test passed → VR, Post-Test passed → Summary |
| Training Journey di Home | ✅ Ada | Pre-Test → VR → Post-Test, tapi perlu diperkuat |

---

## Proposed Changes

### 1. VR Connection — QR Code Pairing

> [!IMPORTANT]
> Ini adalah perubahan terbesar. Saat ini VR Connect menggunakan mekanisme Bluetooth simulasi. User meminta mekanisme **QR Code** yang bisa di-scan oleh Meta Quest 3.

#### [MODIFY] [vr_connect_screen.dart](file:///e:/Flutter/pharmvrpro/lib/features/vr_experience/presentation/screens/vr_connect_screen.dart)

**Perubahan:**
- Ganti animasi "Searching for Headset" dengan **QR Code besar** di tengah layar
- QR Code berisi session token unik (untuk sekarang: `pharmvr://connect?session=<random_id>&module=<module_id>`)
- Tampilkan instruksi: *"Buka PharmVR app di Meta Quest 3, pilih 'Scan QR Code' dari menu utama"*
- Tambahkan countdown / auto-refresh untuk QR Code (opsional, bisa mock)
- Pertahankan indikator status: Waiting → Connected → Ready
- Gunakan package `qr_flutter` untuk generate QR code

#### [MODIFY] [vr_connection_provider.dart](file:///e:/Flutter/pharmvrpro/lib/features/vr_experience/presentation/providers/vr_connection_provider.dart)

**Perubahan:**
- Tambahkan field `sessionToken` (String) untuk QR payload
- Tambahkan method `generateQrSession(String moduleId)` → generate token
- `simulateConnection()` tetap ada untuk demo flow (seolah VR sudah scan)

---

### 2. Routing Flow: Module → Pre-Test → VR → Post-Test

#### [MODIFY] [education_cta_section.dart](file:///e:/Flutter/pharmvrpro/lib/features/education/presentation/widgets/education_cta_section.dart)

**Perubahan:**
- Tombol "Mulai Pre-Test" sudah routing ke `/assessment/intro/{moduleId}/pre` ✅
- Tidak ada perubahan besar, hanya pastikan `moduleId` diteruskan dengan benar

#### [MODIFY] [assessment_result_screen.dart](file:///e:/Flutter/pharmvrpro/lib/features/assessment/presentation/screens/assessment_result_screen.dart)

**Perubahan:**
- Pre-Test **lulus** → route ke `/vr/connect` (bukan langsung `/vr/launch`)
- Ini memaksa mahasiswa men-scan QR Code dulu sebelum masuk VR
- Pre-Test **gagal** → tetap "Retake Pre-Test"
- Post-Test → tetap ke Summary

#### [MODIFY] [vr_launch_screen.dart](file:///e:/Flutter/pharmvrpro/lib/features/vr_experience/presentation/screens/vr_launch_screen.dart)

**Perubahan minor:**
- Setelah VR session selesai (`endSimulation()`), auto-route ke Post-Test intro: `/assessment/intro/{moduleId}/post`
- Ini sudah ada via listener, tapi perlu dipastikan benar

---

### 3. Home Dashboard — Journey Visibility

#### [MODIFY] [dashboard_screen.dart](file:///e:/Flutter/pharmvrpro/lib/features/dashboard/presentation/screens/dashboard_screen.dart)

**Perubahan:**
- Training Journey widget sudah menampilkan Pre-Test → VR → Post-Test ✅
- Tambahkan teks kontekstual di bawah current module: *"Langkah berikutnya: Mulai Pre-Test"*
- **Current Module card** sudah clickable dan mengarah ke detail modul ✅

> [!NOTE]
> Tidak ada perubahan besar di Dashboard karena journey sudah tergambar. Fokus utama di VR Connect (QR Code) dan routing flow.

---

### 4. Dependency: `qr_flutter` Package

Perlu menambahkan package `qr_flutter` untuk generate QR Code di VR Connect screen.

```yaml
# pubspec.yaml
dependencies:
  qr_flutter: ^4.1.0
```

---

## Verification Plan

### Automated Tests
- Tidak ada automated test baru yang diusulkan untuk perubahan UI ini
- Existing tests: [test/widget_test.dart](file:///e:/Flutter/pharmvrpro/test/widget_test.dart) dan [test/validators_test.dart](file:///e:/Flutter/pharmvrpro/test/validators_test.dart) — tidak relevan untuk flow ini

### Manual Verification (oleh User)

1. **Buka tab Edukasi** → pilih tab "Modul Pelatihan" → klik modul **Cleanroom Gowning Protocol**
2. Di halaman detail modul, klik **"Mulai Pre-Test"**
3. Selesaikan Pre-Test → di halaman Result, pastikan tombol **"Launch VR Simulation"** sekarang mengarah ke **VR Connect (QR Code)**
4. Di VR Connect, pastikan **QR Code besar** ditampilkan di layar dengan instruksi scan
5. Klik "Simulasi Koneksi" → pastikan status berubah jadi Connected → auto-navigate ke VR Launch
6. Di VR Launch, klik "Launch VR" → setelah session selesai, pastikan auto-navigate ke **Post-Test**
7. Selesaikan Post-Test → pastikan diarahkan ke **Training Summary**
8. Kembali ke **Home** → pastikan Training Journey widget menunjukkan progress yang benar

> [!TIP]
> Karena ini adalah UI-only flow tanpa backend nyata, semua langkah VR menggunakan simulasi `Future.delayed`. QR Code menampilkan URL statis untuk sekarang.
