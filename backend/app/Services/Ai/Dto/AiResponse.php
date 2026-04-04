<?php

namespace App\Services\Ai\Dto;

class AiResponse
{
    public string $content;
    public string $role;
    public array $metadata;

    public function __construct(string $content, string $role = 'assistant', array $metadata = [])
    {
        $this->content = $content;
        $this->role = $role;
        $this->metadata = $metadata;
    }

    /**
     * Convert to array for easy response formatting.
     */
    public function toArray(): array
    {
        return [
            'content' => $this->content,
            'role' => $this->role,
            'metadata' => $this->metadata,
        ];
    }
}
