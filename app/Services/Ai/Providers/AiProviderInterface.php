<?php

namespace App\Services\Ai\Providers;

use App\Services\Ai\Dto\AiResponse;

interface AiProviderInterface
{
    /**
     * Generate response from the AI provider.
     * 
     * @param array $messages Standardized message array [['role' => '...', 'content' => '...']]
     * @param array $options Additional options (model, temperature, etc.)
     * @return AiResponse
     */
    public function generateResponse(array $messages, array $options = []): AiResponse;
}
