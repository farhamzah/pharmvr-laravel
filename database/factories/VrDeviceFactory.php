<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\VrDevice;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;

class VrDeviceFactory extends Factory
{
    protected $model = VrDevice::class;

    public function definition()
    {
        return [
            'user_id' => User::factory(),
            'device_type' => 'meta_quest_3',
            'device_name' => $this->faker->firstName . "'s Quest 3",
            'headset_identifier' => $this->faker->unique()->bothify('??-####-####'),
            'platform_name' => 'Android (Meta Quest OS)',
            'app_version' => '1.0.' . $this->faker->randomDigit(),
            'device_token_hash' => Hash::make('token_123'),
            'status' => 'active',
            'last_seen_at' => now(),
        ];
    }

    public function inactive()
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'inactive',
        ]);
    }

    public function disconnected()
    {
        return $this->state(fn (array $attributes) => [
            'last_seen_at' => now()->subDays(5),
        ]);
    }
}
