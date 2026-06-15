<?php

namespace Database\Seeders;

use App\Models\VrSceneContent;
use Illuminate\Database\Seeder;

class VrSceneContentSeeder extends Seeder
{
    public function run(): void
    {
        VrSceneContent::updateOrCreate(
            [
                'scene_slug' => 'gmp_standard_room',
                'content_key' => 'cpob_12_aspects',
                'locale' => 'id',
                'version' => 1,
            ],
            [
                'content_type' => 'grid_panel',
                'title' => '12 Aspek CPOB / GMP',
                'subtitle' => 'Fondasi sistem mutu untuk ruang produksi farmasi.',
                'body' => 'Gunakan panel ini untuk menghubungkan elemen ruang standar CPOB dengan aspek regulatori yang perlu dipahami sebelum praktik produksi.',
                'items_json' => $this->cpobItems(),
                'metadata_json' => [
                    'source' => 'PharmVR GMP Standard Room baseline',
                    'learning_scene' => 'GMP Standard Room / Ruang Standar CPOB',
                    'editable_in_admin' => true,
                ],
                'sort_order' => 1,
                'is_active' => true,
                'status' => VrSceneContent::STATUS_PUBLISHED,
            ]
        );
    }

    private function cpobItems(): array
    {
        return [
            $this->item(1, 'Sistem Mutu', 'Sistem mutu memastikan seluruh aktivitas produksi dikendalikan, terdokumentasi, dan dievaluasi berkelanjutan.', 'Panel status ruang dan checklist kesiapan.', 'quality_system'),
            $this->item(2, 'Personalia', 'Personel harus kompeten, terlatih, dan memahami higiene serta tanggung jawab CPOB.', 'Akses masuk, briefing Vira, dan signage personel.', 'personnel'),
            $this->item(3, 'Bangunan & Fasilitas', 'Bangunan dan fasilitas harus mendukung pembersihan, alur kerja, dan pencegahan kontaminasi.', 'Lantai, dinding, coved corner, plafon, pintu, dan HVAC.', 'premises_facilities'),
            $this->item(4, 'Peralatan', 'Peralatan perlu bersih, sesuai tujuan, teridentifikasi, dan siap digunakan.', 'Machine base dan status equipment readiness.', 'equipment'),
            $this->item(5, 'Produksi', 'Produksi harus mengikuti prosedur tertulis, line clearance, dan kontrol proses yang sesuai.', 'Checklist kesiapan ruang sebelum operasi.', 'production'),
            $this->item(6, 'Penyimpanan & Distribusi', 'Penyimpanan dan distribusi menjaga status, identitas, dan kondisi mutu bahan maupun produk.', 'Label status dan pemisahan area bersih.', 'storage_distribution'),
            $this->item(7, 'Pengawasan Mutu (QC)', 'QC memverifikasi mutu melalui pengujian, spesifikasi, dan keputusan berbasis data.', 'Panel monitoring lingkungan sebagai bukti kontrol.', 'quality_control'),
            $this->item(8, 'Inspeksi Diri & Audit', 'Inspeksi diri dan audit memastikan sistem tetap patuh dan temuan ditindaklanjuti.', 'Hotspot inspeksi ruang dan catatan evidence.', 'self_inspection_audit'),
            $this->item(9, 'Keluhan & Penarikan', 'Keluhan dan penarikan membutuhkan traceability, investigasi, dan tindakan korektif yang jelas.', 'Dokumentasi status ruang dan identifikasi risiko mix-up.', 'complaints_recall'),
            $this->item(10, 'Dokumentasi', 'Dokumentasi memastikan setiap aktivitas dapat ditelusuri, diverifikasi, dan dipertanggungjawabkan.', 'Checklist, event learning, dan completion evidence.', 'documentation'),
            $this->item(11, 'Kegiatan Alih Daya', 'Kegiatan alih daya harus dikendalikan melalui kontrak, kualifikasi, dan pengawasan mutu.', 'Standar ruang sebagai acuan vendor/layanan pendukung.', 'outsourced_activities'),
            $this->item(12, 'Kualifikasi & Validasi', 'Kualifikasi dan validasi membuktikan fasilitas, sistem, dan proses bekerja sesuai tujuan.', 'HVAC, pressure cascade, dan readiness status.', 'qualification_validation'),
        ];
    }

    private function item(int $number, string $title, string $description, string $locationHint, string $cpobKey): array
    {
        return [
            'number' => $number,
            'title' => $title,
            'description' => $description,
            'location_hint' => $locationHint,
            'cpob_key' => $cpobKey,
            'is_active' => true,
            'sort_order' => $number,
        ];
    }
}
