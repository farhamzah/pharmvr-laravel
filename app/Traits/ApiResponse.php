<?php

namespace App\Traits;

use Illuminate\Http\JsonResponse;

trait ApiResponse
{
    /**
     * Send a successful JSON response.
     *
     * @param mixed  $data
     * @param string $message
     * @param int    $code
     * @param array|null $meta
     * @return JsonResponse
     */
    protected function successResponse(mixed $data, string $message = 'Success', int $code = 200, ?array $meta = null): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data'    => $data,
            'meta'    => $meta,
            'errors'  => null,
        ], $code);
    }

    /**
     * Send an error JSON response.
     *
     * @param string $message
     * @param int    $code
     * @param mixed  $errors
     * @return JsonResponse
     */
    protected function errorResponse(string $message = 'Error', int $code = 400, mixed $errors = null): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $message,
            'data'    => null,
            'meta'    => null,
            'errors'  => $errors,
        ], $code);
    }
}
