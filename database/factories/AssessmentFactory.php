<?php

namespace Database\Factories;

use App\Enums\AssessmentType;
use App\Enums\AssessmentStatus;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Assessment>
 */
class AssessmentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'module_id' => \App\Models\TrainingModule::factory(),
            'type'               => $this->faker->randomElement([
                AssessmentType::PRETEST->value,
                AssessmentType::POSTTEST->value,
            ]),
            'title'              => $this->faker->sentence(4),
            'description'        => $this->faker->paragraph(2),
            'status'             => AssessmentStatus::ACTIVE->value,
            'number_of_questions_to_take' => 10,
            'passing_score'      => 80,
            'time_limit_minutes' => 15,
        ];
    }
}
