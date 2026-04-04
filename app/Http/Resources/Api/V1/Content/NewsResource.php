<?php

namespace App\Http\Resources\Api\V1\Content;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Services\AssetUrlService;

class NewsResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'content_type' => $this->content_type ?? 'internal',
            'title' => $this->title,
            'slug' => $this->slug,
            'summary' => $this->summary,
            'content' => $this->isExternal() ? null : $this->content,
            'image_url' => AssetUrlService::resolve($this->image_url),
            'category' => $this->category,
            'is_featured' => $this->is_featured,
            'is_pinned' => $this->is_pinned ?? false,
            'published_at' => $this->published_at?->toIso8601String(),
            'created_at' => $this->created_at?->toIso8601String(),
            'read_time' => $this->isExternal() ? 2 : max(1, ceil(str_word_count(strip_tags($this->content)) / 200)),
            'original_url' => $this->original_url,
            'source_name' => $this->source_name ?? $this->source?->name,
            'source_logo' => $this->source?->logo_url,
            'author' => $this->author,
            'ai_summary' => $this->ai_summary,
            'ai_tags' => $this->ai_tags,
            'topic_category' => $this->topic_category,
        ];
    }
}
