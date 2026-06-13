<?php

namespace Database\Seeders;

use App\Enums\AssessmentStatus;
use App\Enums\AssessmentType;
use App\Enums\QuestionUsageScope;
use App\Models\Assessment;
use App\Models\QuestionBankItem;
use App\Models\QuestionBankOption;
use App\Models\TrainingModule;
use Illuminate\Database\Seeder;

class GmpStandardRoomAssessmentSeeder extends Seeder
{
    public function run(): void
    {
        $module = TrainingModule::updateOrCreate(
            ['slug' => 'gmp_standard_room'],
            [
                'title' => 'GMP Standard Room / Ruang Standar CPOB',
                'description' => 'Foundation scene untuk mengenalkan elemen desain ruang produksi yang mendukung penerapan CPOB.',
                'difficulty' => 'beginner',
                'estimated_duration' => 20,
                'is_active' => true,
            ]
        );

        Assessment::updateOrCreate(
            ['module_id' => $module->id, 'type' => AssessmentType::PRETEST->value],
            [
                'title' => 'Pre-Test GMP Standard Room',
                'description' => 'Verifikasi awal pemahaman elemen desain cleanroom sebelum memasuki scene GMP Standard Room.',
                'passing_score' => 0,
                'number_of_questions_to_take' => 5,
                'time_limit_minutes' => 10,
                'status' => AssessmentStatus::ACTIVE->value,
                'created_by' => 1,
            ]
        );

        Assessment::updateOrCreate(
            ['module_id' => $module->id, 'type' => AssessmentType::POSTTEST->value],
            [
                'title' => 'Post-Test GMP Standard Room',
                'description' => 'Evaluasi akhir setelah menyelesaikan scene GMP Standard Room. Nilai minimal 70 diperlukan untuk menyelesaikan modul foundation ini.',
                'passing_score' => 70,
                'number_of_questions_to_take' => 5,
                'time_limit_minutes' => 10,
                'status' => AssessmentStatus::ACTIVE->value,
                'created_by' => 1,
            ]
        );

        $questions = [
            [
                'text' => 'Apa tujuan utama coved corner pada ruang produksi bersih?',
                'explanation' => 'Coved corner mengurangi sudut tajam yang sulit dibersihkan dan membantu mencegah akumulasi partikel.',
                'options' => [
                    'Mengurangi area sulit dibersihkan dan mencegah akumulasi partikel' => true,
                    'Meningkatkan kapasitas penyimpanan bahan' => false,
                    'Menggantikan kebutuhan HVAC' => false,
                    'Menurunkan kebutuhan dokumentasi batch' => false,
                ],
            ],
            [
                'text' => 'Mengapa permukaan lantai dan dinding ruang produksi harus mudah dibersihkan?',
                'explanation' => 'Permukaan yang halus dan mudah dibersihkan mendukung sanitasi, inspeksi, dan pengendalian kontaminasi.',
                'options' => [
                    'Agar sanitasi dan pengendalian kontaminasi dapat dilakukan efektif' => true,
                    'Agar ruangan terlihat lebih gelap' => false,
                    'Agar operator dapat melewati checklist produksi' => false,
                    'Agar tekanan ruangan tidak perlu dipantau' => false,
                ],
            ],
            [
                'text' => 'Apa fungsi utama kombinasi supply dan return grille pada ruang produksi?',
                'explanation' => 'Supply dan return grille membantu mengarahkan pola aliran udara untuk mendukung kontrol partikel dan kebersihan area.',
                'options' => [
                    'Mengarahkan pola aliran udara untuk mendukung kontrol partikel' => true,
                    'Menentukan nomor batch produk' => false,
                    'Mengganti label status ruangan' => false,
                    'Menghilangkan kebutuhan line clearance' => false,
                ],
            ],
            [
                'text' => 'Apa tujuan pemantauan differential pressure atau pressure cascade?',
                'explanation' => 'Pressure cascade membantu menjaga arah aliran udara antar area sesuai kebutuhan kontrol kontaminasi.',
                'options' => [
                    'Menjaga arah aliran udara antar area untuk kontrol kontaminasi' => true,
                    'Mengatur warna signage ruangan' => false,
                    'Mengukur bobot tablet secara otomatis' => false,
                    'Membuka semua pintu airlock bersamaan' => false,
                ],
            ],
            [
                'text' => 'Apa yang harus dipastikan sebelum peralatan digunakan di ruang produksi?',
                'explanation' => 'Peralatan perlu berada dalam status bersih, sesuai label, siap digunakan, dan area telah melalui line clearance.',
                'options' => [
                    'Status bersih, label sesuai, siap digunakan, dan line clearance terpenuhi' => true,
                    'Peralatan pernah digunakan pada batch sebelumnya' => false,
                    'Semua alarm dimatikan agar proses cepat' => false,
                    'Operator melewati pemeriksaan dokumen' => false,
                ],
            ],
            [
                'text' => 'Mengapa signage atau status label ruangan penting dalam CPOB?',
                'explanation' => 'Signage membantu menunjukkan status area, alur akses, dan kondisi ruangan untuk mencegah salah penggunaan.',
                'options' => [
                    'Menunjukkan status area dan mencegah salah penggunaan ruangan' => true,
                    'Menggantikan pemeriksaan line clearance' => false,
                    'Menyimpan data hasil post-test' => false,
                    'Menaikkan tekanan ruangan secara otomatis' => false,
                ],
            ],
        ];

        foreach ($questions as $questionData) {
            $question = QuestionBankItem::updateOrCreate(
                [
                    'module_id' => $module->id,
                    'question_text' => $questionData['text'],
                ],
                [
                    'usage_scope' => QuestionUsageScope::BOTH->value,
                    'is_active' => true,
                    'difficulty' => 'basic',
                    'explanation' => $questionData['explanation'],
                    'created_by' => 1,
                ]
            );

            $question->options()->delete();
            $index = 0;
            foreach ($questionData['options'] as $text => $isCorrect) {
                QuestionBankOption::create([
                    'question_bank_item_id' => $question->id,
                    'option_text' => $text,
                    'is_correct' => $isCorrect,
                    'sort_order' => $index + 1,
                    'option_key' => chr(65 + $index),
                ]);
                $index++;
            }
        }
    }
}
