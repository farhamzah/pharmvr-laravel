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
            if (array_key_exists('full_name', $data)) {
                $parts = explode(' ', trim($data['full_name']));
                $profileData['first_name'] = $parts[0];
                $profileData['last_name'] = count($parts) > 1 ? implode(' ', array_slice($parts, 1)) : '';
            }
            
            // Map phone_number to phone
            if (array_key_exists('phone_number', $data)) {
                $profileData['phone'] = $data['phone_number'];
            } elseif (array_key_exists('phone', $data)) {
                $profileData['phone'] = $data['phone'];
            }

            // Map university/institution
            if (array_key_exists('university', $data)) {
                $profileData['university'] = $data['university'];
            }
            if (array_key_exists('institution', $data)) {
                $profileData['institution'] = $data['institution'];
                // Fallback for university if only institution is provided
                if (!isset($profileData['university'])) {
                    $profileData['university'] = $data['institution'];
                }
            }

            if (array_key_exists('semester', $data)) {
                $profileData['semester'] = $data['semester'];
            }
            if (array_key_exists('nim', $data)) {
                $profileData['nim'] = $data['nim'];
            }

            if (!empty($profileData)) {
                $user->profile()->updateOrCreate(
                    ['user_id' => $user->id],
                    $profileData
                );
            }

            // Handle Avatar Upload
            if (isset($data['avatar']) && $data['avatar'] instanceof \Illuminate\Http\UploadedFile) {
                $path = $data['avatar']->store('avatars', 'public');
                $user->profile()->updateOrCreate(
                    ['user_id' => $user->id],
                    ['avatar_url' => $path]
                );
            }

            return $user->refresh()->load('profile');
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
