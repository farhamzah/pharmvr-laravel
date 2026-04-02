<?php

namespace App\Services\Ai\Providers;

use App\Services\Ai\Dto\AiResponse;

class GeminiProvider implements AiProviderInterface
{
    protected string $apiKey;
    protected string $model;

    public function __construct(string $apiKey, string $model = 'gemini-pro')
    {
        $this->apiKey = $apiKey;
        $this->model = $model;
    }

    public function generateResponse(array $messages, array $options = []): AiResponse
    {
        // Placeholder for real Gemini API call
        
        return new AiResponse(
            "Gemini Response Placeholder (Active Model: {$this->model})",
            'assistant',
            ['provider' => 'gemini', 'model' => $this->model]
        );
    }
}
