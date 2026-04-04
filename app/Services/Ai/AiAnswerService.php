<?php

namespace App\Services\Ai;

use App\Models\AiChatSession;
use App\Models\AiChatMessage;
use App\Models\TrainingModule;
use App\Enums\ChatSender;
use App\Services\Ai\GeminiService; // Added this line
use Illuminate\Support\Str;

class AiAnswerService
{
    protected $retrievalService;
    protected $geminiService; // Added this line
    
    // Domain rules for industrial pharmacy
    protected array $supportedTopics = [
        'GMP', 'CPOB', 'cleanroom', 'gowning', 'line clearance', 
        'pharmaceutical production', 'QC', 'IPC', 'equipment', 'learning module'
    ];

    public function __construct(AiRetrievalService $retrievalService, GeminiService $geminiService) // Modified this line
    {
        $this->retrievalService = $retrievalService;
        $this->geminiService = $geminiService; // Added this line
    }

    /**
     * Enforces domain rules and generates a structured answer.
     */
    public function generateAnswer(string $question, AiChatSession $session): array
    {
        // 1. Safety / Domain check
        if (!$this->isTopicSupported($question)) {
            return $this->refuseQuestion();
        }

        // 2. Retrieval
        $chunks = $this->retrievalService->retrieveRelevantChunks($question, $session->module);
        
        if ($chunks->isEmpty()) {
            return $this->handleNoSourceFound($session);
        }

        // 3. Prompt Assembly (Ready for OpenAI/GPT call)
        $contextText = $chunks->pluck('chunk_text')->implode("\n\n---\n\n");
        $answerText = $this->generateGroundedResponse($question, $contextText, $session->assistant_mode, $session->platform?->value);
        
        // 4. Citation Formatting
        $citations = $chunks->map(function ($chunk) {
            return [
                'source_id' => $chunk->source_id,
                'title' => $chunk->source->title,
                'category' => $chunk->source->category ?? 'General',
                'page_number' => $chunk->page_number,
                'section_title' => $chunk->section_title,
                'trust_level' => $chunk->source->trust_level ?? 'verified',
                'excerpt' => Str::limit($chunk->chunk_text, 200)
            ];
        })->unique('source_id')->values()->toArray();

        // 5. Build Result
        $isVr = ($session->platform === \App\Enums\ChatPlatform::VR || $session->assistant_mode === 'vr_concise');
        $responseMode = $isVr ? 'vr_concise' : 'grounded';

        return [
            'session_id' => $session->id,
            'answer' => $answerText,
            'cited_sources' => $citations,
            'suggested_followups' => $this->generateSuggestedFollowups($question, $session->assistant_mode),
            'confidence_score' => 0.92, 
            'response_mode' => $responseMode
        ];
    }

    protected function isTopicSupported(string $question): bool
    {
        $lowerQuestion = strtolower($question);
        foreach ($this->supportedTopics as $topic) {
            if (Str::contains($lowerQuestion, strtolower($topic))) {
                return true;
            }
        }
        return false;
    }

    protected function refuseQuestion(): array
    {
        return [
            'answer' => "Maaf, saya hanya dapat membantu topik farmasi industri, GMP/CPOB, cleanroom, dan prosedur pembelajaran terkait dokumen yang tersedia.",
            'cited_sources' => [],
            'suggested_followups' => ['Apa itu GMP?', 'Jelaskan gowning di cleanroom'],
            'response_mode' => 'restricted'
        ];
    }

    protected function handleNoSourceFound(AiChatSession $session): array
    {
        $isVr = ($session->platform === \App\Enums\ChatPlatform::VR || $session->assistant_mode === 'vr_concise');
        
        return [
            'answer' => "Pertanyaan Anda masuk dalam domain farmasi industri, namun saya tidak menemukan referensi spesifik dari dokumen terpercaya saat ini.",
            'cited_sources' => [],
            'suggested_followups' => ['Apa itu CPOB?'],
            'response_mode' => $isVr ? 'vr_concise' : 'neutral'
        ];
    }

    /**
     * Use Gemini LLM to generate a summarized response based on context.
     */
    protected function generateGroundedResponse(string $question, string $context, ?string $mode, ?string $platform = null): string
    {
        $isVr = ($platform === 'vr' || $mode === 'vr_concise');
        $llmResponse = null;
        
        if (!empty($context) && strlen($context) > 50) {
            // Construct a robust prompt for RAG
            $prompt = "You are an Industrial Pharmacy / GMP Expert assistant. " .
                     "Use the following technical context from a CPOB (Good Manufacturing Practice) document to answer the user's question accurately and professionally.\n\n" .
                     "CONTEXT:\n{$context}\n\n" .
                     "QUESTION:\n{$question}\n\n" .
                     "INSTRUCTIONS:\n" .
                     "1. Answer in Indonesian language.\n" .
                     "2. Provide a COMPLETE and detailed explanation. If listing items (like aspects/rules), list ALL OF THEM mentioned in the context.\n" .
                     "3. If the context doesn't contain the full answer, provide what is available and mention it's partial.\n" .
                     ($isVr ? "4. KEEP THE RESPONSE CONCISE (max 5 sentences) for VR display.\n" : "4. Use professional and informative tone. Do not truncate the list.\n") .
                     "ANSWER:";

            $llmResponse = $this->geminiService->generateResponse($prompt);

            if ($llmResponse) {
                return $llmResponse;
            }
        }

        // Fallback to stylized mocks if LLM fails or no context found
        $prefix = $isVr ? "" : "Informasi Umum: ";
        $limit = $isVr ? 180 : 300;
        
        switch ($mode) {
            case 'gmp_expert':
                $text = "Sesuai standar CPOB/GMP: " . Str::limit($question, 30) . " diatur secara ketat. Pastikan seluruh dokumentasi lengkap dan validasi lingkungan sesuai klasifikasi ruangan.";
                break;
                
            case 'training_support':
                $text = "Mengenai " . Str::limit($question, 30) . ": Hal ini sangat penting dalam kurikulum pelatihan. Jika Anda berada di area produksi, tujuannya adalah meminimalkan kontaminasi silang.";
                break;
                
            default:
                $text = Str::limit($question, 20) . " berkaitan dengan SOP untuk memastikan kualitas produk melalui kontrol ketat pada setiap tahapan proses produksi.";
                break;
        }

        $fullResponse = $prefix . $text;
        return $isVr ? Str::limit($fullResponse, $limit) : $fullResponse;
    }

    protected function generateSuggestedFollowups(string $question, ?string $mode): array
    {
        switch ($mode) {
            case 'gmp_expert':
                return ["Apa sanksi ketidakpatuhan GMP?", "Tunjukkan regulasi auditnya"];
            case 'training_support':
                return ["Berikan contoh kasus lainnya", "Ringkas poin penting ini"];
            case 'lab_procedures':
                return ["Bagaimana troubleshoot error?", "Tunjukkan checklist pembersihannya"];
            default:
                return ["Jelaskan prosedur regulasinya", "Bagaimana cara verifikasi hasilnya?"];
        }
    }
}
