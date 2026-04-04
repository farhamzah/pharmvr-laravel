<?php

namespace App\Http\Controllers\Api\V1\Vr;

use App\Http\Controllers\Controller;
use App\Models\VrDevice;
use App\Models\VrPairing;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class VrPairingController extends Controller
{
    use ApiResponse;

    /**
     * Mobile app requests a pairing session (Start).
     */
    public function start(Request $request)
    {
        $user = $request->user();

        // Expire older pending sessions
        VrPairing::where('user_id', $user->id)
            ->where('status', 'pending')
            ->update(['status' => 'expired']);

        // Generate a 6-digit short code
        $pairingCode = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        $pairingToken = (string) Str::uuid();
        $expiresAt = Carbon::now()->addMinutes(10);

        // [NEW] Validation: Check if pre-test is passed for the requested module
        if ($request->training_module_id) {
            $progress = \App\Models\UserTrainingProgress::where('user_id', $user->id)
                ->where('training_module_id', $request->training_module_id)
                ->first();

            if (!$progress || $progress->pre_test_status !== 'passed') {
                return $this->errorResponse('Anda harus lulus Pre-Test terlebih dahulu sebelum dapat melakukan integrasi VR untuk modul ini.', 403);
            }
        }

        $pairing = VrPairing::create([
            'user_id' => $user->id,
            'pairing_code_hash' => Hash::make($pairingCode),
            'pairing_token_hash' => Hash::make($pairingToken),
            'status' => 'pending',
            'expires_at' => $expiresAt,
            'requested_module_id' => $request->training_module_id,
        ]);

        return $this->successResponse([
            'pairing_id'   => $pairing->id,
            'pairing_code' => $pairingCode,
            'pairing_token'=> $pairingToken, // Payload placeholder for QR
            'expires_at'   => $expiresAt->toDateTimeString(),
            'status'       => $pairing->status,
            'instructions' => 'Masukkan kode 6-digit berikut pada Meta Quest 3 Anda untuk menghubungkan akun.',
            'device_type_target' => 'meta_quest_3',
        ], 'Pairing session initiated successfully.');
    }

    /**
     * Check current pairing status.
     */
    public function current(Request $request)
    {
        $user = $request->user();

        $pairing = VrPairing::where('user_id', $user->id)
            ->whereIn('status', ['pending', 'confirmed'])
            ->orderBy('created_at', 'desc')
            ->first();

        if (!$pairing) {
            return $this->successResponse(null, 'No active pairing session found.');
        }

        return $this->successResponse([
            'pairing_id' => $pairing->id,
            'status' => $pairing->status,
            'device_id' => $pairing->device_id,
            'confirmed_at' => $pairing->confirmed_at?->toDateTimeString(),
            'expires_at' => $pairing->expires_at->toDateTimeString(),
            'is_expired' => $pairing->isExpired(),
        ]);
    }

    /**
     * Cancel a pending pairing session.
     */
    public function cancel(Request $request, VrPairing $pairing)
    {
        // Ensure user owns the pairing
        if ($pairing->user_id !== $request->user()->id) {
            return $this->errorResponse('Unauthorized.', 403);
        }

        if ($pairing->status !== 'pending') {
            return $this->errorResponse('Only pending pairings can be cancelled.', 422);
        }

        $pairing->update([
            'status' => 'cancelled',
            'cancelled_at' => Carbon::now(),
        ]);

        return $this->successResponse(null, 'Pairing session cancelled successfully.');
    }

    /**
     * Headset app confirms the pairing using the code (Unity Side).
     */
    public function pair(Request $request)
    {
        $request->validate([
            'pairing_code' => 'required|string|size:6',
            'headset_identifier' => 'required|string',
            'device_name' => 'nullable|string',
            'platform_name' => 'nullable|string',
            'app_version' => 'nullable|string',
            'device_type' => 'nullable|string|in:meta_quest_3,smartphone_vr',
        ]);

        $pendingPairings = VrPairing::where('status', 'pending')
            ->where('expires_at', '>', Carbon::now())
            ->get();

        $pairing = $pendingPairings->first(function ($p) use ($request) {
            return Hash::check($request->pairing_code, $p->pairing_code_hash);
        });

        if (!$pairing) {
            return $this->errorResponse('Invalid or expired pairing code.', 422);
        }

        try {
            return DB::transaction(function () use ($pairing, $request) {
                $deviceAccessToken = Str::random(64);
                
                // Update or Create VR Device
                $device = VrDevice::updateOrCreate(
                    ['headset_identifier' => $request->headset_identifier],
                    [
                        'user_id' => $pairing->user_id,
                        'device_type' => $request->device_type ?? 'meta_quest_3',
                        'device_name' => $request->device_name ?? 'Meta Quest 3',
                        'platform_name' => $request->platform_name ?? 'Meta Quest OS',
                        'app_version' => $request->app_version,
                        'device_token_hash' => Hash::make($deviceAccessToken),
                        'status' => 'active',
                        'last_seen_at' => Carbon::now(),
                        'current_pairing_id' => $pairing->id,
                    ]
                );

                // Update pairing record
                $pairing->update([
                    'status' => 'confirmed',
                    'device_id' => $device->id,
                    'confirmed_at' => Carbon::now(),
                ]);

                return $this->successResponse([
                    'pairing_id' => $pairing->id,
                    'device_id' => $device->id,
                    'device_access_token' => $deviceAccessToken,
                    'device_summary' => [
                        'name' => $device->device_name,
                        'user_name' => $pairing->user->name,
                        'platform' => $device->platform_name,
                    ],
                    'pairing_status' => $pairing->status,
                    'notes' => 'Token access valid selama perangkat aktif. Gunakan untuk heartbeat.',
                    'requested_module_id' => $pairing->requested_module_id
                ], 'Headset paired successfully.');
            });
        } catch (\Exception $e) {
            return $this->errorResponse('Pairing failed: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Headset app confirms the pairing using the code (Mobile Legacy Alias).
     */
    public function confirmPairing(Request $request)
    {
        return $this->pair($request);
    }
}
