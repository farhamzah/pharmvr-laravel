<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use App\Traits\Auditable;

/**
 * @mixin \Illuminate\Database\Eloquent\Builder
 * @property int $id
 * @property string $title
 * @property string $slug
 * @property string|null $summary
 * @property string $content
 * @property string|null $image_url
 * @property string|null $category
 * @property \Illuminate\Support\Carbon|null $published_at
 * @property bool $is_active
 * @property bool $is_featured
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Database\Factories\NewsFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|News newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|News newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|News query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|News whereCategory($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|News whereContent($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|News whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|News whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|News whereImageUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|News whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|News whereIsFeatured($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|News wherePublishedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|News whereSlug($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|News whereSummary($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|News whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|News whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class News extends Model
{
    use HasFactory, Auditable;

    protected $fillable = [
        'title',
        'slug',
        'summary',
        'content',
        'image_url',
        'category',
        'published_at',
        'is_active',
        'is_featured',
        'content_type',
        'news_source_id',
        'original_url',
        'author',
        'source_name',
        'ai_summary',
        'ai_tags',
        'topic_category',
        'relevance_score',
        'is_pinned',
        'is_hidden',
        'content_hash',
    ];

    protected $casts = [
        'published_at' => 'datetime',
        'is_active'    => 'boolean',
        'is_featured'  => 'boolean',
        'ai_tags'      => 'array',
        'is_pinned'    => 'boolean',
        'is_hidden'    => 'boolean',
    ];

    public function source()
    {
        return $this->belongsTo(NewsSource::class, 'news_source_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }
    
    public function scopeVisible($query)
    {
        return $query->where('is_active', true)->where('is_hidden', false);
    }

    public function scopeInternal($query)
    {
        return $query->where('content_type', 'internal');
    }

    public function scopeExternal($query)
    {
        return $query->where('content_type', 'external');
    }

    public function isExternal(): bool
    {
        return $this->content_type === 'external';
    }
}
