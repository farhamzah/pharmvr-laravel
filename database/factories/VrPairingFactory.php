<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\VrPairing;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;

class VrPairingFactory extends Factory
{
    protected $model = VrPairing::class;

    public function definition()
    {
        return [
            'user_id' => User::factory(),
            'status' => 'pending',
            'pairing_code_hash' => Hash::make('123456'),
            'pairing_token_hash' => Hash::make($this->faker->uuid),
            'expires_at' => now()->addMinutes(10),
        ];
    }

    public function confirmed()
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'confirmed',
            'confirmed_at' => now(),
        ]);
    }

    public function expired()
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'expired',
            'expires_at' => now()->subMinutes(1),
        ]);
    }
}
