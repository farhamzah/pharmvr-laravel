<?php

namespace App\Services\Ai;

use App\Models\AiKnowledgeChunk;
use App\Models\AiKnowledgeSource;
use App\Models\TrainingModule;
use Illuminate\Database\Eloquent\Collection;

class AiRetrievalService
{
    /**
     * Retrieve relevant chunks based on a query.
     * Stubbed keyword search for now, ready for future Vector Search.
     */
    public function retrieveRelevantChunks(string $query, ?TrainingModule $module = null, int $limit = 10): Collection
    {
        // 1. Sanitize query: Remove punctuation and trim
        $cleanQuery = trim(preg_replace('/[[:punct:]]/', ' ', $query));
        
        if (empty($cleanQuery)) {
            return new Collection();
        }

        $queryBuilder = AiKnowledgeChunk::query()
            ->whereHas('source', function ($q) {
                $q->where('is_active', true);
            });

        // 2. Handle Module Scope + Global Knowledge
        if ($module) {
            $queryBuilder->whereHas('source', function ($q) use ($module) {
                $q->where(function ($sub) use ($module) {
                    $sub->where('module_id', $module->id)
                        ->orWhereNull('module_id');
                });
            });
        }

        // 3. Robust Search: Split into words for better matching
        $words = explode(' ', $cleanQuery);
        $words = array_filter($words, fn($w) => strlen($w) > 2); // Only words > 2 chars
        
        if (empty($words)) {
            // Fallback to literal search if all words were too short
            return $queryBuilder->where('chunk_text', 'like', "%{$cleanQuery}%")
                ->limit($limit)
                ->get();
        }

        $queryBuilder->where(function ($q) use ($words) {
            foreach ($words as $word) {
                $q->orWhere('chunk_text', 'like', "%{$word}%");
            }
        });

        return $queryBuilder->limit($limit)->get();
    }
}
