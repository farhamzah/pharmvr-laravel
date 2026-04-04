<?php

namespace Database\Factories;

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
            'training_module_id' => \App\Models\TrainingModule::factory(),
            'type'               => $this->faker->randomElement(['pre_test', 'post_test']),
            'title'              => $this->faker->sentence(4),
            'description'        => $this->faker->paragraph(2),
            'min_score'          => 80,
            'duration_minutes'   => 15,
            'is_active'          => true,
        ];
    }
}
