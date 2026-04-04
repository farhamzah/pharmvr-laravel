<?php

namespace Database\Factories;

use App\Models\VrDevice;
use App\Models\VrSession;
use App\Models\TrainingModule;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class VrSessionFactory extends Factory
{
    protected $model = VrSession::class;

    public function definition()
    {
        $startedAt = $this->faker->dateTimeBetween('-1 day', 'now');
        
        return [
            'user_id' => User::factory(),
            'device_id' => VrDevice::factory(),
            'training_module_id' => TrainingModule::factory(),
            'session_status' => $this->faker->randomElement(['starting', 'playing', 'playing', 'completed', 'interrupted']),
            'progress_percentage' => $this->faker->numberBetween(0, 100),
            'started_at' => $startedAt,
            'last_activity_at' => now(),
            'current_step' => $this->faker->randomElement(['intro', 'sterile_gowning', 'room_entry', 'vial_inspection']),
        ];
    }

    public function playing()
    {
        return $this->state(fn (array $attributes) => [
            'session_status' => 'playing',
            'progress_percentage' => $this->faker->numberBetween(10, 90),
        ]);
    }

    public function completed()
    {
        return $this->state(fn (array $attributes) => [
            'session_status' => 'completed',
            'progress_percentage' => 100,
            'completed_at' => now(),
            'summary_json' => [
                'final_score' => $this->faker->numberBetween(70, 100),
                'total_time' => $this->faker->numberBetween(300, 1200),
                'errors_counted' => $this->faker->numberBetween(0, 5),
            ],
        ]);
    }

    public function interrupted()
    {
        return $this->state(fn (array $attributes) => [
            'session_status' => 'interrupted',
            'interrupted_at' => now(),
            'summary_json' => [
                'reason' => 'headset_removed',
                'error_code' => 'ACCEL_WAKE',
            ],
        ]);
    }
}
