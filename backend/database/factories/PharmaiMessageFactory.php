<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\PharmaiMessage>
 */
class PharmaiMessageFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $interactions = [
            ['role' => 'user', 'content' => 'Apa syarat utama masuk ke ruang bersih Kelas B?'],
            ['role' => 'assistant', 'content' => 'Syarat utama meliputi penggunaan pakaian steril lengkap (gowning), mencuci tangan secara aseptik, dan memastikan tidak ada perhiasan atau kosmetik yang digunakan.'],
            ['role' => 'user', 'content' => 'Bagaimana cara menangani tumpahan bahan aktif di area produksi?'],
            ['role' => 'assistant', 'content' => 'Tumpahan harus segera diisolasi, dilaporkan kepada supervisor, dan dibersihkan menggunakan kit dekontaminasi sesuai Prosedur Operasi Standar (SOP) yang berlaku.'],
            ['role' => 'user', 'content' => 'Apa perbedaan antara kualifikasi kinerja (PQ) dan kualifikasi operasional (OQ)?'],
            ['role' => 'assistant', 'content' => 'OQ memverifikasi bahwa peralatan berfungsi sesuai desain pada seluruh rentang operasional, sedangkan PQ membuktikan bahwa peralatan bekerja secara konsisten untuk menghasilkan produk yang memenuhi spesifikasi dalam kondisi produksi nyata.'],
        ];

        $interaction = $this->faker->randomElement($interactions);

        return [
            'conversation_id' => \App\Models\PharmaiConversation::factory(),
            'role' => $interaction['role'],
            'content' => $interaction['content'],
            'metadata' => [
                'tokens' => rand(50, 200),
                'latency_ms' => rand(300, 1500),
            ],
        ];
    }
}
