<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class AiKnowledgeChunk extends Model
{
    use HasFactory;

    protected $fillable = [
        'source_id',
        'chunk_index',
        'section_title',
        'page_number',
        'chunk_text',
        'token_count',
        'embedding_status',
        'chunk_hash',
    ];

    protected $casts = [
        'chunk_index' => 'integer',
        'page_number' => 'integer',
        'token_count' => 'integer',
        'embedding_status' => \App\Enums\AiProcessingStatus::class,
    ];

    public function source()
    {
        return $this->belongsTo(AiKnowledgeSource::class, 'source_id');
    }
}
