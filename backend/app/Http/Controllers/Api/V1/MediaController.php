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
     */
    public function serve(Request $request, $path)
    {
        if (str_contains($path, '..')) {
            abort(403, 'Invalid path');
        }

        $cleanPath = preg_replace('#^/?storage/#', '', $path);
        $cleanPath = ltrim($cleanPath, '/');

        // 1. Try public disk (storage/app/public)
        if (Storage::disk('public')->exists($cleanPath)) {
            $file = Storage::disk('public')->get($cleanPath);
            $type = Storage::disk('public')->mimeType($cleanPath);
            return $this->responseWithCors($file, $type);
        }

        // 2. Try root public directory recursively for assets/ etc.
        $possiblePaths = [
            public_path($path),
            public_path($cleanPath),
            public_path('storage/' . $cleanPath),
        ];

        foreach ($possiblePaths as $fullPath) {
            if (file_exists($fullPath) && is_file($fullPath)) {
                $file = file_get_contents($fullPath);
                $type = mime_content_type($fullPath);
                return $this->responseWithCors($file, $type);
            }
        }

        // 3. Fallback logic: 
        // In local, we skip the SVG placeholder to avoid "reverting to basics" visuals if assets are missing.
        if (app()->environment('local')) {
            abort(404, 'Asset not found locally: ' . $path);
        }

        // In Production, return the stylized SVG placeholder
        return $this->getPlaceholderResponse($path);
    }

    /**
     * Generate a professional SVG placeholder for missing images.
     */
    private function getPlaceholderResponse($path)
    {
        $isModule = str_contains($path, 'module') || str_contains($path, 'course');
        $isNews = str_contains($path, 'news') || str_contains($path, 'post');
        
        $color = $isModule ? '#00E5FF' : ($isNews ? '#FF4081' : '#00A8FF');
        $bg = '#121B22';
        $label = $isModule ? 'Module' : ($isNews ? 'News' : 'Asset');
        
        $svg = "<svg width='400' height='300' xmlns='http://www.w3.org/2000/svg'>
            <rect width='400' height='300' fill='$bg'/>
            <defs>
                <linearGradient id='grad' x1='0%' y1='0%' x2='100%' y2='100%'>
                    <stop offset='0%' style='stop-color:$color;stop-opacity:0.2' />
                    <stop offset='100%' style='stop-color:$color;stop-opacity:0.05' />
                </linearGradient>
            </defs>
            <rect width='400' height='300' fill='url(#grad)'/>
            <path d='M200 120 L230 180 L170 180 Z' fill='none' stroke='$color' stroke-width='2' opacity='0.5'/>
            <text x='50%' y='60%' font-family='sans-serif' font-size='20' fill='$color' text-anchor='middle' opacity='0.8'>$label Preview</text>
            <text x='50%' y='70%' font-family='sans-serif' font-size='12' fill='$color' text-anchor='middle' opacity='0.4'>PharmVR Professional Content</text>
        </svg>";

        return response($svg, 200)->header('Content-Type', 'image/svg+xml')
            ->header('Access-Control-Allow-Origin', '*')
            ->header('Cache-Control', 'public, max-age=3600');
    }

    private function responseWithCors($file, $type)
    {
        return response($file, 200)
            ->header('Content-Type', $type)
            ->header('Access-Control-Allow-Origin', '*')
            ->header('Access-Control-Allow-Methods', 'GET, OPTIONS')
            ->header('Access-Control-Allow-Headers', 'Content-Type, X-Requested-With')
            ->header('Cache-Control', 'public, max-age=86400');
    }
}
