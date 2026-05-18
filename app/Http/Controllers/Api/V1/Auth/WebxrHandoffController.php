<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\Auth\UserResource;
use App\Models\WebxrHandoffToken;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class WebxrHandoffController extends Controller
{
    use ApiResponse;

    private const EXPIRY_SECONDS = 120;

    public function create(Request $request): JsonResponse
    {
        $user = $request->user();
        $plainToken = Str::random(64);
        $expiresAt = now()->addSeconds(self::EXPIRY_SECONDS);

        WebxrHandoffToken::create([
            'user_id' => $user->id,
            'token_hash' => WebxrHandoffToken::hashPlainToken($plainToken),
            'expires_at' => $expiresAt,
        ]);

        $webxrBaseUrl = rtrim(config('app.webxr_base_url', env('WEBXR_BASE_URL', 'https://pharmvr.cloud')), '/');
        $webxrUrl = $webxrBaseUrl . '/lobby?handoff_token=' . rawurlencode($plainToken);

        return $this->successResponse([
            'handoff_token' => $plainToken,
            'expires_at' => $expiresAt->toISOString(),
            'webxr_url' => $webxrUrl,
        ], 'WebXR handoff token created.');
    }

    public function exchange(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'handoff_token' => ['required', 'string'],
        ]);

        $handoff = WebxrHandoffToken::query()
            ->where('token_hash', WebxrHandoffToken::hashPlainToken($validated['handoff_token']))
            ->first();

        if (!$handoff) {
            return $this->errorResponse('Invalid WebXR handoff token.', 422);
        }

        if ($handoff->isUsed()) {
            return $this->errorResponse('WebXR handoff token has already been used.', 422);
        }

        if ($handoff->isExpired()) {
            return $this->errorResponse('WebXR handoff token has expired.', 422);
        }

        $handoff->forceFill(['used_at' => now()])->save();

        $user = $handoff->user()->with(['profile', 'preferences'])->firstOrFail();
        $accessToken = $user->createToken('webxr-session')->plainTextToken;

        return $this->successResponse([
            'token' => $accessToken,
            'token_type' => 'Bearer',
            'user' => new UserResource($user),
        ], 'WebXR handoff token exchanged.');
    }
}
