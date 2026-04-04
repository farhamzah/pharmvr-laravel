<?php

namespace App\Http\Resources\Api\V1\Assessment;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OptionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'           => $this->id,
            'option_label' => $this->option_key ?? '',
            'option_text'  => $this->option_text,
            // 'is_correct' is omitted for question delivery, only used in scoring logic
        ];
    }
}
