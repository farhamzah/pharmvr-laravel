<?php

namespace App\Http\Resources\Api\V1\Content;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TrainingModuleResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'                 => $this->id,
            'title'              => $this->title,
            'slug'               => $this->slug,
            'description'        => $this->description,
            'cover_image_url'    => $this->cover_image_url,
            'difficulty'         => $this->difficulty,
            'estimated_duration' => $this->estimated_duration,
            'status'             => $this->whenLoaded('userProgress', function() {
                return $this->userProgress->first()?->status ?? 'available';
            }, 'available'),
            'completion'         => $this->whenLoaded('userProgress', function() {
                return $this->userProgress->first()?->completion_percentage ?? 0;
            }, 0),
        ];
    }
}
