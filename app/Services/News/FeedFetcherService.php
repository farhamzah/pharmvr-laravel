<?php

namespace App\Services\News;

use App\Models\News;
use App\Models\NewsSource;
use App\Models\NewsSyncLog;
use App\Services\News\Parsers\GenericRssParser;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class FeedFetcherService
{
    public function __construct(
        protected NewsDeduplicatorService $deduplicator,
        protected NewsAiCurationService $aiCuration,
        protected GenericRssParser $genericParser
    ) {
    }

    public function syncAllSources(bool $force = false): void
    {
        $sources = NewsSource::where('is_active', true)->get();

        foreach ($sources as $source) {
            /** @var NewsSource $source */
            $this->syncSource($source, $force);
        }
    }

    public function syncSource(NewsSource $source, bool $force = false): NewsSyncLog
    {
        if (!$force && $source->last_synced_at) {
            $hoursSinceLastSync = $source->last_synced_at->diffInHours(now());
            if ($hoursSinceLastSync < $source->sync_interval_hours) {
                return new NewsSyncLog(['status' => 'success', 'error_message' => 'Skipped: Interval not reached']);
            }
        }

        $log = NewsSyncLog::create([
            'news_source_id' => $source->id,
            'status' => 'running',
        ]);

        try {
            $response = Http::withHeaders([
                'User-Agent' => 'PharmVR/1.0 (News Aggregator)'
            ])->timeout(20)->get($source->feed_url);

            if (!$response->successful()) {
                throw new \Exception("HTTP Error: " . $response->status());
            }

            $parserClass = $source->parser_class ? new ($source->parser_class)() : $this->genericParser;
            if (!($parserClass instanceof \App\Services\News\Parsers\SourceParserInterface)) {
                throw new \Exception("Invalid parser class");
            }

            $articles = $parserClass->parse($response->body(), $source);
            
            $log->articles_fetched = count($articles);

            $newCount = 0;
            $skippedCount = 0;
            $failedCount = 0;

            foreach ($articles as $article) {
                if ($this->deduplicator->isDuplicate($article)) {
                    $skippedCount++;
                    continue; 
                }

                $aiData = $this->aiCuration->curate($article);

                if (!$aiData) {
                    $failedCount++;
                    continue;
                }

                if (($aiData['relevance_score'] ?? 0) < $source->min_relevance_score) {
                    $skippedCount++; 
                    continue;
                }

                News::create([
                    'title'           => $article->title,
                    'slug'            => Str::slug($article->title) . '-' . uniqid(),
                    'content'         => '<i>This is an external curated news article. Please see the AI Summary or visit the original source.</i>',
                    'summary'         => Str::limit($article->snippet, 200),
                    'image_url'       => $article->imageUrl,
                    'category'        => 'News', 
                    'published_at'    => $article->publishedAt,
                    'is_active'       => true,
                    'content_type'    => 'external',
                    'news_source_id'  => $source->id,
                    'original_url'    => $article->originalUrl,
                    'author'          => $article->author,
                    'source_name'     => $source->name,
                    'ai_summary'      => $aiData['ai_summary'] ?? null,
                    'ai_tags'         => $aiData['ai_tags'] ?? null,
                    'topic_category'  => $aiData['topic_category'] ?? null,
                    'relevance_score' => $aiData['relevance_score'] ?? null,
                    'content_hash'    => $this->deduplicator->generateHash($article),
                ]);

                $newCount++;
            }

            $log->update([
                'completed_at' => now(),
                'status' => $failedCount > 0 ? 'partial' : 'success',
                'articles_new' => $newCount,
                'articles_skipped' => $skippedCount,
                'articles_failed' => $failedCount,
            ]);

            $source->update([
                'last_synced_at' => now(),
                'last_sync_status' => $failedCount > 0 ? 'partial' : 'success',
                'articles_synced_count' => $source->articles_synced_count + $newCount,
            ]);

        } catch (\Exception $e) {
            Log::error("Failed to sync feed for {$source->name}: " . $e->getMessage());
            $log->update([
                'completed_at' => now(),
                'status' => 'failed',
                'error_message' => $e->getMessage(),
            ]);
            $source->update([
                'last_synced_at' => now(),
                'last_sync_status' => 'failed',
            ]);
        }

        return $log;
    }
}
