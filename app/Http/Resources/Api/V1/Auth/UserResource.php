<?php

namespace App\Http\Resources\Api\V1\Auth;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

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
            'can_bypass_prerequisites' => $this->can_bypass_prerequisites,
            'profile' => $this->whenLoaded('profile', function () {
                return [
                    'first_name' => $this->profile->first_name,
                    'last_name'  => $this->profile->last_name,
                    'phone'      => $this->profile->phone,
                    'avatar_url' => \App\Services\AssetUrlService::resolve($this->profile->avatar_url),
                    'bio'        => $this->profile->bio,
                    'birth_date' => $this->profile->birth_date,
                    'gender'     => $this->profile->gender,
                    'institution' => $this->profile->institution,
                    'university'  => $this->profile->university,
                    'semester'    => $this->profile->semester,
                    'nim'         => $this->profile->nim,
                ];
            }),
            'preferences' => $this->whenLoaded('preferences', function () {
                return (object) ($this->preferences->settings ?? []);
            }),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
