<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class NewsSource extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'feed_url',
        'website_url',
        'logo_url',
        'feed_type',
        'parser_class',
        'is_active',
        'min_relevance_score',
        'sync_interval_hours',
        'last_synced_at',
        'last_sync_status',
        'articles_synced_count',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'last_synced_at' => 'datetime',
    ];

    public function articles(): HasMany
    {
        return $this->hasMany(News::class, 'news_source_id');
    }

    public function syncLogs(): HasMany
    {
        return $this->hasMany(NewsSyncLog::class, 'news_source_id');
    }
}
