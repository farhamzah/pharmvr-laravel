<?php

namespace App\Services\Ai;

class AiChunkingService
{
    /**
     * Splits text into overlapping chunks for better semantic retrieval.
     */
    public function splitIntoChunks(string $text, int $chunkSize = 1000, int $overlap = 200): array
    {
        $chunks = [];
        $length = mb_strlen($text);
        
        if ($length <= $chunkSize) {
            return [$text];
        }

        $start = 0;
        while ($start < $length) {
            $chunk = mb_substr($text, $start, $chunkSize);
            $chunks[] = $chunk;
            
            $start += ($chunkSize - $overlap);
            
            // Avoid infinite loop if chunkSize <= overlap
            if ($chunkSize <= $overlap) {
                break;
            }
        }

        return $chunks;
    }

    /**
     * Estimate token count (rough approximation for now)
     */
    public function estimateTokenCount(string $text): int
    {
        // Simple word count * 1.3 as a rough proxy for tokens
        return ceil(str_word_count($text) * 1.3);
    }
}
