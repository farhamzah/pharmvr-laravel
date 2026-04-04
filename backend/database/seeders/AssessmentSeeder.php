<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\TrainingModule;
use App\Models\Assessment;
use App\Models\QuestionBankItem;
use App\Models\QuestionBankOption;
use App\Models\User;

class AssessmentSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::where('role', 'admin')->first();
        $module = TrainingModule::first();

        if (!$module) return;

        // Cleanup existing questions for this module to avoid duplication during re-seed
        QuestionBankItem::where('module_id', $module->id)->delete();

        // Create Assessments if not exist
        $pretest = Assessment::updateOrCreate(
            ['module_id' => $module->id, 'type' => \App\Enums\AssessmentType::PRETEST->value],
            [
                'title' => 'Pre-Test: ' . $module->title,
                'description' => 'Initial neural evaluation for ' . $module->title,
                'passing_score' => 70,
                'number_of_questions_to_take' => 3,
                'status' => \App\Enums\AssessmentStatus::ACTIVE->value,
                'created_by' => 1,
            ]
        );

        $posttest = Assessment::updateOrCreate(
            ['module_id' => $module->id, 'type' => \App\Enums\AssessmentType::POSTTEST->value],
            [
                'title' => 'Post-Test: ' . $module->title,
                'description' => 'Final mastery verification for ' . $module->title,
                'passing_score' => 85,
                'number_of_questions_to_take' => 5,
                'status' => \App\Enums\AssessmentStatus::ACTIVE->value,
                'created_by' => 1,
            ]
        );

        // Create varied sample questions
        $sampleQuestions = [
            [
                'text' => 'What is the required ISO class for a primary engineering control (PEC) in a sterile compounding area?',
                'options' => [
                    'ISO Class 5' => true,
                    'ISO Class 7' => false,
                    'ISO Class 8' => false,
                    'ISO Class 9' => false,
                ],
                'explanation' => 'ISO Class 5 is the environment required for the PEC where sterile manipulations occur.',
                'scope' => \App\Enums\QuestionUsageScope::BOTH,
            ],
            [
                'text' => 'Which of the following is the correct order for donning PPE (garbing) before entering the cleanroom?',
                'options' => [
                    'Shoe covers, Head/Facial covers, Gown, Gloves' => true,
                    'Gloves, Gown, Shoe covers, Head covers' => false,
                    'Gown, Shoe covers, Gloves, Head covers' => false,
                    'Shoe covers, Gloves, Gown, Head covers' => false,
                ],
                'explanation' => 'Garbing starts from "dirtiest" to "cleanest" areas (peripheral to central).',
                'scope' => \App\Enums\QuestionUsageScope::PRETEST,
            ],
            [
                'text' => 'How long must a laminar airflow workbench (LAFW) run before it can be used for sterile compounding?',
                'options' => [
                    '30 minutes' => true,
                    '10 minutes' => false,
                    '60 minutes' => false,
                    '5 minutes' => false,
                ],
                'explanation' => 'The hood must run for at least 30 minutes to ensure a stable sterile environment.',
                'scope' => \App\Enums\QuestionUsageScope::POSTTEST,
            ],
            [
                'text' => 'What should be used to disinfect the work surface of a LAFW before compounding?',
                'options' => [
                    '70% Isopropyl Alcohol' => true,
                    'Bleach solution' => false,
                    'Hydrogen Peroxide' => false,
                    'Distilled Water' => false,
                ],
                'explanation' => '70% IPA is the standard for surface disinfection in sterile environments.',
                'scope' => \App\Enums\QuestionUsageScope::BOTH,
            ],
            [
                'text' => 'Where should a sterile needle be opened within the horizontal laminar flow hood?',
                'options' => [
                    'At least 6 inches inside the hood' => true,
                    'Right at the edge of the hood' => false,
                    'Outside the hood' => false,
                    '1 inch from the HEPA filter' => false,
                ],
                'explanation' => 'Sterile work must be done at least 6 inches from the front opening.',
                'scope' => \App\Enums\QuestionUsageScope::BOTH,
            ],
            [
                'text' => 'Which component of a syringe must never be touched to maintain sterility?',
                'options' => [
                    'The plunger rod' => true,
                    'The finger flange' => false,
                    'The barrel outside' => false,
                    'The measurement markings' => false,
                ],
                'explanation' => 'The plunger rod (the "ribs") is a critical site that must not be touched.',
                'scope' => \App\Enums\QuestionUsageScope::POSTTEST,
            ]
        ];

        foreach ($sampleQuestions as $qData) {
            $question = QuestionBankItem::create([
                'module_id' => $module->id,
                'question_text' => $qData['text'],
                'usage_scope' => $qData['scope']->value,
                'is_active' => true,
                'difficulty' => 'Critical',
                'explanation' => $qData['explanation'],
                'created_by' => 1,
            ]);

            $idx = 0;
            foreach ($qData['options'] as $text => $isCorrect) {
                QuestionBankOption::create([
                    'question_bank_item_id' => $question->id,
                    'option_text' => $text,
                    'is_correct' => $isCorrect,
                    'sort_order' => $idx + 1,
                    'option_key' => chr(65 + $idx),
                ]);
                $idx++;
            }
        }
    }
}
