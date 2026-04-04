<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $user_id
 * @property string $interaction_type
 * @property string $source_type
 * @property int $source_id
 * @property string $provider_name
 * @property string|null $model_name
 * @property int|null $latency_ms
 * @property int|null $prompt_tokens
 * @property int|null $completion_tokens
 * @property int|null $total_tokens
 * @property string|null $domain_mode
 * @property bool $is_safe_response
 * @property int|null $conversation_id
 * @property int|null $vr_session_id
 * @property array<array-key, mixed>|null $metadata
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\PharmaiConversation|null $conversation
 * @property-read Model|\Eloquent $source
 * @property-read \App\Models\User $user
 * @property-read \App\Models\VrSession|null $vrSession
 * @method static \Database\Factories\AiUsageLogFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AiUsageLog newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AiUsageLog newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AiUsageLog query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AiUsageLog whereCompletionTokens($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AiUsageLog whereConversationId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AiUsageLog whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AiUsageLog whereDomainMode($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AiUsageLog whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AiUsageLog whereInteractionType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AiUsageLog whereIsSafeResponse($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AiUsageLog whereLatencyMs($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AiUsageLog whereMetadata($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AiUsageLog whereModelName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AiUsageLog wherePromptTokens($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AiUsageLog whereProviderName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AiUsageLog whereSourceId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AiUsageLog whereSourceType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AiUsageLog whereTotalTokens($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AiUsageLog whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AiUsageLog whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AiUsageLog whereVrSessionId($value)
 * @mixin \Eloquent
 */
class AiUsageLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'interaction_type',
        'source_type',
        'source_id',
        'provider_name',
        'model_name',
        'latency_ms',
        'prompt_tokens',
        'completion_tokens',
        'total_tokens',
        'domain_mode',
        'is_safe_response',
        'conversation_id',
        'vr_session_id',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
        'is_safe_response' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function conversation()
    {
        return $this->belongsTo(PharmaiConversation::class, 'conversation_id');
    }

    public function vrSession()
    {
        return $this->belongsTo(VrSession::class, 'vr_session_id');
    }

    /**
     * Get the source interaction (Polymorphic).
     */
    public function source()
    {
        return $this->morphTo();
    }
}
