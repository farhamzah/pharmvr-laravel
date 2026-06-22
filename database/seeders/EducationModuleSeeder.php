<?php

namespace Database\Seeders;

use App\Models\EducationContent;
use App\Models\TrainingModule;
use Illuminate\Database\Seeder;

class EducationModuleSeeder extends Seeder
{
    /**
     * Align Education Center modules with the PharmVR non-sterile tablet
     * production path without touching assessment/progress tables.
     */
    public function run(): void
    {
        $modules = [
            [
                'slug' => 'gmp_standard_room',
                'title' => 'GMP Standard Room / Ruang Standar CPOB',
                'code' => 'PH-CPOB-00',
                'duration' => 20,
                'scene_slug' => 'gmp_standard_room',
                'description' => 'Foundation scene untuk mengenalkan permukaan ruang bersih, sudut lengkung, aliran HVAC, kaskade tekanan, signage, airlock, dan kesiapan peralatan sebelum masuk Production Path.',
            ],
            [
                'slug' => 'hygiene',
                'title' => 'Hygiene & Personnel Preparation',
                'code' => 'PH-CPOB-01',
                'duration' => 20,
                'scene_slug' => 'hygiene',
                'description' => 'Pelajari hygiene personel, kebersihan tangan, perilaku area produksi, dan persiapan awal sebelum memasuki jalur produksi tablet non-steril.',
            ],
            [
                'slug' => 'gowning_airlock',
                'title' => 'Gowning & Airlock Procedure',
                'code' => 'PH-CPOB-02',
                'duration' => 25,
                'scene_slug' => 'gowning',
                'description' => 'Latih urutan gowning dan kontrol airlock untuk menjaga kebersihan personel serta mencegah kontaminasi silang pada area produksi.',
            ],
            [
                'slug' => 'production_corridor',
                'title' => 'Production Corridor & Material Flow',
                'code' => 'PH-CPOB-03',
                'duration' => 25,
                'scene_slug' => 'production_corridor',
                'description' => 'Kenali alur personel, material, status ruangan, dan prinsip pemisahan jalur dalam koridor produksi CPOB non-steril.',
            ],
            [
                'slug' => 'weighing',
                'title' => 'Weighing & Dispensing',
                'code' => 'PH-CPOB-04',
                'duration' => 30,
                'scene_slug' => 'weighing',
                'description' => 'Praktik penimbangan bahan, verifikasi label, line clearance, dan dokumentasi dispensing sebelum proses granulasi.',
            ],
            [
                'slug' => 'granulation',
                'title' => 'Granulation Process',
                'code' => 'PH-CPOB-05',
                'duration' => 35,
                'scene_slug' => 'granulation',
                'description' => 'Pahami tahapan granulasi, kontrol parameter proses, pemeriksaan granul, dan pencatatan batch sesuai CPOB.',
            ],
            [
                'slug' => 'final_mixing',
                'title' => 'Final Mixing',
                'code' => 'PH-CPOB-06',
                'duration' => 30,
                'scene_slug' => 'final_mixing',
                'description' => 'Simulasikan pencampuran akhir, penambahan pelicin, pemeriksaan homogenitas, dan kesiapan massa tablet.',
            ],
            [
                'slug' => 'tabletting',
                'title' => 'Tabletting',
                'code' => 'PH-CPOB-07',
                'duration' => 35,
                'scene_slug' => 'tabletting',
                'description' => 'Latih setup mesin tablet, kontrol bobot/kekerasan, inspeksi in-process, dan penanganan deviasi kompresi tablet.',
            ],
            [
                'slug' => 'coating',
                'title' => 'Coating',
                'code' => 'PH-CPOB-08',
                'duration' => 35,
                'scene_slug' => 'coating',
                'description' => 'Pelajari coating pan, parameter spray, kontrol visual tablet salut, dan dokumentasi proses coating.',
            ],
            [
                'slug' => 'blistering',
                'title' => 'Blistering',
                'code' => 'PH-CPOB-09',
                'duration' => 30,
                'scene_slug' => 'blistering',
                'description' => 'Simulasikan forming, sealing, coding, checkweigher, reject handling, dan pemeriksaan kemasan primer.',
            ],
            [
                'slug' => 'secondary_packing',
                'title' => 'Secondary Packing',
                'code' => 'PH-CPOB-10',
                'duration' => 30,
                'scene_slug' => 'secondary_packing',
                'description' => 'Latih cartoning, coding, line clearance, kontrol reject, rekonsiliasi output, dan packaging record.',
            ],
            [
                'slug' => 'warehouse',
                'title' => 'Warehouse / Gudang Produk Jadi',
                'code' => 'PH-CPOB-11',
                'duration' => 20,
                'scene_slug' => 'warehouse',
                'description' => 'Latih transfer produk jadi dari Secondary Packing ke gudang finished goods, segregasi quarantine/released/hold/reject, FEFO/FIFO, stock card, suhu/RH, dan dispatch readiness.',
            ],
            [
                'slug' => 'report_certificate',
                'title' => 'Report & Certificate',
                'code' => 'PH-CPOB-12',
                'duration' => 15,
                'scene_slug' => null,
                'route' => '/vr/production-path-report',
                'description' => 'Review laporan Production Path 12/12, status kelulusan, dan sertifikat digital setelah seluruh post-test selesai.',
            ],
        ];

        $activeSlugs = collect($modules)->pluck('slug')->all();

        EducationContent::where('type', 'module')
            ->whereNotIn('slug', $activeSlugs)
            ->update(['is_active' => false]);

        foreach ($modules as $index => $item) {
            $trainingModule = null;

            if ($item['scene_slug'] !== null) {
                $trainingModule = TrainingModule::updateOrCreate(
                    ['slug' => $item['slug']],
                    [
                        'title' => $item['title'],
                        'description' => $item['description'],
                        'difficulty' => $index < 3 ? 'Beginner' : 'Intermediate',
                        'estimated_duration' => $item['duration'],
                        'cover_image_path' => null,
                        'is_active' => true,
                    ],
                );
            }

            EducationContent::updateOrCreate(
                ['slug' => $item['slug']],
                [
                    'code' => $item['code'],
                    'training_module_id' => $trainingModule?->id,
                    'title' => $item['title'],
                    'type' => 'module',
                    'source_type' => 'external',
                    'category' => 'CPOB Non-Sterile Tablet',
                    'related_topic' => 'Production Path',
                    'level' => $index < 3 ? 'Beginner' : 'Intermediate',
                    'tags' => ['CPOB', 'GMP', 'Non-Sterile Tablet', 'Production Path'],
                    'learning_path' => [
                        'has_pre_test' => $item['scene_slug'] !== null,
                        'has_vr_sim' => $item['scene_slug'] !== null,
                        'has_post_test' => $item['scene_slug'] !== null,
                        'scene_slug' => $item['scene_slug'],
                        'route' => $item['route'] ?? null,
                        'order' => $index + 1,
                        'thumbnail_type' => $item['scene_slug'] ?? $item['slug'],
                    ],
                    'next_step_label' => $item['scene_slug'] === null ? 'Lihat Report & Sertifikat' : 'Mulai Belajar',
                    'next_step_action' => $item['scene_slug'] === null ? 'open_report' : 'open_production_path',
                    'description' => $item['description'],
                    'short_summary' => $item['description'],
                    'thumbnail_url' => null,
                    'duration_minutes' => $item['duration'],
                    'pages_count' => null,
                    'is_active' => true,
                ],
            );
        }
    }
}
