<?php

namespace App\Http\Controllers\Api\V1\Vr;

use App\Http\Controllers\Controller;
use App\Models\VrDevice;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;

class VrDeviceController extends Controller
{
    use ApiResponse;

    /**
     * Helper to validate device by token.
     */
    private function validateDevice(Request $request)
    {
        $headsetToken = $request->header('X-VR-Device-Token') ?? $request->device_access_token;
        $headsetId = $request->header('X-VR-Headset-ID');

        if (!$headsetToken) {
            return null;
        }

        // Optimization (Phase 4.1): If Headset ID is provided, use indexed lookup
        if ($headsetId) {
            $device = VrDevice::where('headset_identifier', $headsetId)
                ->where('status', 'active')
                ->first();
            
            if ($device && Hash::check($headsetToken, $device->device_token_hash)) {
                return $device;
            }
            return null;
        }

        // Fallback for legacy calls or if header is missing (High Cost)
        $devices = VrDevice::where('status', 'active')->get();
        return $devices->first(function ($d) use ($headsetToken) {
            return Hash::check($headsetToken, $d->device_token_hash);
        });
    }

    /**
     * Headset sends a heartbeat to update last_seen_at.
     */
    public function heartbeat(Request $request)
    {
        $request->validate([
            'device_access_token' => 'required|string',
        ]);

        $device = $this->validateDevice($request);

        if (!$device) {
            return $this->errorResponse('Invalid device access token.', 401);
        }

        $device->update([
            'last_seen_at' => Carbon::now(),
        ]);

        return $this->successResponse([
            'device_id' => $device->id,
            'status' => $device->status,
            'last_seen' => $device->last_seen_at->toDateTimeString(),
        ], 'Heartbeat received.');
    }

    /**
     * Unpair or deactivate the device.
     */
    public function unpair(Request $request)
    {
        $request->validate([
            'device_access_token' => 'required|string',
        ]);

        $device = $this->validateDevice($request);

        if (!$device) {
            return $this->errorResponse('Invalid device access token.', 401);
        }

        $device->update([
            'status' => 'unlinked',
            'user_id' => null,
            'current_pairing_id' => null,
            'device_token_hash' => null, // Revoke token
        ]);

        return $this->successResponse(null, 'Device unpaired successfully.');
    }
}
