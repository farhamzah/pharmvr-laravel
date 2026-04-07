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
                'first_name'   => $this->profile?->first_name,
                'last_name'    => $this->profile?->last_name,
                'phone'        => $this->profile?->phone,
                'phone_number' => $this->profile?->phone, // Alias for consistency
                'avatar_url'   => AssetUrlService::resolve($this->profile?->avatar_url),
                'bio'          => $this->profile?->bio,
                'university'   => $this->profile?->university,
                'institution'  => $this->profile?->university, // For backward compatibility
                'semester'     => $this->profile?->semester ? (int) $this->profile->semester : null,
                'nim'          => $this->profile?->nim,
                'birth_date'   => $this->profile?->birth_date,
                'gender'       => $this->profile?->gender,
            ],
            'preferences' => $this->preferences?->settings ?? (object)[],
            'created_at'  => $this->created_at,
        ];
    }
}
