<?php

namespace App\Services\News\Parsers;

use App\Models\NewsSource;
use App\Services\News\Dto\ParsedArticle;

interface SourceParserInterface
{
    /**
     * Parse raw feed content into an array of ParsedArticle DTOs.
     *
     * @param string $feedContent
     * @param NewsSource $source
     * @return ParsedArticle[]
     */
    public function parse(string $feedContent, NewsSource $source): array;
}
