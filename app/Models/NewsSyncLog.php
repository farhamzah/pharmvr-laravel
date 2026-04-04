<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NewsSyncLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'news_source_id',
        'started_at',
        'completed_at',
        'status',
        'articles_fetched',
        'articles_new',
        'articles_skipped',
        'articles_failed',
        'error_message',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function source(): BelongsTo
    {
        return $this->belongsTo(NewsSource::class, 'news_source_id');
    }
}
