<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AiChatMessage extends Model
{
    use HasFactory;

    protected $fillable = [
        'session_id',
        'sender',
        'message_text',
        'cited_sources_json',
        'scene_context',
        'object_context',
        'response_time_ms',
        'confidence_score',
        'response_mode',
        'suggested_followups',
    ];

    protected $casts = [
        'sender' => \App\Enums\ChatSender::class,
        'cited_sources_json' => 'array',
        'suggested_followups' => 'array',
        'response_time_ms' => 'integer',
        'confidence_score' => 'decimal:2',
    ];

    public function session()
    {
        return $this->belongsTo(AiChatSession::class, 'session_id');
    }
}
