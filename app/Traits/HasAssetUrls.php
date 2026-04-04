<?php

namespace App\Traits;

use App\Services\AssetUrlService;

/**
 * Provides convenient asset URL resolution for models.
 * 
 * Usage in model:
 *   use HasAssetUrls;
 *   protected array $assetColumns = ['image_url', 'thumbnail_url'];
 */
trait HasAssetUrls
{
    /**
     * Resolve an asset column value to a full URL.
     */
    public function resolveAssetUrl(string $column): ?string
    {
        return AssetUrlService::resolve($this->attributes[$column] ?? null);
    }
}
