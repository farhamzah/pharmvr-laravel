<?php

namespace App\Services\News\Dto;

use Illuminate\Support\Carbon;

class ParsedArticle
{
    public function __construct(
        public readonly string $title,
        public readonly string $originalUrl,
        public readonly ?string $snippet,
        public readonly ?string $author,
        public readonly ?string $imageUrl,
        public readonly ?Carbon $publishedAt,
        public readonly ?string $sourceName,
        public readonly ?string $sourceCredit = null
    ) {}
}
