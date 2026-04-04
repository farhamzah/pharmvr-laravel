<?php

namespace Database\Factories;

use App\Models\TrainingModule;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class TrainingModuleFactory extends Factory
{
    protected $model = TrainingModule::class;

    public function definition()
    {
        $title = $this->faker->unique()->sentence(3);
        return [
            'title' => $title,
            'slug' => Str::slug($title),
            'description' => $this->faker->paragraph,
            'difficulty' => $this->faker->randomElement(['Beginner', 'Intermediate', 'Advanced']),
            'estimated_duration' => $this->faker->numberBetween(10, 60),
            'is_active' => true,
        ];
    }
}
