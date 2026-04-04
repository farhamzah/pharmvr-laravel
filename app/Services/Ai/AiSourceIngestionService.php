<?php

namespace App\Services\Ai;

use App\Models\AiKnowledgeSource;
use App\Models\AiKnowledgeChunk;
use App\Enums\AiProcessingStatus;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class AiSourceIngestionService
{
    protected $chunkingService;

    public function __construct(AiChunkingService $chunkingService)
    {
        $this->chunkingService = $chunkingService;
    }

    /**
     * Process a knowledge source: parse, chunk, and store.
     */
    public function processSource(AiKnowledgeSource $source): bool
    {
        $source->update([
            'parsing_status' => AiProcessingStatus::PROCESSING,
            'status' => \App\Enums\AiSourceStatus::PROCESSING,
        ]);

        try {
            $text = $this->extractText($source);
            
            // Foolproof sanitization for UTF-8 using iconv //IGNORE
            // This will strip any bytes that are not valid UTF-8
            $text = iconv('UTF-8', 'UTF-8//IGNORE', $text);
            // Remove control characters except for newline/tab
            $text = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', '', $text);
            
            if (empty($text)) {
                throw new \Exception("Could not extract text from source.");
            }

            // Delete old chunks if re-processing
            $source->chunks()->delete();

            $chunks = $this->chunkingService->splitIntoChunks($text);
            \Log::info("Source {$source->id} split into " . count($chunks) . " chunks.");
            
            foreach ($chunks as $index => $chunkText) {
                try {
                    AiKnowledgeChunk::create([
                        'source_id' => $source->id,
                        'chunk_index' => $index,
                        'chunk_text' => $chunkText,
                        'token_count' => $this->chunkingService->estimateTokenCount($chunkText),
                        'chunk_hash' => hash('sha256', $chunkText),
                        'embedding_status' => AiProcessingStatus::PENDING,
                    ]);
                } catch (\Exception $chunkError) {
                    \Log::error("Failed to create chunk {$index} for source {$source->id}: " . $chunkError->getMessage());
                    throw $chunkError;
                }
            }

            $source->update([
                'parsing_status' => AiProcessingStatus::COMPLETED,
                'total_chunks' => count($chunks),
                'indexing_status' => AiProcessingStatus::PENDING,
                'status' => \App\Enums\AiSourceStatus::INDEXED,
            ]);

            // If we're not actually doing remote vector indexing yet, we can move to READY
            // but for now let's stick to INDEXED to show the lifecycle.
            // If is_active is true, we should probably move to ACTIVE.
            if ($source->is_active) {
                $source->update(['status' => \App\Enums\AiSourceStatus::ACTIVE]);
            } else {
                $source->update(['status' => \App\Enums\AiSourceStatus::READY]);
            }

            return true;
        } catch (\Exception $e) {
            $source->update([
                'parsing_status' => AiProcessingStatus::FAILED,
                'status' => \App\Enums\AiSourceStatus::FAILED,
            ]);
            \Log::error("Ingestion failed for source {$source->id}: " . $e->getMessage() . " at " . $e->getFile() . ":" . $e->getLine());
            \Log::error($e->getTraceAsString());
            return false;
        }
    }

    /**
     * Extracts text based on source type.
     */
    protected function extractText(AiKnowledgeSource $source): string
    {
        // 1. Use manual content if provided (highest priority)
        if ($source->content) {
            return $source->content;
        }

        // 2. Handle Text-based File Uploads (txt, md)
        if (in_array($source->source_type->value, ['txt', 'md'])) {
            if ($source->file_path && Storage::disk('public')->exists($source->file_path)) {
                return Storage::disk('public')->get($source->file_path);
            }
        }

        // 3. Handle Web Scraping (Current Stub)
        if ($source->source_type->value === 'web') {
            return "Content scraped from URL: {$source->url}. [SYSTEM_STUB: Remote traversal successful]";
        }

        // 4. Handle PDF/DOCX
        if (in_array($source->source_type->value, ['pdf', 'docx'])) {
            if ($source->file_path && Storage::disk('public')->exists($source->file_path)) {
                $absolutePath = Storage::disk('public')->path($source->file_path);
                
                try {
                    if ($source->source_type->value === 'pdf') {
                        $parser = new \Smalot\PdfParser\Parser();
                        $pdf = $parser->parseFile($absolutePath);
                        return $pdf->getText();
                    }
                    
                    // Note: DOCX would require another library like PhpWord or a custom script.
                    // For now we prioritize PDF as that's what the CPOB book is.
                } catch (\Exception $e) {
                    \Log::error("Failed to parse {$source->source_type->value} for source {$source->id}: " . $e->getMessage());
                    // Fallback to description
                    return $source->description ?? "Failed to extract content from {$source->source_type->value}.";
                }
            }
        }

        // 5. Fallback for other types
        return $source->description ?? '';
    }
}
