<?php

namespace App\Services\News;

use App\Models\News;
use App\Services\News\Dto\ParsedArticle;

class NewsDeduplicatorService
{
    public function isDuplicate(ParsedArticle $article): bool
    {
        $hash = $this->generateHash($article);
        return News::where('content_hash', $hash)->exists();
    }

    public function generateHash(ParsedArticle $article): string
    {
        return hash('sha256', $article->originalUrl);
    }
}
