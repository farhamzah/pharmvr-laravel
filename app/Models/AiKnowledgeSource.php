<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class AiKnowledgeSource extends Model
{
    use HasFactory;

    protected $fillable = [
        'module_id',
        'title',
        'slug',
        'description',
        'category',
        'topic',
        'author',
        'publisher',
        'publication_year',
        'source_type',
        'file_path',
        'url',
        'content',
        'language',
        'trust_level',
        'is_active',
        'parsing_status',
        'indexing_status',
        'total_chunks',
        'uploaded_by',
        'status',
    ];

    protected $casts = [
        'source_type' => \App\Enums\SourceType::class,
        'trust_level' => \App\Enums\TrustLevel::class,
        'parsing_status' => \App\Enums\AiProcessingStatus::class,
        'indexing_status' => \App\Enums\AiProcessingStatus::class,
        'status' => \App\Enums\AiSourceStatus::class,
        'is_active' => 'boolean',
        'total_chunks' => 'integer',
        'publication_year' => 'integer',
    ];

    public function module()
    {
        return $this->belongsTo(TrainingModule::class, 'module_id');
    }

    public function chunks()
    {
        return $this->hasMany(AiKnowledgeChunk::class, 'source_id');
    }

    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    /**
     * Scope a query to only include active sources.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
