<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;

class AssetUrlService
{
    /**
     * Resolve a stored path/URL into a full, publicly accessible URL.
     */
    /**
     * Resolve a stored path/URL into a full, publicly accessible URL.
     */
    public static function resolve(?string $path): ?string
    {
        if (empty($path)) {
            return null;
        }

        // 1. External URLs (youtube, etc) — handle localhost/legacy first
        if (preg_match('#^https?://#i', $path)) {
            // If it's a localhost/local URL, extract the relative part and proceed
            if (self::isLegacyLocalUrl($path)) {
                $path = self::extractRelativePath($path);
            } else {
                // External URL (e.g. YouTube thumbnail), keep as-is with HTTPS upgrade
                if (app()->environment('production') && str_contains($path, 'pharmvr.cloud')) {
                    $path = str_replace('http://', 'https://', $path);
                }
                return $path;
            }
        }

        // 2. Normalize and clean the path
        $path = ltrim($path, '/');
        // Strip 'storage/' prefix if the DB path includes it
        $path = preg_replace('#^storage/#', '', $path);

        // 3. Environment-aware Base URL
        $apiUrl = config('app.url'); // e.g. http://localhost:8000 or https://admin.pharmvr.cloud
        $apiUrl = rtrim($apiUrl, '/');

        // 4. Force Media Proxy for everything dynamic or static in Public/Storage
        // This ensures the MediaController attached CORS headers to every asset.
        return "$apiUrl/api/v1/media/$path";
    }

    /**
     * Resolve a relative path to a full URL (Legacy fallback/unused in Deep Fix logic).
     */
    private static function resolveRelativePath(string $path): string
    {
        $cleanPath = preg_replace('#^/?storage/#', '', $path);
        if (str_starts_with($cleanPath, 'assets/')) {
            return url($cleanPath);
        }
        return url('storage/' . $cleanPath);
    }

    /**
     * Check if URL points to a local development server.
     */
    private static function isLegacyLocalUrl(string $url): bool
    {
        return (bool) preg_match(
            '#^https?://(localhost|127\.0\.0\.1|10\.0\.2\.2)(:\d+)?/#i',
            $url
        );
    }

    /**
     * Extract relative path from an absolute URL.
     * e.g., http://localhost:8000/assets/images/news/x.jpg → assets/images/news/x.jpg
     * e.g., http://localhost:8000/storage/news/x.jpg → storage/news/x.jpg
     */
    private static function extractRelativePath(string $url): string
    {
        $parsed = parse_url($url);
        $path = $parsed['path'] ?? '';
        return ltrim($path, '/');
    }

    /**
     * Normalize a path for storage in the database.
     * Strips APP_URL, localhost URLs, and storage/ prefixes from dynamic assets.
     *
     * Use this when SAVING to the database.
     *
     * @param string|null $path  The raw path/URL to normalize
     * @param string $type  'static' for public/assets, 'dynamic' for storage
     * @return string|null  Clean, portable path for database storage
     */
    public static function normalize(?string $path, string $type = 'dynamic'): ?string
    {
        if (empty($path)) {
            return null;
        }

        // External full URLs → keep as-is (YouTube thumbnails, etc.)
        if (preg_match('#^https?://#i', $path)) {
            // If it's a localhost/local URL, extract the relative part
            if (self::isLegacyLocalUrl($path)) {
                $path = self::extractRelativePath($path);
            } else {
                return $path; // External URL, keep as-is
            }
        }

        // Remove leading slash
        $path = ltrim($path, '/');

        // Remove 'storage/' prefix for dynamic assets
        if ($type === 'dynamic') {
            $path = preg_replace('#^storage/#', '', $path);
        }

        return $path;
    }
}
