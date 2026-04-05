<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;

class MediaController extends Controller
{
    /**
     * Proxies storage files to ensure CORS headers are sent.
     * Essential for Flutter Web when using 'php artisan serve'.
     */
    public function serve(Request $request, $path)
    {
        // 1. Security: Ensure path doesn't try to go outside of public storage
        if (str_contains($path, '..')) {
            abort(403, 'Invalid path');
        }

        // 2. Pre-processing: Strip redundant 'storage/' or '/storage/' if present
        $cleanPath = preg_replace('#^/?storage/#', '', $path);

        // 3. Check in 'public' disk (which maps to storage/app/public)
        if (Storage::disk('public')->exists($cleanPath)) {
            $file = Storage::disk('public')->get($cleanPath);
            $type = Storage::disk('public')->mimeType($cleanPath);
        } else {
            // 4. New: Also check the regular public directory (for assets/ and similar)
            $publicFilePath = public_path($path);
            if (file_exists($publicFilePath) && is_file($publicFilePath)) {
                $file = file_get_contents($publicFilePath);
                $type = mime_content_type($publicFilePath);
            } else {
                abort(404, 'File not found: ' . $path);
            }
        }

        return response($file, 200)
            ->header('Content-Type', $type)
            ->header('Access-Control-Allow-Origin', '*')
            ->header('Access-Control-Allow-Methods', 'GET, OPTIONS')
            ->header('Access-Control-Allow-Headers', 'Content-Type, X-Requested-With')
            ->header('Cache-Control', 'public, max-age=86400');
    }
}
