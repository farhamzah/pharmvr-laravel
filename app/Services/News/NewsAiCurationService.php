<?php

namespace App\Services\News;

use App\Services\Ai\GeminiService;
use App\Services\News\Dto\ParsedArticle;
use Illuminate\Support\Facades\Log;

class NewsAiCurationService
{
    public function __construct(protected GeminiService $gemini)
    {
    }

    /**
     * @return array|null Returns structure matching JSON prompt or null on failure.
     */
    public function curate(ParsedArticle $article): ?array
    {
        $prompt = "You are an expert content curator for a pharmaceutical and healthcare VR platform called 'PharmVR'.\n\n";
        $prompt .= "Analyze this news article:\n";
        $prompt .= "Title: " . $article->title . "\n";
        $prompt .= "Snippet: " . $article->snippet . "\n";
        $prompt .= "Source: " . $article->sourceName . "\n\n";
        $prompt .= "Tasks:\n";
        $prompt .= "1. RELEVANCE: Score 0-100 how relevant this is to: AI, VR/XR, pharmacy, pharmaceutical manufacturing, GMP/CPOB, drug discovery, pharmacy education, digital health, simulation training, immersive learning.\n";
        $prompt .= "2. CATEGORY: Pick exactly one: 'AI', 'VR/XR', 'Pharma Industry', 'GMP/CPOB', 'Pharmacy Education', 'Drug Discovery', 'Digital Health', 'General Health'\n";
        $prompt .= "3. SUMMARY: Write a 60-120 word engaging summary **in fluent Bahasa Indonesia**. Do NOT just translate the text blindly, paraphrase it well so it's interesting for pharmaceutical students/professionals.\n";
        $prompt .= "4. TAGS: Generate 3-5 lowercase tags in English or Indonesian (e.g., 'vr', 'pharma', 'biotech').\n\n";
        $prompt .= "Return ONLY valid JSON format with no markdown wrappers:\n";
        $prompt .= '{"relevance_score": 75, "topic_category": "Drug Discovery", "ai_summary": "...", "ai_tags": ["tag1","tag2"]}';

        $response = $this->gemini->generateResponse($prompt);

        if (!$response) {
            Log::warning("AI curation returned null for article: {$article->title}");
            return null;
        }

        $jsonString = trim($response);
        if (str_starts_with($jsonString, '```json')) {
            $jsonString = substr($jsonString, 7);
            if (str_ends_with($jsonString, '```')) {
                $jsonString = substr($jsonString, 0, -3);
            }
        }
        $jsonString = trim($jsonString, "` \n\r\t");

        $data = json_decode($jsonString, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            Log::error("Failed to parse AI curation JSON. Raw: {$response}");
            return null;
        }

        return $data;
    }
}
