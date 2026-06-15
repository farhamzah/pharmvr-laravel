<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VrSceneLayout extends Model
{
    use HasFactory;

    public const STATUS_DRAFT = 'draft';
    public const STATUS_PUBLISHED = 'published';
    public const STATUS_ARCHIVED = 'archived';

    public const STATUSES = [
        self::STATUS_DRAFT,
        self::STATUS_PUBLISHED,
        self::STATUS_ARCHIVED,
    ];

    protected $fillable = [
        'scene_slug',
        'title',
        'template_key',
        'version',
        'status',
        'layout_json',
        'metadata_json',
        'validation_warnings_json',
        'created_by',
        'updated_by',
        'published_by',
        'published_at',
    ];

    protected $casts = [
        'version' => 'integer',
        'layout_json' => 'array',
        'metadata_json' => 'array',
        'validation_warnings_json' => 'array',
        'published_at' => 'datetime',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function publisher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'published_by');
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_PUBLISHED);
    }

    public function scopeForScene(Builder $query, string $sceneSlug): Builder
    {
        return $query->where('scene_slug', Scene::resolveCanonicalSlug($sceneSlug));
    }

    public function scopeLatestVersion(Builder $query): Builder
    {
        return $query->orderByDesc('version')->orderByDesc('id');
    }
}
