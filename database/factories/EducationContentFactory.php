<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\EducationContent>
 */
class EducationContentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $pharmTopics = [
            'Prinsip CPOB 2024',
            'Teknik Aseptis di LAF/BSC',
            'Kalibrasi pH Meter & Spektrofotometer',
            'SOP Operasional Autoklaf',
            'Manajemen Logistik Farmasi (CDOB)',
            'Pencampuran Sediaan Sitostatika',
            'Metode Angka Lempeng Total (ALT)',
            'Kualifikasi Instalasi Pengolahan Air (PW/WFI)'
        ];

        $type = $this->faker->randomElement(['module', 'video', 'document']);
        $code = 'PH-' . $this->faker->unique()->numberBetween(1000, 9999);
        $title = $this->faker->randomElement($pharmTopics) . " [" . $code . "] (" . ucfirst($type) . ")";
        
        return [
            'code'             => $code,
            'title'            => $title,
            'slug'             => \Illuminate\Support\Str::slug($title),
            'type'             => $type,
            'category'         => $this->faker->randomElement(['CPOB', 'QC', 'QA', 'Logistik', 'Produksi']),
            'related_topic'    => $this->faker->words(2, true),
            'level'            => $this->faker->randomElement(['Beginner', 'Intermediate', 'Advanced']),
            'tags'             => [$this->faker->word(), $this->faker->word()],
            'description'      => 'Materi edukasi mengenai ' . $title . ' untuk tenaga profesional farmasi.',
            'thumbnail_url'    => 'https://picsum.photos/seed/' . rand(1, 1000) . '/400/300',
            'file_url'         => $type === 'document' ? 'https://example.com/sample.pdf' : null,
            'file_type'        => $type === 'document' ? 'PDF' : null,
            'video_id'         => $type === 'video' ? 'dQw4w9WgXcQ' : null,
            'platform'         => $type === 'video' ? 'youtube' : null,
            'duration_minutes' => $type !== 'document' ? $this->faker->numberBetween(10, 90) : null,
            'pages_count'      => $type === 'document' ? $this->faker->numberBetween(10, 50) : null,
            'is_active'        => true,
            'learning_path'    => $type === 'module' ? [
                'has_pre_test'  => true,
                'has_vr_sim'    => $this->faker->boolean(60),
                'has_post_test' => true,
            ] : null,
        ];
    }
}
