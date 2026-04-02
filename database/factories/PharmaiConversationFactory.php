<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\PharmaiConversation>
 */
class PharmaiConversationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $titles = [
            'Dasar-dasar CPOB 2018',
            'Prosedur Gowning Kelas B',
            'Validasi Autoklaf & Sterilisasi',
            'Penanganan Penyimpangan (CAPA)',
            'Prinsip HVAC di Industri Farmasi',
            'Kebersihan Personal & Mikrobiologi',
            'Dokumentasi Bets & Alur Kerja',
        ];

        return [
            'user_id' => \App\Models\User::factory(),
            'title' => $this->faker->randomElement($titles),
            'status' => 'active',
            'last_message_at' => now(),
        ];
    }
}
