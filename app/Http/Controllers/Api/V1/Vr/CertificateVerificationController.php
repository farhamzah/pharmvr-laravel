<?php

namespace App\Http\Controllers\Api\V1\Vr;

use App\Http\Controllers\Controller;
use App\Models\Certificate;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;

class CertificateVerificationController extends Controller
{
    use ApiResponse;

    /**
     * Public certificate verification — no authentication required.
     * Only exposes non-sensitive fields.
     */
    public function verify(string $certificateId): JsonResponse
    {
        $certificate = Certificate::where('certificate_id', $certificateId)
            ->where('status', 'issued')
            ->with('user:id,name')
            ->first();

        if (!$certificate) {
            return response()->json([
                'success' => false,
                'message' => 'Certificate not found.',
                'data'    => ['valid' => false],
                'errors'  => null,
            ], 404);
        }

        $metadata = is_array($certificate->metadata_json) ? $certificate->metadata_json : [];

        return $this->successResponse([
            'valid'                  => true,
            'certificate_id'         => $certificate->certificate_id,
            'certificate_type'       => $certificate->certificate_type,
            'title'                  => $certificate->title,
            'status'                 => $certificate->status,
            'issued_at'              => $certificate->issued_at?->toISOString(),
            'learner_name'           => $certificate->user?->name ?? 'Verified Learner',
            'production_path_title'  => $metadata['production_path_title'] ?? 'Non-Sterile Solid Dosage Production Path',
            'completed_scenes'       => $metadata['completed_scenes'] ?? 11,
            'total_scenes'           => $metadata['total_scenes'] ?? 11,
        ], 'Certificate is valid.');
    }
}
