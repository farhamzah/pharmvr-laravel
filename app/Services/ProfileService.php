<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ProfileService
{
    /**
     * Update the authenticated user's profile.
     */
    public function updateProfile(User $user, array $data): User
    {
        return DB::transaction(function () use ($user, $data) {
            // Update user table
            $userData = [];
            if (isset($data['full_name'])) {
                $userData['name'] = $data['full_name'];
            }
            if (isset($data['email'])) {
                $userData['email'] = $data['email'];
            }

            if (!empty($userData)) {
                $user->update($userData);
            }

            // Update profile table
            $profileData = [];
            if (isset($data['phone_number'])) {
                $profileData['phone'] = $data['phone_number'];
            }
            foreach (['university', 'semester', 'nim'] as $field) {
                if (isset($data[$field])) {
                    $profileData[$field] = $data[$field];
                }
            }

            if (!empty($profileData)) {
                $user->profile()->update($profileData);
            }

            // Handle Avatar Upload
            if (isset($data['avatar']) && $data['avatar'] instanceof \Illuminate\Http\UploadedFile) {
                $path = $data['avatar']->store('avatars', 'public');
                $user->profile()->update([
                    'avatar_url' => $path
                ]);
            }

            return $user->load('profile');
        });
    }

    /**
     * Change the authenticated user's password.
     */
    public function changePassword(User $user, array $data): void
    {
        // Securely verify current password
        if (!Hash::check($data['current_password'], $user->password)) {
            throw ValidationException::withMessages([
                'current_password' => ['Kata sandi saat ini tidak cocok.'],
            ]);
        }

        // Hash and save new password
        $user->forceFill([
            'password' => Hash::make($data['password']),
        ])->save();
    }
}
