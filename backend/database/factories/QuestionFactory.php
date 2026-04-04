<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Question>
 */
class QuestionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'assessment_id' => \App\Models\Assessment::factory(),
            'question_text' => $this->faker->sentence(10) . '?',
            'image_url'     => null,
            'explanation'   => $this->faker->paragraph(2),
            'order'         => 0,
        ];
    }
}
