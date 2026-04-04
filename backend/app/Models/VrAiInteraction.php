<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $user_id
 * @property int|null $vr_session_id
 * @property int|null $training_module_id
 * @property string $trigger_event_type
 * @property string $hint_type
 * @property array<array-key, mixed>|null $input_context
 * @property string $output_text
 * @property string|null $display_text
 * @property string|null $speech_text
 * @property string $severity
 * @property string|null $recommended_next_action
 * @property bool $is_voice_suitable
 * @property array<array-key, mixed>|null $metadata
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\TrainingModule|null $trainingModule
 * @property-read \App\Models\User $user
 * @property-read \App\Models\VrSession|null $vrSession
 * @method static \Database\Factories\VrAiInteractionFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VrAiInteraction newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VrAiInteraction newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VrAiInteraction query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VrAiInteraction whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VrAiInteraction whereDisplayText($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VrAiInteraction whereHintType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VrAiInteraction whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VrAiInteraction whereInputContext($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VrAiInteraction whereIsVoiceSuitable($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VrAiInteraction whereMetadata($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VrAiInteraction whereOutputText($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VrAiInteraction whereRecommendedNextAction($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VrAiInteraction whereSeverity($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VrAiInteraction whereSpeechText($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VrAiInteraction whereTrainingModuleId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VrAiInteraction whereTriggerEventType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VrAiInteraction whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VrAiInteraction whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VrAiInteraction whereVrSessionId($value)
 * @mixin \Eloquent
 */
class VrAiInteraction extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'vr_session_id',
        'training_module_id',
        'trigger_event_type',
        'hint_type',
        'input_context',
        'output_text',
        'display_text',
        'speech_text',
        'severity',
        'recommended_next_action',
        'is_voice_suitable',
        'metadata',
    ];

    protected $casts = [
        'input_context' => 'array',
        'metadata' => 'array',
        'is_voice_suitable' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function vrSession()
    {
        return $this->belongsTo(VrSession::class, 'vr_session_id');
    }

    public function trainingModule()
    {
        return $this->belongsTo(TrainingModule::class);
    }
}
