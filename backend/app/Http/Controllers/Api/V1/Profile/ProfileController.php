<?php

namespace App\Http\Controllers\Api\V1\Profile;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Profile\ChangePasswordRequest;
use App\Http\Requests\Api\V1\Profile\UpdateProfileRequest;
use App\Http\Resources\Api\V1\UserResource;
use App\Services\ProfileService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    use ApiResponse;

    protected ProfileService $profileService;

    public function __construct(ProfileService $profileService)
    {
        $this->profileService = $profileService;
    }

    /**
     * Retrieve the authenticated user's profile.
     */
    public function show(Request $request): JsonResponse
    {
        $user = new UserResource($request->user()->load('profile'));
        return $this->successResponse($user, 'Profile retrieved successfully.');
    }

    /**
     * Update the authenticated user's profile.
     */
    public function update(UpdateProfileRequest $request): JsonResponse
    {
        $data = $request->validated();
        
        // Ensure avatar is handled if present
        if ($request->hasFile('avatar')) {
            $data['avatar'] = $request->file('avatar');
        }

        \Log::info("Profile update request received: ", $data);

        $user = $this->profileService->updateProfile($request->user(), $data);
        return $this->successResponse(new UserResource($user), 'Profile updated successfully.');
    }

    /**
     * Change the authenticated user's password.
     */
    public function changePassword(ChangePasswordRequest $request): JsonResponse
    {
        $this->profileService->changePassword($request->user(), $request->validated());
        return $this->successResponse(null, 'Password changed successfully.');
    }
}
