<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use App\Models\News;
use App\Models\NewsSource;

class DummyCategorySeeder extends Seeder
{
    public function run(): void
    {
        $source = NewsSource::firstOrCreate(
            ['slug' => 'pharmaphorum'],
            ['name' => 'Pharmaphorum', 'feed_url' => 'http', 'is_active' => true]
        );

        $articles = [
            [
                'title' => 'FDA Clears First VR Simulator for Cleanroom Operations',
                'topic_category' => 'VR/XR',
                'ai_summary' => 'Badan Pengawas Obat dan Makanan (FDA) baru saja memberikan izin pada platform VR pertama untuk pelatihan Cleanroom Kelas A/B. Modul ini diyakini akan mempercepat onboarding staf produksi.',
                'image_url' => 'https://images.unsplash.com/photo-1622979135225-d2ba269cf1ac?ixlib=rb-4.0.3&auto=format&fit=crop&w=1470&q=80',
                'ai_tags' => ['VR', 'Training', 'FDA', 'Sterile'],
            ],
            [
                'title' => 'How Machine Learning is Shaping Pharmacy Benefits',
                'topic_category' => 'AI',
                'ai_summary' => 'Algoritma machine learning terbaru mampu mendeteksi anomali pada klaim asuransi farmasi dengan akurasi 98%. AI memprediksi efektivitas obat untuk menurunkan beban biaya kesehatan nasional.',
                'image_url' => 'https://images.unsplash.com/photo-1677442136019-21780ecad995?auto=format&fit=crop&q=80&w=1470',
                'ai_tags' => ['AI', 'Insurance', 'Cost Savings'],
            ],
            [
                'title' => 'Telepharmacy Regulations Updated for 2026',
                'topic_category' => 'Digital Health',
                'ai_summary' => 'Pemerintah menerbitkan panduan komprehensif terkait operasional Apotek Digital. Aturan ini memuat proteksi data resep elektronik, konsultasi jarak jauh, dan logistik pengiriman obat terpusat.',
                'image_url' => 'https://images.unsplash.com/photo-1542884748-2b87b36c6b90?auto=format&fit=crop&q=80&w=1470',
                'ai_tags' => ['Telepharmacy', 'Regulation', 'e-Prescription'],
            ]
        ];

        foreach ($articles as $index => $article) {
            News::firstOrCreate(
                ['title' => $article['title']],
                [
                    'slug' => Str::slug($article['title']) . '-' . uniqid(),
                    'summary' => Str::limit($article['ai_summary'], 100),
                    'content' => '<i>This is a curated external article. See summary for details.</i>',
                    'image_url' => $article['image_url'],
                    'category' => 'News',
                    'published_at' => now()->subHours($index + 1),
                    'is_active' => true,
                    'is_featured' => false,
                    'content_type' => 'external',
                    'news_source_id' => $source->id,
                    'original_url' => 'https://pharmaphorum.com',
                    'source_name' => 'Pharmaphorum',
                    'ai_summary' => $article['ai_summary'],
                    'ai_tags' => $article['ai_tags'],
                    'topic_category' => $article['topic_category'],
                    'relevance_score' => 85 + $index,
                    'content_hash' => md5($article['title'])
                ]
            );
        }
    }
}
