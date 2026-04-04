<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $vr_session_id
 * @property int $user_id
 * @property int $training_module_id
 * @property string $hint_type
 * @property string $trigger_reason
 * @property string|null $related_step
 * @property string $displayed_text
 * @property \Illuminate\Support\Carbon $displayed_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\TrainingModule $trainingModule
 * @property-read \App\Models\User $user
 * @property-read \App\Models\VrSession $vrSession
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VrSessionHint newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VrSessionHint newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VrSessionHint query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VrSessionHint whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VrSessionHint whereDisplayedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VrSessionHint whereDisplayedText($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VrSessionHint whereHintType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VrSessionHint whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VrSessionHint whereRelatedStep($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VrSessionHint whereTrainingModuleId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VrSessionHint whereTriggerReason($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VrSessionHint whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VrSessionHint whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VrSessionHint whereVrSessionId($value)
 * @mixin \Eloquent
 */
class VrSessionHint extends Model
{
    use HasFactory;

    protected $fillable = [
        'vr_session_id',
        'user_id',
        'training_module_id',
        'hint_type',
        'trigger_reason',
        'related_step',
        'displayed_text',
        'displayed_at',
    ];

    protected $casts = [
        'displayed_at' => 'datetime',
    ];

    public function vrSession()
    {
        return $this->belongsTo(VrSession::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function trainingModule()
    {
        return $this->belongsTo(TrainingModule::class);
    }
}
