<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\News>
 */
class NewsFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $pharmThemes = [
            'Optimasi Formulasi Tablet Salut Selaput',
            'Validasi Pembersihan Lini Produksi Sirup',
            'Manajemen Rantai Dingin (Cold Chain) untuk Produk Biologi',
            'Penerapan PAT (Process Analytical Technology) pada Granulasi',
            'Integritas Data Digital dalam Pembuatan Batch Record',
            'Kualifikasi Kinerja Sistem Tata Udara (HVAC) Kelas C',
            'Studi Stabilitas Jangka Panjang Sediaan Parenteral',
            'Deteksi Kontaminasi Partikulat pada Injeksi Volume Besar'
        ];
        $title = $this->faker->randomElement($pharmThemes) . ' (' . $this->faker->word() . ')';
        return [
            'title'        => $title,
            'slug'         => \Illuminate\Support\Str::slug($title),
            'summary'      => 'Pembahasan mengenai ' . strtolower($title) . ' untuk meningkatkan standar kualitas industri.',
            'content'      => $this->faker->paragraphs(4, true),
            'image_url'    => 'https://picsum.photos/seed/' . rand(1, 1000) . '/800/600',
            'category'     => $this->faker->randomElement(['Production', 'Quality Control', 'Regulation', 'R&D']),
            'published_at' => $this->faker->dateTimeBetween('-1 month', 'now'),
            'is_active'    => true,
            'is_featured'  => $this->faker->boolean(30),
        ];
    }
}
