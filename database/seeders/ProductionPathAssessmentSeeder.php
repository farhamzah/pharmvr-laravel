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

class ProductionPathAssessmentSeeder extends Seeder
{
    public function run(): void
    {
        foreach (config('production_path.scenes', []) as $scene) {
            $slug = $scene['slug'];
            $title = $scene['title'];

            $module = TrainingModule::updateOrCreate(
                ['slug' => $slug],
                [
                    'title' => $title,
                    'description' => "Assessment gate untuk scene {$title} dalam Production Path.",
                    'difficulty' => $slug === 'hygiene' ? 'beginner' : 'intermediate',
                    'estimated_duration' => 10,
                    'is_active' => true,
                ]
            );

            Assessment::updateOrCreate(
                ['module_id' => $module->id, 'type' => AssessmentType::PRETEST->value],
                [
                    'title' => "Pre-Test {$title}",
                    'description' => "Verifikasi awal sebelum memasuki scene {$title}.",
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
                    'title' => "Post-Test {$title}",
                    'description' => "Evaluasi akhir setelah menyelesaikan scene {$title}. Nilai minimal 70 diperlukan untuk membuka scene berikutnya.",
                    'passing_score' => 70,
                    'number_of_questions_to_take' => 5,
                    'time_limit_minutes' => 10,
                    'status' => AssessmentStatus::ACTIVE->value,
                    'created_by' => 1,
                ]
            );

            $this->seedQuestions($module, $title);
        }
    }

    private function seedQuestions(TrainingModule $module, string $title): void
    {
        $questions = [
            "Apa tujuan utama mengikuti SOP pada scene {$title}?",
            "Apa tindakan pertama sebelum memulai aktivitas pada scene {$title}?",
            "Mengapa dokumentasi real-time penting pada scene {$title}?",
            "Apa risiko utama jika langkah kritis pada scene {$title} dilewati?",
            "Kapan operator boleh melanjutkan ke tahap berikutnya pada scene {$title}?",
        ];

        foreach ($questions as $index => $text) {
            $question = QuestionBankItem::updateOrCreate(
                [
                    'module_id' => $module->id,
                    'question_text' => $text,
                ],
                [
                    'usage_scope' => QuestionUsageScope::BOTH->value,
                    'is_active' => true,
                    'difficulty' => 'basic',
                    'explanation' => 'Jawaban benar menekankan kepatuhan SOP, kontrol kontaminasi, dan dokumentasi sesuai CPOB.',
                    'created_by' => 1,
                ]
            );

            $question->options()->delete();
            $options = [
                'Mengikuti SOP, memastikan status aman, dan mendokumentasikan hasil sesuai CPOB' => true,
                'Mempercepat proses walaupun ada langkah yang belum diverifikasi' => false,
                'Mengabaikan dokumentasi jika operator sudah berpengalaman' => false,
                'Melanjutkan tahap berikutnya tanpa konfirmasi status' => false,
            ];

            $sort = 1;
            foreach ($options as $optionText => $isCorrect) {
                QuestionBankOption::create([
                    'question_bank_item_id' => $question->id,
                    'option_text' => $optionText,
                    'is_correct' => $isCorrect,
                    'sort_order' => $sort,
                    'option_key' => chr(64 + $sort),
                ]);
                $sort++;
            }
        }
    }
}
