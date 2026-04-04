<?php

namespace Database\Seeders;

use App\Models\News;
use App\Models\EducationContent;
use App\Models\TrainingModule;
use App\Models\Assessment;
use App\Models\QuestionBankItem;
use App\Models\QuestionBankOption;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Carbon\Carbon;

class ContentSeeder extends Seeder
{
    /**
     * Seed the application's database with Phase 2 contents.
     */
    public function run(): void
    {
        // Truncate tables to avoid duplicate entry errors
        Schema::disableForeignKeyConstraints();
        News::truncate();
        EducationContent::truncate();
        TrainingModule::truncate();
        Assessment::truncate();
        QuestionBankItem::truncate();
        QuestionBankOption::truncate();
        Schema::enableForeignKeyConstraints();

        // 1. Seed News
        News::create([
            'title'        => 'Implementasi Quality by Design (QbD) Dasar Efisiensi Produksi',
            'slug'         => Str::slug('Implementasi Quality by Design QbD Dasar Efisiensi Produksi'),
            'summary'      => 'Bagaimana pendekatan QbD membantu industri farmasi mengurangi variasi batch dan pemborosan material.',
            'content'      => 'Quality by Design (QbD) merupakan kerangka kerja sistematis yang menekankan pemahaman produk dan proses sejak awal...',
            'image_url'    => 'assets/images/news/compliance.jpg',
            'category'     => 'Industry',
            'is_featured'  => true,
            'published_at' => Carbon::now(),
        ]);

        News::create([
            'title'        => 'Persiapan Inspeksi BPOM: Fokus pada Integritas Data Digital',
            'slug'         => Str::slug('Persiapan Inspeksi BPOM Fokus pada Integritas Data Digital'),
            'summary'      => 'Memastikan rekaman batch elektronik memenuhi standar ALCOA+ dalam ekosistem CPOB 4.0.',
            'content'      => 'Integritas data kini menjadi fokus utama inspeksi BPOM di Indonesia seiring digitalisasi pabrik farmasi...',
            'image_url'    => 'assets/images/news/lab.jpg',
            'category'     => 'Regulation',
            'is_featured'  => true,
            'published_at' => Carbon::now()->subDays(1),
        ]);

        News::create([
            'title'        => 'Optimasi Laju Alir Serbuk pada Mesin Kompresi Tablet Kecepatan Tinggi',
            'slug'         => Str::slug('Optimasi Laju Alir Serbuk pada Mesin Kompresi Tablet Kecepatan Tinggi'),
            'summary'      => 'Studi kasus penggunaan eksipien inovatif untuk mencegah capping dan laminating pada tablet.',
            'content'      => 'Proses kompresi tablet sering menghadapi masalah teknis seperti capping jika sifat alir serbuk tidak optimal...',
            'image_url'    => 'assets/images/news/logistics.jpg',
            'category'     => 'Research',
            'is_featured'  => false,
            'published_at' => Carbon::now()->subDays(3),
        ]);

        News::create([
            'title'        => 'Validasi Metode Pembersihan: Deteksi Residu Aktif Menggunakan HPLC',
            'slug'         => Str::slug('Validasi Metode Pembersihan Deteksi Residu Aktif Menggunakan HPLC'),
            'summary'      => 'Teknik swab dan rinse sampling terbaru untuk memastikan eliminasi kontaminasi silang di fasilitas multi-produk.',
            'content'      => 'Pembersihan peralatan produksi antar produk yang berbeda adalah langkah krusial dalam GMP...',
            'image_url'    => 'assets/images/news/compliance.jpg',
            'category'     => 'Quality Control',
            'is_featured'  => false,
            'published_at' => Carbon::now()->subDays(5),
        ]);

        // 3. Seed Training Modules (VR)
        $vrLab = TrainingModule::create([
            'title'              => 'Pengenalan Lab Steril (VR)',
            'slug'               => 'pengenalan-lab-steril',
            'description'        => 'Tur virtual ke dalam fasilitas produksi steril CPOB.',
            'difficulty'         => 'Beginner',
            'estimated_duration' => 15,
            'cover_image_path'   => 'assets/images/news/compliance.jpg',
        ]);

        $vrGowning = TrainingModule::create([
            'title'              => 'Prosedur Gowning Level 3',
            'slug'               => 'prosedur-gowning-level-3',
            'description'        => 'Simulasi mengenakan pakaian steril lengkap untuk kelas A.',
            'difficulty'         => 'Intermediate',
            'estimated_duration' => 10,
            'cover_image_path'   => 'assets/images/news/lab.jpg',
        ]);

        $vrGmp = TrainingModule::create([
            'title'              => 'GMP Sterile Production',
            'slug'               => 'gmp-sterile',
            'description'        => 'Basics of sterile production and cleanroom protocols.',
            'difficulty'         => 'Beginner',
            'estimated_duration' => 20,
            'cover_image_path'   => 'assets/images/news/logistics.jpg',
        ]);

        // 2. Seed Education Contents
        EducationContent::create([
            'code'             => 'PH-GMP-01',
            'training_module_id' => $vrLab->id,
            'title'            => 'Pengenalan Lab Steril (VR)',
            'slug'             => 'pengenalan-lab-steril-education',
            'type'             => 'module',
            'category'         => 'CPOB',
            'related_topic'    => 'Personal Hygiene & Gowning',
            'level'            => 'Intermediate',
            'tags'             => ['Steril', 'Validation', 'ISO 5'],
            'learning_path'    => [
                'has_pre_test'  => true,
                'has_vr_sim'    => true,
                'has_post_test' => true,
            ],
            'next_step_label'  => 'Mulai Pre-Test Validasi',
            'next_step_action' => 'open_assessment',
            'description'      => 'Modul mendalam mengenai klasifikasi ruang bersih Kelas A hingga D sesuai aneks 1 CPOB terbaru.',
            'thumbnail_url'    => 'assets/images/news/compliance.jpg',
            'duration_minutes' => 60,
            'pages_count'      => 20,
        ]);

        EducationContent::create([
            'code'             => 'PH-GW-03',
            'training_module_id' => $vrGowning->id,
            'title'            => 'Prosedur Gowning Level 3',
            'slug'             => 'prosedur-gowning-level-3-education',
            'type'             => 'module',
            'category'         => 'CPOB',
            'related_topic'    => 'Gowning',
            'level'            => 'Intermediate',
            'tags'             => ['Steril', 'Gowning'],
            'learning_path'    => [
                'has_pre_test'  => true,
                'has_vr_sim'    => true,
                'has_post_test' => true,
            ],
            'next_step_label'  => 'Mulai Pre-Test Gowning',
            'next_step_action' => 'open_assessment',
            'description'      => 'Pelajari prosedur gowning lengkap untuk memasuki area produksi kelas A.',
            'thumbnail_url'    => 'assets/images/news/lab.jpg',
            'duration_minutes' => 30,
            'pages_count'      => 15,
        ]);

        EducationContent::create([
            'code'             => 'PH-GMP-PROD',
            'training_module_id' => $vrGmp->id,
            'title'            => 'GMP Sterile Production',
            'slug'             => 'gmp-sterile-production-education',
            'type'             => 'module',
            'category'         => 'CPOB',
            'related_topic'    => 'Production',
            'level'            => 'Beginner',
            'tags'             => ['GMP', 'Sterile', 'Production'],
            'learning_path'    => [
                'has_pre_test'  => true,
                'has_vr_sim'    => true,
                'has_post_test' => true,
            ],
            'next_step_label'  => 'Mulai Belajar GMP',
            'next_step_action' => 'open_assessment',
            'description'      => 'Pelajari dasar-dasar produksi steril sesuai standar GMP internasional.',
            'thumbnail_url'    => 'assets/images/news/logistics.jpg',
            'duration_minutes' => 45,
            'pages_count'      => 12,
        ]);

        EducationContent::create([
            'title'            => 'CPOB Part 1: Dasar-Dasar CPOB',
            'slug'             => 'cpob-part-1-dasar-dasar-cpob',
            'type'             => 'video',
            'category'         => 'CPOB',
            'related_topic'    => 'Quality System',
            'level'            => 'Beginner',
            'tags'             => ['CPOB', 'Indonesian GMP'],
            'next_step_label'  => 'Lanjutkan ke Part 2',
            'next_step_action' => 'open_module',
            'description'      => 'Pengenalan konsep Cara Pembuatan Obat yang Baik (CPOB) atau GMP di industri farmasi Indonesia.',
            'video_id'         => 'PWEPBGADqtU',
            'platform'         => 'youtube',
            'duration_minutes' => 15,
        ]);

        EducationContent::create([
            'title'            => 'CPOB Part 2: Personalia & Bangunan',
            'slug'             => 'cpob-part-2-personalia-bangunan',
            'type'             => 'video',
            'category'         => 'CPOB',
            'related_topic'    => 'Facilities',
            'level'            => 'Beginner',
            'tags'             => ['Personalia', 'Area Produksi'],
            'next_step_label'  => 'Cek Modul Validasi',
            'next_step_action' => 'open_module',
            'description'      => 'Pembahasan mendalam mengenai persyaratan personil dan fasilitas bangunan sesuai standar CPOB.',
            'video_id'         => 'PSNc2GGRbqo',
            'platform'         => 'youtube',
            'duration_minutes' => 18,
        ]);

        EducationContent::create([
            'title'            => 'CPOB 2018: Proses Produksi',
            'slug'             => 'cpob-2018-proses-produksi',
            'type'             => 'video',
            'category'         => 'Production',
            'related_topic'    => 'Manufacturing',
            'level'            => 'Intermediate',
            'tags'             => ['CPOB 2018', 'Produksi'],
            'next_step_label'  => 'Simulasi VR Produksi',
            'next_step_action' => 'open_vr',
            'description'      => 'Mempelajari manajemen produksi farmasi industri berdasarkan pedoman CPOB 2018.',
            'video_id'         => 'JW2rh4EbCXI',
            'platform'         => 'youtube',
            'duration_minutes' => 22,
        ]);

        EducationContent::create([
            'title'       => 'SOP Penanganan Produk Kembalian (Product Return)',
            'slug'        => 'sop-penanganan-produk-kembalian',
            'type'        => 'document',
            'category'    => 'Logistics',
            'related_topic' => 'Distribution (CDOB)',
            'level'       => 'Beginner',
            'tags'        => ['CDOB', 'Logistics', 'Quality'],
            'description' => 'Dokumen standar operasional prosedur untuk penerimaan, karantina, dan pemusnahan produk kembalian.',
            'file_url'    => 'https://example.com/sop_return.pdf',
            'thumbnail_url' => 'assets/images/news/compliance.jpg',
            'file_type'   => 'PDF',
            'pages_count' => 8,
        ]);

        EducationContent::create([
            'title'       => 'Pencampuran Sediaan Sitostatika [PH-4407] (Document)',
            'slug'        => 'pencampuran-sediaan-sitostatika-document',
            'type'        => 'document',
            'category'    => 'Production',
            'related_topic' => 'Manufacturing',
            'level'       => 'Intermediate',
            'tags'        => ['Sitostatika', 'Production', 'Safety'],
            'description' => 'Materi edukasi mengenai Pencampuran Sediaan Sitostatika [PH-4407] (Document) untuk tenaga profesional farmasi.',
            'file_url'    => 'https://drive.google.com/file/d/1lAlY2fOvrgjrUgl6W3a9SwpvR2Jk-Du6/view?usp=drive_link',
            'thumbnail_url' => 'assets/images/news/compliance.jpg', // Same as document #1
            'file_type'   => 'PDF',
            'pages_count' => 29,
        ]);

        EducationContent::create([
            'title'       => 'Prinsip CPOB 2024 [PH-4336] (Document)',
            'slug'        => 'prinsip-cpob-2024-document',
            'type'        => 'document',
            'category'    => 'Quality Assurance',
            'related_topic' => 'Compliance',
            'level'       => 'Beginner',
            'tags'        => ['CPOB 2024', 'QA', 'Regulatory'],
            'description' => 'Materi edukasi mengenai Prinsip CPOB 2024 [PH-4336] (Document) sesuai standar industri terbaru.',
            'file_url'    => 'https://drive.google.com/file/d/1lAlY2fOvrgjrUgl6W3a9SwpvR2Jk-Du6/view?usp=drive_link',
            'thumbnail_url' => 'assets/images/news/lab.jpg',
            'file_type'   => 'PDF',
            'pages_count' => 15,
        ]);

        $module = TrainingModule::where('slug', 'pengenalan-lab-steril')->first();
        if ($module) {
            Assessment::create([
                'module_id'          => $module->id,
                'type'               => 'pretest',
                'title'              => 'Pre-Test: Dasar Ruang Steril',
                'description'        => 'Uji pemahaman dasar Anda sebelum memasuki simulasi Lab Steril.',
                'passing_score'      => 80,
                'time_limit_minutes' => 10,
                'status'             => 'active',
                'number_of_questions_to_take' => 5,
            ]);

            $questions = [
                [
                    'text' => 'Berapa jumlah maksimum partikel > 5.0µm yang diizinkan di Kelas A (Operasional)?',
                    'explanation' => 'Menurut CPOB, Kelas A operasional harus memiliki jumlah partikel yang sangat rendah.',
                    'options' => [
                        ['text' => '20', 'correct' => true],
                        ['text' => '2900', 'correct' => false],
                        ['text' => '20000', 'correct' => false],
                        ['text' => '3500000', 'correct' => false],
                    ]
                ],
                [
                    'text' => 'Manakah urutan gowning yang benar untuk memasuki Kelas B?',
                    'explanation' => 'Urutan gowning dimulai dari area kotor ke area bersih.',
                    'options' => [
                        ['text' => 'Cuci tangan -> Gowning -> Sepatu steril -> Sarung tangan', 'correct' => true],
                        ['text' => 'Cuci tangan -> Sarung tangan -> Gowning', 'correct' => false],
                        ['text' => 'Sepatu steril -> Gowning -> Sarung tangan', 'correct' => false],
                        ['text' => 'Langsung pakai gowning', 'correct' => false],
                    ]
                ],
                [
                    'text' => 'Apa itu "Clean-in-Place" (CIP)?',
                    'explanation' => 'CIP adalah metode pembersihan peralatan tanpa pembongkaran.',
                    'options' => [
                        ['text' => 'Metode pembersihan peralatan tanpa pembongkaran', 'correct' => true],
                        ['text' => 'Membersihkan lantai ruangan', 'correct' => false],
                        ['text' => 'Mencuci tangan dengan alkohol', 'correct' => false],
                        ['text' => 'Pembersihan mingguan ruangan', 'correct' => false],
                    ]
                ],
                [
                    'text' => 'Berapa perbedaan tekanan (delta P) minimum antara dua kelas kebersihan yang berbeda?',
                    'explanation' => 'Standar CPOB mensyaratkan perbedaan tekanan udara untuk mencegah kontaminasi silang.',
                    'options' => [
                        ['text' => '10-15 Pa', 'correct' => true],
                        ['text' => '1-2 Pa', 'correct' => false],
                        ['text' => '50-100 Pa', 'correct' => false],
                        ['text' => 'Tidak perlu ada perbedaan', 'correct' => false],
                    ]
                ],
                [
                    'text' => 'Apa fungsi utama filter HEPA di ruang steril?',
                    'explanation' => 'Filter HEPA menyaring partikel udara dengan efisiensi sangat tinggi.',
                    'options' => [
                        ['text' => 'Menyaring kontaminan mikroba dan partikel udara', 'correct' => true],
                        ['text' => 'Mendinginkan suhu ruangan', 'correct' => false],
                        ['text' => 'Mengatur kelembaban udara', 'correct' => false],
                        ['text' => 'Menyerap bau kimia', 'correct' => false],
                    ]
                ],
            ];

            foreach ($questions as $index => $q) {
                $question = QuestionBankItem::create([
                    'module_id'     => $module->id,
                    'question_text' => $q['text'],
                    'explanation'   => $q['explanation'],
                    'usage_scope'   => 'pretest',
                    'difficulty'    => 'beginner',
                ]);

                foreach ($q['options'] as $oIndex => $o) {
                    QuestionBankOption::create([
                        'question_bank_item_id' => $question->id,
                        'option_text'           => $o['text'],
                        'is_correct'            => $o['correct'],
                        'option_key'            => chr(65 + $oIndex), // A, B, C, D
                        'sort_order'            => $oIndex + 1,
                    ]);
                }
            }
        }
    }
}
