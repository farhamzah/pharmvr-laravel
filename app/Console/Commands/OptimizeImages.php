<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use App\Traits\OptimizesImages;

class OptimizeImages extends Command
{
    use OptimizesImages;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'optimize:images {--maxWidth=1080} {--quality=75}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Optimize existing images in the storage directory';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $maxWidth = $this->option('maxWidth');
        $quality = $this->option('quality');

        $directories = [
            'modules/thumbnails',
            'news',
            'settings',
        ];

        $this->info("Starting image optimization & conversion to JPG (Max Width: {$maxWidth}, Quality: {$quality}%)...");

        foreach ($directories as $directory) {
            $this->comment("\nProcessing directory: {$directory}");
            
            $files = Storage::disk('public')->files($directory);
            $bar = $this->output->createProgressBar(count($files));

            foreach ($files as $file) {
                $absolutePath = Storage::disk('public')->path($file);
                
                // Get image info
                $imageInfo = @getimagesize($absolutePath);
                if (!$imageInfo) {
                    $bar->advance();
                    continue;
                }

                [$width, $height, $type] = $imageInfo;

                // Optimization: Resize and always convert to JPG
                if (in_array($type, [IMAGETYPE_JPEG, IMAGETYPE_PNG, IMAGETYPE_WEBP])) {
                    $newPath = $this->optimizeAndConvert($file, $maxWidth, $quality);
                    
                    if ($newPath && $newPath !== $file) {
                        // Extension changed, update database
                        $this->updateDatabasePaths($file, $newPath);
                        // Delete old file
                        Storage::disk('public')->delete($file);
                    }
                }

                $bar->advance();
            }
            $bar->finish();
        }

        $this->info("\n\nOptimization & Conversion complete!");
    }

    /**
     * Optimize and convert to JPG.
     */
    protected function optimizeAndConvert($relativePath, $maxWidth, $quality)
    {
        $path = Storage::disk('public')->path($relativePath);
        $imageInfo = getimagesize($path);
        [$width, $height, $type] = $imageInfo;

        // Load image based on type
        switch ($type) {
            case IMAGETYPE_JPEG:
                $src = imagecreatefromjpeg($path);
                break;
            case IMAGETYPE_PNG:
                $src = @imagecreatefrompng($path);
                if (!$src) return null;
                imagealphablending($src, false);
                imagesavealpha($src, true);
                break;
            case IMAGETYPE_WEBP:
                $src = imagecreatefromwebp($path);
                break;
            default:
                return null;
        }

        // Calculate new dimensions
        if ($width > $maxWidth) {
            $newWidth = $maxWidth;
            $newHeight = (int) ($height * ($maxWidth / $width));
        } else {
            $newWidth = $width;
            $newHeight = $height;
        }

        // Create new image (Always truecolor for JPG output)
        $dst = imagecreatetruecolor($newWidth, $newHeight);
        
        // Fill with white background for transparency conversion to JPG
        $white = imagecolorallocate($dst, 255, 255, 255);
        imagefilledrectangle($dst, 0, 0, $newWidth, $newHeight, $white);
        
        imagecopyresampled($dst, $src, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);

        // Define new path with .jpg extension
        $pathInfo = pathinfo($relativePath);
        $newRelativePath = $pathInfo['dirname'] . '/' . $pathInfo['filename'] . '.jpg';
        $newAbsolutePath = Storage::disk('public')->path($newRelativePath);

        // Save as JPG
        imagejpeg($dst, $newAbsolutePath, $quality);

        imagedestroy($src);
        imagedestroy($dst);

        return $newRelativePath;
    }

    /**
     * Update all database tables that might point to these images.
     */
    protected function updateDatabasePaths($oldPath, $newPath)
    {
        $oldStoragePath = 'storage/' . $oldPath;
        $newStoragePath = 'storage/' . $newPath;

        // 1. Training Modules (Usually stores 'storage/...')
        \App\Models\TrainingModule::where('cover_image_path', $oldPath)
            ->orWhere('cover_image_path', $oldStoragePath)
            ->update(['cover_image_path' => $newStoragePath]);

        // 2. Education Content
        \App\Models\EducationContent::where('thumbnail_url', $oldPath)
            ->orWhere('thumbnail_url', $oldStoragePath)
            ->update(['thumbnail_url' => $newPath]);

        // 3. News
        \App\Models\News::where('image_url', $oldPath)
            ->orWhere('image_url', $oldStoragePath)
            ->update(['image_url' => $newPath]);

        // 4. Settings (Preserve 'storage/' if it was there)
        \App\Models\Setting::where('value', $oldPath)
            ->update(['value' => $newPath]);
        \App\Models\Setting::where('value', $oldStoragePath)
            ->update(['value' => $newStoragePath]);
    }
}
