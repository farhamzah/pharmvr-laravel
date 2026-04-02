<?php

namespace App\Traits;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

trait OptimizesImages
{
    /**
     * Store an optimized version of an uploaded image.
     *
     * @param UploadedFile $file
     * @param string $directory
     * @param int $maxWidth
     * @param int $quality
     * @return string Path to the stored file
     */
    protected function storeOptimized(UploadedFile $file, string $directory, int $maxWidth = 1080, int $quality = 75): string
    {
        // Get original image info
        $imageInfo = getimagesize($file->getPathname());
        if (!$imageInfo) {
            // Not an image or GD can't read it, fall back to standard store
            return $file->store($directory, 'public');
        }

        [$width, $height, $type] = $imageInfo;
        
        // Load image based on type
        switch ($type) {
            case IMAGETYPE_JPEG:
                $src = imagecreatefromjpeg($file->getPathname());
                break;
            case IMAGETYPE_PNG:
                $src = imagecreatefrompng($file->getPathname());
                // Handle transparency for PNG
                imagealphablending($src, false);
                imagesavealpha($src, true);
                break;
            case IMAGETYPE_WEBP:
                $src = imagecreatefromwebp($file->getPathname());
                break;
            default:
                // Unsupported type for optimization, fall back
                return $file->store($directory, 'public');
        }

        // Calculate new dimensions
        if ($width > $maxWidth) {
            $newWidth = $maxWidth;
            $newHeight = (int) ($height * ($maxWidth / $width));
        } else {
            $newWidth = $width;
            $newHeight = $height;
        }

        // Create new image
        $dst = imagecreatetruecolor($newWidth, $newHeight);
        
        // Keep transparency for PNG/WebP
        if ($type == IMAGETYPE_PNG || $type == IMAGETYPE_WEBP) {
            imagealphablending($dst, false);
            imagesavealpha($dst, true);
            $transparent = imagecolorallocatealpha($dst, 255, 255, 255, 127);
            imagefilledrectangle($dst, 0, 0, $newWidth, $newHeight, $transparent);
        }

        // Resize
        imagecopyresampled($dst, $src, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);

        // Generate filename
        $filename = time() . '_' . Str::random(10) . '.jpg'; // Store as JPG for best compression/performance ratio
        $tempPath = tempnam(sys_get_temp_dir(), 'opt');

        // Output as JPG for better compression
        imagejpeg($dst, $tempPath, $quality);

        // Store to public disk
        $finalPath = $directory . '/' . $filename;
        Storage::disk('public')->put($finalPath, file_get_contents($tempPath));

        // Cleanup
        imagedestroy($src);
        imagedestroy($dst);
        unlink($tempPath);

        return $finalPath;
    }
}
