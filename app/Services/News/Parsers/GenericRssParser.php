<?php

namespace App\Services\News\Parsers;

use App\Models\NewsSource;
use App\Services\News\Dto\ParsedArticle;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

class GenericRssParser implements SourceParserInterface
{
    public function parse(string $feedContent, NewsSource $source): array
    {
        $articles = [];
        
        try {
            libxml_use_internal_errors(true);
            $xml = simplexml_load_string($feedContent, 'SimpleXMLElement', LIBXML_NOCDATA);
            
            if ($xml === false) {
                Log::warning("XML parsing failed for source: {$source->name}");
                return [];
            }

            $items = $xml->channel->item ?? [];
            if (count($items) === 0 && isset($xml->entry)) {
                $items = $xml->entry; // Atom fallback
            }

            foreach ($items as $item) {
                $title = (string) $item->title;
                $url = (string) ($item->link['href'] ?? $item->link);
                
                if (empty($title) || empty($url)) {
                    continue;
                }

                $snippet = (string) ($item->description ?? $item->summary ?? '');
                $snippet = strip_tags($snippet); 
                
                $author = (string) ($item->author ?? $item->creator ?? '');
                
                $pubDateStr = (string) ($item->pubDate ?? $item->published ?? $item->updated ?? '');
                $publishedAt = $pubDateStr ? Carbon::parse($pubDateStr) : Carbon::now();

                $imageUrl = null;
                if (isset($item->enclosure) && str_starts_with((string)$item->enclosure['type'], 'image/')) {
                    $imageUrl = (string)$item->enclosure['url'];
                }

                $articles[] = new ParsedArticle(
                    title: $title,
                    originalUrl: $url,
                    snippet: mb_substr(trim($snippet), 0, 1000),
                    author: $author ?: null,
                    imageUrl: $imageUrl,
                    publishedAt: $publishedAt,
                    sourceName: $source->name
                );
            }
        } catch (\Exception $e) {
            Log::error("Error parsing feed for {$source->name}: " . $e->getMessage());
        }

        return collect($articles)->unique('originalUrl')->values()->all();
    }
}
