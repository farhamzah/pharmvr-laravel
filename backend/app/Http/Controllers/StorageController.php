<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class StorageController extends Controller
{
    /**
     * Serve files from storage/app/public.
     * This is a fallback for local development environments where symlinks are restricted.
     */
    public function show(string $path): BinaryFileResponse
    {
        // Sanitize: prevent path traversal attacks
        $path = str_replace(['..', "\0"], '', $path);
        $fullPath = realpath(storage_path('app/public/' . $path));
        $allowedBase = realpath(storage_path('app/public'));

        // Ensure resolved path is within the allowed directory
        if (!$fullPath || !$allowedBase || !str_starts_with($fullPath, $allowedBase)) {
            abort(404);
        }

        if (!file_exists($fullPath)) {
            abort(404);
        }

        return response()->file($fullPath);
    }
}
