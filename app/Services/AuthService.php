<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Password;

class AuthService
{
    /**
     * Register a new user and generate access token.
     */
    public function register(array $data): array
    {
        return DB::transaction(function () use ($data) {
            $user = User::create([
                'name'     => $data['full_name'],
                'email'    => $data['email'],
                'password' => Hash::make($data['password']),
                'role'     => 'user',
            ]);

            // Auto-create profile and preferences
            $user->profile()->create([
                'institution' => $data['institution'] ?? null,
            ]);
            $user->preferences()->create([
                'settings' => [] // Default empty settings
            ]);

            $token = $user->createToken('auth_token')->plainTextToken;

            return [
                'user'  => $user,
                'token' => $token,
            ];
        });
    }

    /**
     * Attempt login and generate access token.
     */
    public function login(array $credentials): array
    {
        $user = User::where('email', $credentials['email'])->first();

        if (!$user || !Hash::check($credentials['password'], $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        $tokenName = $credentials['device_name'] ?? 'auth_token';
        $token = $user->createToken($tokenName)->plainTextToken;

        return [
            'user'  => $user,
            'token' => $token,
        ];
    }

    /**
     * Send password reset link.
     */
    public function forgotPassword(array $data): string
    {
        $status = Password::sendResetLink($data);

        if ($status !== Password::RESET_LINK_SENT) {
            throw ValidationException::withMessages([
                'email' => [__($status)],
            ]);
        }

        return __($status);
    }

    /**
     * Reset user password.
     */
    public function resetPassword(array $data): string
    {
        $status = Password::reset($data, function ($user, $password) {
            $user->forceFill([
                'password' => Hash::make($password)
            ])->setRememberToken(Str::random(60))->save();
        });

        if ($status !== Password::PASSWORD_RESET) {
            throw ValidationException::withMessages([
                'email' => [__($status)],
            ]);
        }

        return __($status);
    }

    /**
     * Logout user by revoking current token.
     */
    public function logout(User $user): void
    {
        /** @var \Laravel\Sanctum\PersonalAccessToken $token */
        $token = $user->currentAccessToken();
        
        $token?->delete();
    }

    /**
     * Handle Google login/registration.
     */
    public function googleLogin(string $idToken): array
    {
        // 1. Verify token via Google API
        $response = \Illuminate\Support\Facades\Http::get("https://oauth2.googleapis.com/tokeninfo", [
            'id_token' => $idToken,
        ]);

        if ($response->failed()) {
            throw ValidationException::withMessages([
                'google_token' => ['Invalid Google token.'],
            ]);
        }

        $payload = $response->json();
        
        $email = $payload['email'];
        $name = $payload['name'] ?? $payload['given_name'] . ' ' . ($payload['family_name'] ?? '');
        $avatar = $payload['picture'] ?? null;
        $googleId = $payload['sub'];

        // 2. Find or Create User
        $user = User::where('email', $email)->orWhere('google_id', $googleId)->first();

        if (!$user) {
            $user = DB::transaction(function () use ($email, $name, $avatar, $googleId) {
                $user = User::create([
                    'name'      => $name,
                    'email'     => $email,
                    'password'  => Hash::make(Str::random(24)),
                    'role'      => 'user',
                    'status'    => 'active',
                    'google_id' => $googleId,
                    'avatar_url' => $avatar,
                ]);

                $user->profile()->create();
                $user->preferences()->create(['settings' => []]);
                
                return $user;
            });
        } else {
            // Update google_id and avatar if missing
            $user->update([
                'google_id' => $googleId,
                'avatar_url' => $avatar,
            ]);
        }

        $token = $user->createToken('google_auth_token')->plainTextToken;

        return [
            'user'  => $user,
            'token' => $token,
        ];
    }
}
