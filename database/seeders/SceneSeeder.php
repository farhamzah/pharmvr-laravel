<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Scene;
use App\Models\SceneStep;
use App\Models\TrainingModule;

/**
 * Seeds the canonical WebXR scene registry.
 *
 * Legacy slugs that may still exist in older databases are intentionally not
 * deleted here: training-safety, production-corridor, final-mixing, mixing,
 * qc-lab, qa, and gudang. They should be migrated deliberately later if needed.
 */
class SceneSeeder extends Seeder
{
    public function run(): void
    {
        // Get or create the main training module
        $module = TrainingModule::firstOrCreate(
            ['slug' => 'produksi-tablet-non-steril'],
            [
                'title' => 'Produksi Tablet Non-Steril',
                'description' => 'Modul pelatihan CPOB/GMP untuk produksi sediaan tablet non-steril. Mencakup alur lengkap dari gowning hingga packaging.',
                'difficulty' => 'intermediate',
                'estimated_duration' => 120,
                'is_active' => true,
            ]
        );

        $scenes = $this->getSceneData($module->id);

        foreach ($scenes as $sceneData) {
            $steps = $sceneData['steps'];
            unset($sceneData['steps']);

            $scene = Scene::updateOrCreate(
                ['slug' => $sceneData['slug']],
                $sceneData
            );

            foreach ($steps as $stepData) {
                SceneStep::updateOrCreate(
                    ['scene_id' => $scene->id, 'slug' => $stepData['slug']],
                    $stepData
                );
            }
        }

        // Set scene dependencies (sequential unlock)
        $this->setSceneDependencies();

        $this->command->info('✅ Seeded ' . Scene::count() . ' scenes with ' . SceneStep::count() . ' steps.');
    }

