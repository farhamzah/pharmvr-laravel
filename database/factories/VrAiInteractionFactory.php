<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\VrAiInteraction>
 */
class VrAiInteractionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $types = ['hint', 'reminder', 'feedback'];
        $type = $this->faker->randomElement($types);

        $scenarios = [
            'hint' => [
                'output_text' => 'Gunakan sanitizer sebelum memegang sarung tangan.',
                'display_text' => 'Sanitasi tangan Anda.',
                'speech_text' => 'Gunakan sanitizer di atas meja untuk membersihkan tangan Anda sebelum mengambil sarung tangan steril.',
                'severity' => 'info',
            ],
            'reminder' => [
                'output_text' => 'Pastikan masker menutup hidung dengan sempurna.',
                'display_text' => 'Cek posisi masker.',
                'speech_text' => 'Ingat untuk selalu memeriksa posisi masker Anda. Masker harus menutup hidung dan mulut sepenuhnya.',
                'severity' => 'info',
            ],
            'feedback' => [
                'output_text' => 'Hati-hati! Tangan Anda menyentuh area non-steril.',
                'display_text' => 'Pelanggaran Sterilitas!',
                'speech_text' => 'Waspada, tangan Anda baru saja menyentuh bagian luar wadah yang tidak steril. Segera lakukan sanitasi ulang.',
                'severity' => 'warning',
            ],
        ];

        $scenario = $scenarios[$type];

        return [
            'user_id' => \App\Models\User::factory(),
            'vr_session_id' => \App\Models\VrSession::factory(),
            'hint_type' => $type,
            'trigger_event_type' => $this->faker->word(),
            'input_context' => ['step' => 'gowning', 'module' => 'sterile-basics'],
            'output_text' => $scenario['output_text'],
            'display_text' => $scenario['display_text'],
            'speech_text' => $scenario['speech_text'],
            'severity' => $scenario['severity'],
            'recommended_next_action' => 'Lanjutkan ke langkah berikutnya sesuai panduan.',
            'metadata' => [
                'tokens' => rand(30, 100),
                'latency_ms' => rand(200, 1200),
            ],
        ];
    }
}
