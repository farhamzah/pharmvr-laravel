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

class HygieneAssessmentSeeder extends Seeder
{
    public function run(): void
    {
        $module = TrainingModule::updateOrCreate(
            ['slug' => 'hygiene'],
            [
                'title' => 'Hygiene',
                'description' => 'Pre-test dan post-test untuk hand hygiene, personal hygiene, dan kesiapan masuk area produksi.',
                'difficulty' => 'beginner',
                'estimated_duration' => 10,
                'is_active' => true,
            ]
        );

        Assessment::updateOrCreate(
            ['module_id' => $module->id, 'type' => AssessmentType::PRETEST->value],
            [
                'title' => 'Pre-Test Hygiene',
                'description' => 'Verifikasi awal pemahaman personal hygiene sebelum memasuki scene Hygiene VR.',
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
                'title' => 'Post-Test Hygiene',
                'description' => 'Evaluasi akhir setelah menyelesaikan scene Hygiene VR. Nilai minimal 70 diperlukan untuk membuka Gowning.',
                'passing_score' => 70,
                'number_of_questions_to_take' => 5,
                'time_limit_minutes' => 10,
                'status' => AssessmentStatus::ACTIVE->value,
                'created_by' => 1,
            ]
        );

        $questions = [
            [
                'text' => 'Mengapa hand hygiene wajib dilakukan sebelum masuk area produksi?',
                'scope' => QuestionUsageScope::BOTH,
                'explanation' => 'Hand hygiene menurunkan risiko kontaminasi mikroba dan partikulat dari personel.',
                'options' => [
                    'Untuk mencegah kontaminasi dari personel ke produk dan area produksi' => true,
                    'Agar proses masuk area menjadi lebih cepat' => false,
                    'Untuk mengganti kebutuhan APD' => false,
                    'Agar operator tidak perlu mengikuti gowning' => false,
                ],
            ],
            [
                'text' => 'Mengapa perhiasan dan personal item tidak boleh dibawa ke area produksi?',
                'scope' => QuestionUsageScope::BOTH,
                'explanation' => 'Perhiasan dan barang pribadi dapat menjadi sumber kontaminasi dan mix-up.',
                'options' => [
                    'Karena dapat menjadi sumber kontaminasi dan benda asing' => true,
                    'Karena membuat operator lebih lambat berjalan' => false,
                    'Karena semua barang pribadi harus disimpan di ruang QC' => false,
                    'Karena hanya supervisor yang boleh memakai perhiasan' => false,
                ],
            ],
            [
                'text' => 'Apa fungsi sticky mat sebelum memasuki area bersih?',
                'scope' => QuestionUsageScope::BOTH,
                'explanation' => 'Sticky mat membantu menangkap debu dan partikel dari alas kaki.',
                'options' => [
                    'Menangkap partikel dari alas kaki sebelum masuk area bersih' => true,
                    'Mengeringkan tangan operator' => false,
                    'Mengukur suhu ruangan' => false,
                    'Menggantikan proses sanitasi tangan' => false,
                ],
            ],
            [
                'text' => 'Kapan mirror check dilakukan dalam alur hygiene/gowning?',
                'scope' => QuestionUsageScope::BOTH,
                'explanation' => 'Mirror check dilakukan untuk memastikan APD dan kondisi personel sesuai SOP sebelum lanjut.',
                'options' => [
                    'Sebelum lanjut ke area berikutnya untuk memastikan APD dan kebersihan sesuai SOP' => true,
                    'Setelah semua proses produksi selesai' => false,
                    'Hanya saat audit eksternal' => false,
                    'Hanya jika operator merasa APD tidak nyaman' => false,
                ],
            ],
            [
                'text' => 'Apa risiko utama jika prosedur hygiene dilewati?',
                'scope' => QuestionUsageScope::BOTH,
                'explanation' => 'Melewati hygiene meningkatkan risiko kontaminasi silang dan ketidaksesuaian CPOB.',
                'options' => [
                    'Kontaminasi silang dan risiko ketidaksesuaian CPOB' => true,
                    'Mesin produksi menjadi lebih cepat panas' => false,
                    'Batch record otomatis gagal dicetak' => false,
                    'Checkweigher menjadi tidak terkalibrasi' => false,
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
                    'usage_scope' => $questionData['scope']->value,
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