    private function getSceneData(int $moduleId): array
    {
        return $this->canonicalSceneData($moduleId);

        return [
            // ─── SCENE 1: Training Room ────────────────────────
            [
                'training_module_id' => $moduleId,
                'slug' => 'training_room',
                'title' => 'Ruang Training & Safety Induction',
                'description' => 'Briefing keselamatan kerja, overview CPOB 5 pilar, dan persiapan sebelum memasuki area produksi.',
                'learning_objectives' => [
                    'Memahami 5 pilar CPOB (Personel, Bangunan, Peralatan, Produksi, Pengawasan Mutu)',
                    'Mengetahui prosedur safety induction area produksi',
                    'Memahami aturan dasar area bersih',
                ],
                'order_index' => 1,
                'priority' => 'P0',
                'difficulty' => 'beginner',
                'estimated_minutes' => 10,
                'environment_asset' => 'training_room',
                'is_active' => true,
                'steps' => [
                    [
                        'slug' => 'view_cpob_poster',
                        'title' => 'Baca Poster 5 Pilar CPOB',
                        'description' => 'Baca dan pahami poster 5 pilar CPOB yang terpasang di ruang training.',
                        'order_index' => 1,
                        'is_required' => true,
                        'scoring_weight' => 1.00,
                        'max_score' => 25,
                        'mistake_penalty' => 0,
                        'interaction_type' => 'observe',
                    ],
                    [
                        'slug' => 'watch_safety_video',
                        'title' => 'Tonton Video Safety Induction',
                        'description' => 'Tonton video safety induction selama 30 detik tentang prosedur keselamatan area produksi.',
                        'order_index' => 2,
                        'is_required' => true,
                        'scoring_weight' => 1.00,
                        'max_score' => 25,
                        'mistake_penalty' => 0,
                        'interaction_type' => 'observe',
                    ],
                    [
                        'slug' => 'complete_safety_checklist',
                        'title' => 'Isi Safety Checklist',
                        'description' => 'Centang 5 item safety checklist: APD, jalur evakuasi, larangan, SOP, emergency contact.',
                        'order_index' => 3,
                        'is_required' => true,
                        'scoring_weight' => 1.50,
                        'max_score' => 30,
                        'mistake_penalty' => 5,
                        'interaction_type' => 'click',
                    ],
                    [
                        'slug' => 'sign_attendance',
                        'title' => 'Tanda Tangan Digital Kehadiran',
                        'description' => 'Tandatangani form kehadiran training secara digital.',
                        'order_index' => 4,
                        'is_required' => true,
                        'scoring_weight' => 0.80,
                        'max_score' => 20,
                        'mistake_penalty' => 0,
                        'interaction_type' => 'click',
                    ],
                ],
            ],

            // ─── SCENE 2: Gowning ─────────────────────────────
            [
                'training_module_id' => $moduleId,
                'slug' => 'gowning',
                'title' => 'Produksi',
                'description' => 'Prosedur pemakaian Alat Pelindung Diri (APD) sesuai SOP CPOB sebelum memasuki area produksi.',
                'learning_objectives' => [
                    'Memahami urutan pemakaian APD yang benar',
                    'Mencegah kontaminasi silang melalui prosedur gowning yang tepat',
                    'Mengetahui jenis APD yang diperlukan untuk area produksi',
                ],
                'order_index' => 2,
                'priority' => 'P0',
                'difficulty' => 'intermediate',
                'estimated_minutes' => 15,
                'environment_asset' => 'gowning',
                'is_active' => true,
                'steps' => [
                    [
                        'slug' => 'hand_wash',
                        'title' => 'Cuci Tangan',
                        'description' => 'Cuci tangan dengan sabun antiseptik selama minimal 20 detik.',
                        'order_index' => 1,
                        'is_required' => true,
                        'scoring_weight' => 1.00,
                        'max_score' => 15,
                        'mistake_penalty' => 15,
                        'interaction_type' => 'sequence',
                    ],
                    [
                        'slug' => 'wear_hairnet',
                        'title' => 'Pakai Hairnet',
                        'description' => 'Pakai hairnet untuk menutup seluruh rambut.',
                        'order_index' => 2,
                        'is_required' => true,
                        'scoring_weight' => 1.00,
                        'max_score' => 15,
                        'mistake_penalty' => 10,
                        'interaction_type' => 'grab',
                    ],
                    [
                        'slug' => 'wear_mask',
                        'title' => 'Pakai Masker',
                        'description' => 'Pakai masker yang menutupi hidung dan mulut.',
                        'order_index' => 3,
                        'is_required' => true,
                        'scoring_weight' => 1.00,
                        'max_score' => 15,
                        'mistake_penalty' => 10,
                        'interaction_type' => 'grab',
                    ],
                    [
                        'slug' => 'wear_gown',
                        'title' => 'Pakai Gown/Coverall',
                        'description' => 'Pakai gown produksi yang bersih dan sesuai ukuran.',
                        'order_index' => 4,
                        'is_required' => true,
                        'scoring_weight' => 1.00,
                        'max_score' => 15,
                        'mistake_penalty' => 10,
                        'interaction_type' => 'grab',
                    ],
                    [
                        'slug' => 'wear_gloves',
                        'title' => 'Pakai Sarung Tangan',
                        'description' => 'Pakai sarung tangan steril. Pastikan tidak menyentuh permukaan luar.',
                        'order_index' => 5,
                        'is_required' => true,
                        'scoring_weight' => 1.00,
                        'max_score' => 15,
                        'mistake_penalty' => 10,
                        'interaction_type' => 'grab',
                    ],
                    [
                        'slug' => 'wear_shoe_cover',
                        'title' => 'Pakai Shoe Cover',
                        'description' => 'Pakai penutup sepatu sebelum memasuki area produksi.',
                        'order_index' => 6,
                        'is_required' => true,
                        'scoring_weight' => 1.00,
                        'max_score' => 15,
                        'mistake_penalty' => 10,
                        'interaction_type' => 'grab',
                        'validation_rule' => ['bonus_correct_order' => 10],
                    ],
                ],
            ],

            // ─── SCENE 3: Airlock ─────────────────────────────
            [
                'training_module_id' => $moduleId,
                'slug' => 'airlock',
                'title' => 'Hygiene (Air Lock)',
                'description' => 'Prosedur masuk area produksi melalui air lock dan air shower untuk mencegah kontaminasi.',
                'learning_objectives' => [
                    'Memahami fungsi air lock sebagai penghalang kontaminasi',
                    'Mengetahui prosedur interlock pintu',
                    'Memahami pentingnya perbedaan tekanan udara',
                ],
                'order_index' => 3,
                'priority' => 'P0',
                'difficulty' => 'beginner',
                'estimated_minutes' => 8,
                'environment_asset' => 'airlock',
                'is_active' => false,
                'steps' => [
                    [
                        'slug' => 'enter_antechamber',
                        'title' => 'Masuk Antechamber',
                        'description' => 'Masuk ke ruang antechamber air lock.',
                        'order_index' => 1,
                        'is_required' => true,
                        'scoring_weight' => 1.00,
                        'max_score' => 15,
                        'mistake_penalty' => 0,
                        'interaction_type' => 'navigate',
                    ],
                    [
                        'slug' => 'close_outer_door',
                        'title' => 'Tutup Pintu Luar',
                        'description' => 'Pastikan pintu luar tertutup rapat sebelum membuka pintu dalam.',
                        'order_index' => 2,
                        'is_required' => true,
                        'scoring_weight' => 1.20,
                        'max_score' => 20,
                        'mistake_penalty' => 20,
                        'interaction_type' => 'click',
                    ],
                    [
                        'slug' => 'check_pressure',
                        'title' => 'Verifikasi Perbedaan Tekanan',
                        'description' => 'Cek pressure gauge. Area produksi harus bertekanan positif terhadap koridor.',
                        'order_index' => 3,
                        'is_required' => true,
                        'scoring_weight' => 1.00,
                        'max_score' => 15,
                        'mistake_penalty' => 10,
                        'interaction_type' => 'inspect',
                    ],
                    [
                        'slug' => 'activate_air_shower',
                        'title' => 'Aktifkan Air Shower',
                        'description' => 'Tekan tombol untuk mengaktifkan air shower. Tunggu hingga proses selesai (~15 detik).',
                        'order_index' => 4,
                        'is_required' => true,
                        'scoring_weight' => 1.00,
                        'max_score' => 15,
                        'mistake_penalty' => 15,
                        'interaction_type' => 'click',
                    ],
                    [
                        'slug' => 'wait_air_shower',
                        'title' => 'Tunggu Air Shower Selesai',
                        'description' => 'Tunggu hingga timer air shower selesai. Jangan membuka pintu sebelum selesai.',
                        'order_index' => 5,
                        'is_required' => true,
                        'scoring_weight' => 1.00,
                        'max_score' => 15,
                        'mistake_penalty' => 15,
                        'interaction_type' => 'observe',
                    ],
                    [
                        'slug' => 'open_inner_door',
                        'title' => 'Buka Pintu Dalam',
                        'description' => 'Buka pintu dalam untuk masuk ke area produksi bersih.',
                        'order_index' => 6,
                        'is_required' => true,
                        'scoring_weight' => 1.00,
                        'max_score' => 20,
                        'mistake_penalty' => 20,
                        'interaction_type' => 'click',
                    ],
                ],
            ],

            // ─── SCENE 4: Production Corridor ─────────────────
            [
                'training_module_id' => $moduleId,
                'slug' => 'production_corridor',
                'title' => 'Koridor Produksi',
                'description' => 'Navigasi koridor produksi bersih, identifikasi zona, dan prosedur line clearance.',
                'learning_objectives' => [
                    'Memahami zona warna dalam area produksi',
                    'Mengetahui perbedaan jalur personel dan material',
                    'Memahami prosedur line clearance',
                    'Mengetahui lokasi jalur evakuasi darurat',
                ],
                'order_index' => 4,
                'priority' => 'P0',
                'difficulty' => 'beginner',
                'estimated_minutes' => 10,
                'environment_asset' => 'production_corridor',
                'is_active' => true,
                'steps' => [
                    [
                        'slug' => 'identify_zones',
                        'title' => 'Identifikasi 3 Zona Warna',
                        'description' => 'Identifikasi zona putih (bersih), abu-abu (transisi), dan hitam (umum).',
                        'order_index' => 1,
                        'is_required' => true,
                        'scoring_weight' => 1.00,
                        'max_score' => 25,
                        'mistake_penalty' => 10,
                        'interaction_type' => 'inspect',
                    ],
                    [
                        'slug' => 'follow_personnel_path',
                        'title' => 'Ikuti Jalur Personel',
                        'description' => 'Navigasi melalui jalur personel (bukan jalur material).',
                        'order_index' => 2,
                        'is_required' => true,
                        'scoring_weight' => 1.00,
                        'max_score' => 25,
                        'mistake_penalty' => 15,
                        'interaction_type' => 'navigate',
                    ],
                    [
                        'slug' => 'check_line_clearance',
                        'title' => 'Cek Line Clearance',
                        'description' => 'Verifikasi line clearance di 3 area: koridor, pintu ruang produksi, dan area staging.',
                        'order_index' => 3,
                        'is_required' => true,
                        'scoring_weight' => 1.20,
                        'max_score' => 25,
                        'mistake_penalty' => 10,
                        'interaction_type' => 'inspect',
                    ],
                    [
                        'slug' => 'find_emergency_exit',
                        'title' => 'Identifikasi Jalur Evakuasi',
                        'description' => 'Temukan dan identifikasi tanda emergency exit di koridor.',
                        'order_index' => 4,
                        'is_required' => true,
                        'scoring_weight' => 1.00,
                        'max_score' => 25,
                        'mistake_penalty' => 5,
                        'interaction_type' => 'navigate',
                    ],
                ],
            ],

            // ─── SCENE 5: Ruang Timbang (Weighing Room) ────────
            [
                'training_module_id' => $moduleId,
                'slug' => 'weighing',
                'title' => 'Ruang Timbang (Weighing Room)',
                'description' => 'Prosedur penimbangan bahan baku farmasi sesuai CPOB, verifikasi material, dan dokumentasi.',
                'learning_objectives' => [
                    'Memahami pentingnya verifikasi label material',
                    'Melakukan prosedur TARE yang benar',
                    'Menimbang sesuai toleransi batch record',
                    'Melakukan dokumentasi real-time',
                ],
                'order_index' => 5,
                'priority' => 'P0',
                'difficulty' => 'intermediate',
                'estimated_minutes' => 15,
                'environment_asset' => 'weighing_room',
                'is_active' => true,
                'steps' => [
                    [
                        'slug' => 'enter_weighing_room',
                        'title' => 'Masuk Ruang Timbang',
                        'description' => 'Masuk ke ruang timbang dan identifikasi area kerja.',
                        'order_index' => 1,
                        'is_required' => true,
                        'scoring_weight' => 1.00,
                        'max_score' => 10,
                        'interaction_type' => 'navigate',
                    ],
                    [
                        'slug' => 'check_batch_record',
                        'title' => 'Periksa Batch Record',
                        'description' => 'Identifikasi bahan dan target berat yang harus ditimbang.',
                        'order_index' => 2,
                        'is_required' => true,
                        'scoring_weight' => 1.00,
                        'max_score' => 15,
                        'interaction_type' => 'inspect',
                    ],
                    [
                        'slug' => 'select_material',
                        'title' => 'Pilih Bahan Baku',
                        'description' => 'Pilih bahan baku yang tepat dan berstatus QA APPROVED.',
                        'order_index' => 3,
                        'is_required' => true,
                        'scoring_weight' => 1.50,
                        'max_score' => 25,
                        'mistake_penalty' => 20,
                        'interaction_type' => 'grab',
                    ],
                    [
                        'slug' => 'tare_scale',
                        'title' => 'TARE Timbangan',
                        'description' => 'Letakkan wadah timbang dan tekan tombol TARE.',
                        'order_index' => 4,
                        'is_required' => true,
                        'scoring_weight' => 1.00,
                        'max_score' => 15,
                        'interaction_type' => 'click',
                    ],
                    [
                        'slug' => 'weigh_material',
                        'title' => 'Timbang Bahan',
                        'description' => 'Timbang bahan hingga mencapai target 2.000 g ± 0.100 g.',
                        'order_index' => 5,
                        'is_required' => true,
                        'scoring_weight' => 2.00,
                        'max_score' => 30,
                        'mistake_penalty' => 10,
                        'interaction_type' => 'sequence',
                    ],
                    [
                        'slug' => 'submit_record',
                        'title' => 'Submit Dokumentasi',
                        'description' => 'Simpan hasil penimbangan pada tablet batch record.',
                        'order_index' => 6,
                        'is_required' => true,
                        'scoring_weight' => 1.00,
                        'max_score' => 20,
                        'interaction_type' => 'click',
                    ],
                ],
            ],

            // ─── SCENE 6: Gudang (Warehouse) ──────────────────
            [
                'training_module_id' => $moduleId,
                'slug' => 'warehouse',
                'title' => 'Gudang (Warehouse)',
                'description' => 'Manajemen stok, penerimaan bahan baku, dan karantina material sesuai standar CPOB.',
                'learning_objectives' => [
                    'Memahami alur penerimaan barang',
                    'Mengetahui status label material (Karantina, Diluluskan, Ditolak)',
                    'Memahami kondisi penyimpanan (suhu & kelembaban)',
                ],
                'order_index' => 6,
                'priority' => 'P1',
                'difficulty' => 'intermediate',
                'estimated_minutes' => 20,
                'environment_asset' => 'warehouse',
                'is_active' => true,
                'steps' => [],
            ],

            // ─── SCENE 6: PPIC ────────────────────────────────
            [
                'training_module_id' => $moduleId,
                'slug' => 'ppic',
                'title' => 'PPIC',
                'description' => 'Perencanaan Produksi dan Pengendalian Persediaan bahan baku serta produk jadi.',
                'learning_objectives' => [
                    'Memahami alur ERP/SAP dalam industri farmasi',
                    'Manajemen jadwal produksi',
                ],
                'order_index' => 6,
                'priority' => 'P2',
                'difficulty' => 'intermediate',
                'estimated_minutes' => 15,
                'environment_asset' => 'office_ppic',
                'is_active' => true,
                'steps' => [],
            ],

            // ─── SCENE 7: Purchasing ──────────────────────────
            [
                'training_module_id' => $moduleId,
                'slug' => 'purchasing',
                'title' => 'Purchasing',
                'description' => 'Prosedur pengadaan bahan baku dari vendor yang terkualifikasi.',
                'learning_objectives' => [
                    'Kualifikasi pemasok (vendor qualification)',
                    'Alur dokumen PO hingga kedatangan barang',
                ],
                'order_index' => 7,
                'priority' => 'P2',
                'difficulty' => 'beginner',
                'estimated_minutes' => 10,
                'environment_asset' => 'office_purchasing',
                'is_active' => true,
                'steps' => [],
            ],

            // ─── SCENE 8: QC (Quality Control) ────────────────
            [
                'training_module_id' => $moduleId,
                'slug' => 'qc-lab',
                'title' => 'Quality Control (QC)',
                'description' => 'Pengujian fisik, kimia, dan mikrobiologi terhadap sampel produk.',
                'learning_objectives' => [
                    'Prosedur pengambilan sampel (sampling)',
                    'Pengujian spesifikasi produk',
                ],
                'order_index' => 8,
                'priority' => 'P1',
                'difficulty' => 'advanced',
                'estimated_minutes' => 30,
                'environment_asset' => 'lab_qc',
                'is_active' => true,
                'steps' => [
                    ['slug' => 'receive_sample', 'title' => 'Sample Receiving', 'description' => 'Scan barcode dan verifikasi label sampel.', 'order_index' => 1, 'is_required' => true, 'scoring_weight' => 1.0, 'max_score' => 10, 'interaction_type' => 'click'],
                    ['slug' => 'weighing_paper', 'title' => 'Prepare Analytical Balance', 'description' => 'Buka draft shield dan letakkan weighing paper.', 'order_index' => 2, 'is_required' => true, 'scoring_weight' => 1.0, 'max_score' => 10, 'interaction_type' => 'click'],
                    ['slug' => 'weight_variation', 'title' => 'Weight Variation Test', 'description' => 'Timbang tablet dan pastikan stabil.', 'order_index' => 3, 'is_required' => true, 'scoring_weight' => 1.0, 'max_score' => 10, 'interaction_type' => 'click'],
                    ['slug' => 'disintegration', 'title' => 'Disintegration Test', 'description' => 'Uji waktu hancur tablet dalam water bath.', 'order_index' => 4, 'is_required' => true, 'scoring_weight' => 1.0, 'max_score' => 10, 'interaction_type' => 'click'],
                    ['slug' => 'friability', 'title' => 'Friability Test', 'description' => 'Uji keregasan tablet menggunakan friabilator.', 'order_index' => 5, 'is_required' => true, 'scoring_weight' => 1.0, 'max_score' => 10, 'interaction_type' => 'click'],
                    ['slug' => 'hardness', 'title' => 'Hardness Test', 'description' => 'Uji kekerasan tablet.', 'order_index' => 6, 'is_required' => true, 'scoring_weight' => 1.0, 'max_score' => 10, 'interaction_type' => 'click'],
                    ['slug' => 'dissolution', 'title' => 'Dissolution Test', 'description' => 'Uji laju disolusi tablet.', 'order_index' => 7, 'is_required' => true, 'scoring_weight' => 1.0, 'max_score' => 10, 'interaction_type' => 'click'],
                    ['slug' => 'hplc_load', 'title' => 'HPLC Sample Loading', 'description' => 'Masukkan vial ke autosampler.', 'order_index' => 8, 'is_required' => true, 'scoring_weight' => 1.0, 'max_score' => 10, 'interaction_type' => 'click'],
                    ['slug' => 'hplc_analysis', 'title' => 'HPLC Assay', 'description' => 'Jalankan analisis kromatografi.', 'order_index' => 9, 'is_required' => true, 'scoring_weight' => 1.0, 'max_score' => 10, 'interaction_type' => 'click'],
                    ['slug' => 'final_decision', 'title' => 'Batch Release Decision', 'description' => 'Putuskan pelulusan atau penolakan batch.', 'order_index' => 10, 'is_required' => true, 'scoring_weight' => 1.0, 'max_score' => 10, 'interaction_type' => 'click'],
                ],
            ],

            // ─── SCENE 9: QA (Quality Assurance) ──────────────
            [
                'training_module_id' => $moduleId,
                'slug' => 'qa',
                'title' => 'Quality Assurance (QA)',
                'description' => 'Pemastian seluruh sistem mutu berjalan sesuai CPOB dan pelulusan produk jadi.',
                'learning_objectives' => [
                    'Review batch record',
                    'Manajemen deviasi dan CAPA',
                ],
                'order_index' => 9,
                'priority' => 'P1',
                'difficulty' => 'advanced',
                'estimated_minutes' => 25,
                'environment_asset' => 'office_qa',
                'is_active' => true,
                'steps' => [],
            ],

            // ─── SCENE 10: Engineering ────────────────────────
            [
                'training_module_id' => $moduleId,
                'slug' => 'engineering',
                'title' => 'Engineering',
                'description' => 'Pemeliharaan fasilitas, sistem HVAC, dan kalibrasi mesin produksi.',
                'learning_objectives' => [
                    'Pemeliharaan preventif mesin',
                    'Sistem tata udara (HVAC) area bersih',
                ],
                'order_index' => 10,
                'priority' => 'P2',
                'difficulty' => 'intermediate',
                'estimated_minutes' => 20,
                'environment_asset' => 'utility_area',
                'is_active' => true,
                'steps' => [],
            ],
        ];
    }

