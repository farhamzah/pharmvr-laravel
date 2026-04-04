<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class NewsSourceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('news_sources')->updateOrInsert(
            ['slug' => 'fiercepharma'],
            [
                'name' => 'FiercePharma',
                'feed_url' => 'https://www.fiercepharma.com/rss/xml',
                'website_url' => 'https://www.fiercepharma.com',
                'is_active' => true,
                'min_relevance_score' => 60,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('news_sources')->updateOrInsert(
            ['slug' => 'kff-health-news'],
            [
                'name' => 'KFF Health News',
                'feed_url' => 'https://kffhealthnews.org/topics/pharmaceuticals/feed/',
                'website_url' => 'https://kffhealthnews.org',
                'is_active' => true,
                'min_relevance_score' => 60,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('news_sources')->updateOrInsert(
            ['slug' => 'pharmaphorum'],
            [
                'name' => 'Pharmaphorum',
                'feed_url' => 'https://pharmaphorum.com/rssfeed/news-and-features',
                'website_url' => 'https://pharmaphorum.com',
                'is_active' => true,
                'min_relevance_score' => 60,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );
    }
}
