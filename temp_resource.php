<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Services\AssetUrlService;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'    => $this->id,
            'name'  => $this->name,
            'email' => $this->email,
            'role'  => $this->role,
            'profile' => [
                'phone'      => $this->profile?->phone,
                'avatar_url' => AssetUrlService::resolve($this->profile?->avatar_url),
                'bio'        => $this->profile?->bio,
                'university' => $this->profile?->university,
                'semester'   => (int) $this->profile?->semester,
                'nim'        => $this->profile?->nim,
                'institution'=> $this->profile?->university, // For backward compatibility if needed
            ],
            'preferences' => $this->preferences?->settings ?? (object)[],
            'created_at'  => $this->created_at,
        ];
    }
}