    private function canonicalSceneData(int $moduleId): array
    {
        return [
            $this->scene($moduleId, 'lobby', 'Lobby / PharmVR Hub', 'Pusat orientasi PharmVR untuk memilih modul, memahami alur CPOB/GMP, dan memulai pembelajaran VR.', 0, 'P0', 'beginner', 8, 'lobby', [
                'Memahami navigasi utama PharmVR',
                'Mengenali alur modul produksi dan support',
                'Memahami peran Vira sebagai AI guide',
            ], [
                $this->step('orientation', 'Orientasi Lobby', 'Kenali panel modul, peta fasilitas, dan area bantuan Vira.', 1, 'observe', 100),
            ]),
            $this->scene($moduleId, 'training_room', 'Training Room', 'Ruang training untuk safety induction, pengenalan CPOB/GMP, SOP dasar, dan persiapan sebelum masuk area produksi.', 1, 'P0', 'beginner', 10, 'training_room', [
                'Memahami prinsip dasar CPOB/GMP',
                'Memahami prosedur safety induction',
                'Memahami aturan dasar area bersih',
            ], [
                $this->step('view_cpob_poster', 'Baca Poster CPOB', 'Pelajari ringkasan prinsip CPOB/GMP sebelum memulai simulasi.', 1, 'observe', 25),
                $this->step('watch_safety_video', 'Tonton Safety Induction', 'Ikuti materi keselamatan kerja dan aturan area produksi.', 2, 'observe', 25),
                $this->step('complete_safety_checklist', 'Isi Safety Checklist', 'Konfirmasi APD, jalur evakuasi, larangan, SOP, dan emergency contact.', 3, 'click', 30, 5),
                $this->step('sign_attendance', 'Tanda Tangan Kehadiran', 'Lengkapi kehadiran training secara digital.', 4, 'click', 20),
            ]),
            $this->scene($moduleId, 'hygiene', 'Hygiene', 'Area hygiene untuk pembelajaran sanitasi personal, hand hygiene, dan pencegahan kontaminasi sebelum gowning.', 2, 'P0', 'beginner', 10, 'hygiene', [
                'Memahami prosedur cuci tangan sesuai CPOB',
                'Memahami risiko kontaminasi dari personel',
                'Melakukan inspeksi kebersihan sebelum masuk produksi',
            ], [
                $this->step('handwash_sequence', 'Urutan Cuci Tangan', 'Ikuti urutan hand hygiene yang benar.', 1, 'sequence', 50, 10),
                $this->step('hygiene_check', 'Pemeriksaan Hygiene', 'Periksa kebersihan personel sebelum memakai APD.', 2, 'inspect', 50, 10),
            ]),
            $this->scene($moduleId, 'gowning', 'Gowning', 'Ruang pemakaian APD cleanroom sesuai SOP untuk mencegah kontaminasi silang.', 3, 'P0', 'intermediate', 15, 'gowning', [
                'Memahami urutan pemakaian APD',
                'Mencegah kontaminasi silang melalui prosedur gowning',
                'Mengetahui jenis APD area produksi',
            ], [
                $this->step('wear_hairnet', 'Pakai Hairnet', 'Pastikan seluruh rambut tertutup.', 1, 'grab', 15, 10),
                $this->step('wear_mask', 'Pakai Masker', 'Gunakan masker dengan benar menutupi hidung dan mulut.', 2, 'grab', 15, 10),
                $this->step('wear_gown', 'Pakai Gown', 'Gunakan gown atau coverall produksi yang bersih.', 3, 'grab', 25, 10),
                $this->step('wear_gloves', 'Pakai Sarung Tangan', 'Gunakan sarung tangan tanpa menyentuh permukaan luar.', 4, 'grab', 20, 10),
                $this->step('final_gowning_check', 'Final Gowning Check', 'Periksa kembali kelengkapan APD sebelum masuk airlock.', 5, 'inspect', 25, 10),
            ]),
            $this->scene($moduleId, 'airlock', 'Airlock', 'Ruang transisi dengan interlock dan pressure control untuk mencegah kontaminasi area produksi.', 4, 'P0', 'beginner', 8, 'airlock', [
                'Memahami fungsi airlock',
                'Memahami interlock pintu',
                'Memahami pressure stabilization',
            ], [
                $this->step('close_outer_door', 'Tutup Pintu Luar', 'Pastikan pintu luar tertutup sebelum membuka pintu dalam.', 1, 'click', 25, 20),
                $this->step('check_pressure', 'Cek Tekanan', 'Verifikasi indikator tekanan antar area.', 2, 'inspect', 25, 10),
                $this->step('wait_stabilization', 'Tunggu Stabilisasi', 'Tunggu proses stabilisasi selesai.', 3, 'observe', 25, 10),
                $this->step('open_inner_door', 'Buka Pintu Dalam', 'Masuk ke area produksi setelah status aman.', 4, 'click', 25, 20),
            ]),
            $this->scene($moduleId, 'production_corridor', 'Production Corridor', 'Koridor produksi untuk mengenali pressure cascade, room classification, alur personel, dan alur material.', 5, 'P0', 'beginner', 10, 'production_corridor', [
                'Memahami layout koridor produksi',
                'Mengenali jalur personel dan material',
                'Memahami line clearance dan signage area',
            ], [
                $this->step('identify_zones', 'Identifikasi Zona', 'Kenali zona area dan klasifikasi kebersihan.', 1, 'inspect', 25, 10),
                $this->step('follow_personnel_path', 'Ikuti Jalur Personel', 'Navigasi melalui jalur personel yang benar.', 2, 'navigate', 25, 15),
                $this->step('check_line_clearance', 'Cek Line Clearance', 'Periksa status line clearance sebelum masuk ruang proses.', 3, 'inspect', 25, 10),
                $this->step('find_emergency_exit', 'Identifikasi Emergency Exit', 'Temukan jalur evakuasi di area produksi.', 4, 'navigate', 25, 5),
            ]),
            $this->scene($moduleId, 'weighing', 'Weighing', 'Ruang penimbangan bahan baku dengan verifikasi label, status material, toleransi berat, dan dokumentasi real-time.', 6, 'P0', 'intermediate', 15, 'weighing_room', [
                'Memahami prosedur penimbangan bahan',
                'Melakukan TARE dan verifikasi material',
                'Mendokumentasikan hasil penimbangan',
            ], [
                $this->step('check_batch_record', 'Periksa Batch Record', 'Identifikasi bahan dan target berat.', 1, 'inspect', 15),
                $this->step('select_material', 'Pilih Material', 'Pilih bahan baku dengan status QA approved.', 2, 'grab', 25, 20),
                $this->step('tare_scale', 'TARE Timbangan', 'Letakkan wadah dan tekan TARE.', 3, 'click', 15),
                $this->step('weigh_material', 'Timbang Material', 'Timbang bahan sesuai toleransi.', 4, 'sequence', 30, 10),
                $this->step('submit_record', 'Submit Catatan', 'Simpan hasil penimbangan.', 5, 'click', 15),
            ]),
            $this->scene($moduleId, 'granulation', 'Granulation', 'Proses granulasi basah, pengeringan FBD, dan kontrol parameter kritis untuk menghasilkan granul siap blending.', 7, 'P0', 'intermediate', 18, 'granulation_room', [
                'Memahami proses granulasi',
                'Memahami parameter kritis HSG dan FBD',
                'Mendokumentasikan hasil proses granulasi',
            ], [
                $this->step('line_clearance', 'Line Clearance', 'Pastikan ruang granulasi siap digunakan.', 1, 'inspect', 20, 5),
                $this->step('hsg_process', 'High Shear Granulation', 'Jalankan proses granulasi basah sesuai parameter.', 2, 'sequence', 35, 10),
                $this->step('fbd_drying', 'Fluid Bed Drying', 'Keringkan granul hingga parameter sesuai.', 3, 'sequence', 30, 10),
                $this->step('record_granulation', 'Catat Proses', 'Lengkapi catatan proses granulasi.', 4, 'click', 15, 5),
            ]),
            $this->scene($moduleId, 'final_mixing', 'Final Mixing', 'Pencampuran akhir granul dengan bahan pelumas atau glidant seperti magnesium stearate, talc, atau aerosil sebelum proses tabletting.', 8, 'P0', 'intermediate', 15, 'dry_mixing_room', [
                'Memahami final blending',
                'Memahami risiko overmixing',
                'Mendokumentasikan parameter mixing',
            ], [
                $this->step('check_material', 'Verifikasi Material', 'Pastikan granul dan excipient sesuai batch record.', 1, 'inspect', 20, 5),
                $this->step('load_granules', 'Load Granul', 'Masukkan granul ke blender.', 2, 'grab', 20, 5),
                $this->step('add_lubricant', 'Tambahkan Lubricant', 'Tambahkan magnesium stearate atau bahan sesuai formula.', 3, 'grab', 20, 5),
                $this->step('run_mixing', 'Jalankan Mixing', 'Jalankan mixing sesuai waktu dan kecepatan.', 4, 'sequence', 30, 10),
                $this->step('record_mixing', 'Catat Mixing', 'Lengkapi catatan batch record.', 5, 'click', 10, 5),
            ]),
            $this->scene($moduleId, 'tabletting', 'Tabletting', 'Proses kompresi tablet, setup mesin, IPC, dan pengendalian parameter kritis tablet press.', 9, 'P0', 'intermediate', 18, 'tabletting_room', [
                'Memahami setup mesin tablet',
                'Memahami kontrol bobot dan kekerasan tablet',
                'Melakukan IPC selama kompresi',
            ], [
                $this->step('machine_setup', 'Setup Mesin', 'Atur parameter awal mesin tablet.', 1, 'click', 25, 5),
                $this->step('compression', 'Kompresi Tablet', 'Jalankan proses kompresi tablet.', 2, 'sequence', 35, 10),
                $this->step('ipc_check', 'IPC Check', 'Periksa bobot, hardness, dan friability.', 3, 'inspect', 30, 10),
                $this->step('record_tabletting', 'Catat Tabletting', 'Lengkapi catatan produksi tablet.', 4, 'click', 10, 5),
            ]),
            $this->scene($moduleId, 'coating', 'Coating', 'Proses penyalutan tablet dengan kontrol spray rate, suhu inlet/outlet, pan speed, dan dokumentasi batch.', 10, 'P0', 'intermediate', 18, 'coating_room', [
                'Memahami proses coating tablet',
                'Memahami parameter kritis coating',
                'Mengenali cacat coating',
            ], [
                $this->step('prepare_solution', 'Siapkan Larutan Coating', 'Verifikasi larutan penyalut.', 1, 'inspect', 20, 5),
                $this->step('load_tablets', 'Load Tablet', 'Masukkan tablet ke pan coating.', 2, 'grab', 20, 5),
                $this->step('run_coating', 'Jalankan Coating', 'Jalankan proses penyalutan sesuai parameter.', 3, 'sequence', 40, 10),
                $this->step('coating_qc', 'QC Coating', 'Periksa appearance dan weight gain.', 4, 'inspect', 20, 5),
            ]),
            $this->scene($moduleId, 'blistering', 'Blistering / Primary Packaging', 'Pengemasan primer blister dengan setup PVC/Alu foil, sealing, leak test, dan IPC packaging.', 11, 'P0', 'intermediate', 18, 'blistering_room', [
                'Memahami proses pengemasan primer',
                'Memahami setup blister machine',
                'Melakukan leak test dan IPC',
            ], [
                $this->step('film_foil_setup', 'Setup Film dan Foil', 'Pasang PVC film dan aluminium foil.', 1, 'click', 25, 5),
                $this->step('run_blistering', 'Jalankan Blistering', 'Jalankan pengemasan primer.', 2, 'sequence', 35, 10),
                $this->step('leak_test', 'Leak Test', 'Uji kebocoran blister.', 3, 'inspect', 25, 10),
                $this->step('record_blistering', 'Catat Packaging Primer', 'Lengkapi catatan packaging primer.', 4, 'click', 15, 5),
            ]),
            $this->scene($moduleId, 'secondary_packing', 'Secondary Packing', 'Pengemasan sekunder, cartoning, coding, vision inspection, checkweigher, dan final packing menuju warehouse.', 12, 'P0', 'intermediate', 15, 'secondary_packing', [
                'Memahami cartoning dan coding',
                'Memahami pemeriksaan visual dan checkweigher',
                'Memahami proses final packing',
            ], [
                $this->step('cartoning_setup', 'Setup Cartoning', 'Siapkan leaflet, carton, dan batch coding.', 1, 'click', 25, 5),
                $this->step('vision_check', 'Vision Inspection', 'Verifikasi coding dan label.', 2, 'inspect', 25, 5),
                $this->step('checkweigher', 'Checkweigher', 'Periksa berat dan reject handling.', 3, 'inspect', 25, 5),
                $this->step('final_pack', 'Final Pack', 'Selesaikan packing sekunder.', 4, 'click', 25, 5),
            ]),
            $this->scene($moduleId, 'qc_lab', 'QC Lab', 'Pemeriksaan formal granul, tablet, dan finished goods menggunakan alat QC fisik dan kimia.', 20, 'P1', 'advanced', 30, 'qc_lab', [
                'Memahami pengujian QC tablet',
                'Mengenali alat QC fisik dan kimia',
                'Membuat keputusan status batch berdasarkan data',
            ], [
                $this->step('receive_sample', 'Sample Receiving', 'Scan barcode dan verifikasi label sampel.', 1, 'click', 10),
                $this->step('physical_testing', 'Physical Testing', 'Lakukan hardness, friability, dan disintegration test.', 2, 'inspect', 30, 5),
                $this->step('chemical_testing', 'Chemical Testing', 'Lakukan HPLC atau dissolution test.', 3, 'inspect', 30, 5),
                $this->step('qc_decision', 'QC Decision', 'Tentukan status batch berdasarkan hasil uji.', 4, 'click', 30, 10),
            ]),
            $this->scene($moduleId, 'qa_office', 'QA Office', 'Pembelajaran sistem mutu, batch record review, deviation, CAPA, audit, dan release decision.', 21, 'P1', 'advanced', 25, 'qa_office', [
                'Memahami peran QA dalam sistem mutu',
                'Melakukan review batch record',
                'Memahami deviasi, CAPA, audit, dan release decision',
            ], [
                $this->step('batch_record_review', 'Review Batch Record', 'Periksa kelengkapan batch record.', 1, 'inspect', 35, 5),
                $this->step('deviation_capa', 'Evaluasi Deviasi dan CAPA', 'Analisis deviasi dan tindakan perbaikan.', 2, 'inspect', 35, 5),
                $this->step('release_decision', 'Release Decision', 'Buat keputusan release berdasarkan bukti mutu.', 3, 'click', 30, 10),
            ]),
            $this->scene($moduleId, 'warehouse', 'Warehouse', 'Manajemen penerimaan, karantina, penyimpanan, status label, suhu, kelembaban, dan distribusi material sesuai CPOB.', 22, 'P1', 'intermediate', 20, 'warehouse', [
                'Memahami alur penerimaan dan penyimpanan material',
                'Memahami status label material',
                'Memahami kontrol suhu dan kelembaban gudang',
            ], [
                $this->step('receive_material', 'Receive Material', 'Verifikasi material masuk.', 1, 'inspect', 25, 5),
                $this->step('label_status', 'Label Status', 'Identifikasi status karantina, diluluskan, atau ditolak.', 2, 'inspect', 25, 5),
                $this->step('storage_condition', 'Storage Condition', 'Periksa suhu dan kelembaban area simpan.', 3, 'inspect', 25, 5),
                $this->step('material_release', 'Material Release', 'Simulasikan alur material ke produksi.', 4, 'navigate', 25, 5),
            ]),
            $this->scene($moduleId, 'ppic', 'PPIC', 'Pembelajaran perencanaan produksi, pengendalian persediaan, jadwal produksi, dan koordinasi supply chain.', 23, 'P2', 'intermediate', 15, 'ppic', [
                'Memahami production planning',
                'Memahami inventory control',
                'Memahami koordinasi jadwal produksi',
            ]),
            $this->scene($moduleId, 'purchasing', 'Purchasing', 'Pembelajaran pengadaan bahan baku, vendor qualification, purchase order, dan dokumen kedatangan material.', 24, 'P2', 'beginner', 12, 'purchasing', [
                'Memahami vendor qualification',
                'Memahami alur purchase order',
                'Memahami kontrol dokumen pengadaan',
            ]),
            $this->scene($moduleId, 'engineering', 'Engineering', 'Pembelajaran utility, HVAC, maintenance, calibration, qualification, dan validation support.', 25, 'P2', 'intermediate', 20, 'engineering', [
                'Memahami utility dan HVAC',
                'Memahami preventive maintenance dan calibration',
                'Memahami qualification dan validation support',
            ]),
        ];
    }

