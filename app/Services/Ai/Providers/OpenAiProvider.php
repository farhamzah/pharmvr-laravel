<?php

namespace App\Services\Ai\Providers;

use App\Services\Ai\Dto\AiResponse;
use Illuminate\Support\Facades\Http;

class OpenAiProvider implements AiProviderInterface
{
    protected string $apiKey;
    protected string $model;

    public function __construct(string $apiKey, string $model = 'gpt-3.5-turbo')
    {
        $this->apiKey = $apiKey;
        $this->model = $model;
    }

    public function generateResponse(array $messages, array $options = []): AiResponse
    {
        // Placeholder for real OpenAI API call
        // In a real implementation, we would use Http::post() here.
        
        return new AiResponse(
            "OpenAI Response Placeholder (Active Model: {$this->model})",
            'assistant',
            ['provider' => 'openai', 'model' => $this->model]
        );
    }
}
