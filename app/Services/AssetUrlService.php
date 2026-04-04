<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;

class AssetUrlService
{
    /**
     * Resolve a stored path/URL into a full, publicly accessible URL.
     *
     * Handles:
     * - null → null
     * - Full external URLs (https://...) → returned as-is
     * - Legacy absolute URLs (http://localhost:8000/...) → stripped and re-resolved
     * - Static asset paths (assets/...) → APP_URL + path
     * - Storage-relative paths (news/xxx.jpg) → Storage URL
     * - Paths with 'storage/' prefix → stripped, then Storage URL
     * - Paths with '/storage/' prefix → stripped, then Storage URL
     */
    public static function resolve(?string $path): ?string
    {
        if (empty($path)) {
            return null;
        }

        // 1. External URLs (https://, ftp://, etc) — keep as-is
        if (preg_match('#^https?://#i', $path)) {
            // Check for legacy localhost / 127.0.0.1 / 10.0.2.2 URLs
            if (self::isLegacyLocalUrl($path)) {
                $relativePath = self::extractRelativePath($path);
                return self::resolveRelativePath($relativePath);
            }
            return $path;
        }

        // 2. Remove leading slash if present
        $path = ltrim($path, '/');

        return self::resolveRelativePath($path);
    }

    /**
     * Resolve a relative path to a full URL.
     */
    private static function resolveRelativePath(string $path): string
    {
        // Strip 'storage/' or '/storage/' prefix
        $cleanPath = preg_replace('#^/?storage/#', '', $path);

        // If it starts with 'assets/', it's a public static file
        if (str_starts_with($cleanPath, 'assets/')) {
            return url($cleanPath);
        }

        // Otherwise, it's a storage dynamic file
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