    private function scene(
        int $moduleId,
        string $slug,
        string $title,
        string $description,
        int $order,
        string $priority,
        string $difficulty,
        int $estimatedMinutes,
        string $environmentAsset,
        array $learningObjectives,
        array $steps = []
    ): array {
        return [
            'training_module_id' => $moduleId,
            'slug' => $slug,
            'title' => $title,
            'description' => $description,
            'learning_objectives' => $learningObjectives,
            'order_index' => $order,
            'priority' => $priority,
            'difficulty' => $difficulty,
            'estimated_minutes' => $estimatedMinutes,
            'environment_asset' => $environmentAsset,
            'is_active' => true,
            'steps' => $steps,
        ];
    }

    private function step(
        string $slug,
        string $title,
        string $description,
        int $order,
        string $interactionType,
        int $maxScore = 100,
        int $mistakePenalty = 0
    ): array {
        return [
            'slug' => $slug,
            'title' => $title,
            'description' => $description,
            'order_index' => $order,
            'is_required' => true,
            'scoring_weight' => 1.00,
            'max_score' => $maxScore,
            'mistake_penalty' => $mistakePenalty,
            'interaction_type' => $interactionType,
        ];
    }

    /**
     * Set sequential scene dependencies.
     */
    private function setSceneDependencies(): void
    {
        $sceneOrder = [
            'hygiene',
            'gowning',
            'airlock',
            'production_corridor',
            'weighing',
            'granulation',
            'final_mixing',
            'tabletting',
            'coating',
            'blistering',
            'secondary_packing',
        ];

        Scene::whereIn('slug', [
            'lobby',
            'training_room',
            'hygiene',
            'qc_lab',
            'qa_office',
            'warehouse',
            'ppic',
            'purchasing',
            'engineering',
        ])->update(['required_previous_scene_id' => null]);

        for ($i = 1; $i < count($sceneOrder); $i++) {
            $current = Scene::where('slug', $sceneOrder[$i])->first();
            $previous = Scene::where('slug', $sceneOrder[$i - 1])->first();

            if ($current && $previous) {
                $current->update(['required_previous_scene_id' => $previous->id]);
            }
        }
    }
}
