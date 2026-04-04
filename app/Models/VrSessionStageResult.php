<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $vr_session_id
 * @property int $user_id
 * @property int $training_module_id
 * @property string $stage_name
 * @property numeric|null $stage_score
 * @property bool $passed
 * @property \Illuminate\Support\Carbon $submitted_at
 * @property array<array-key, mixed>|null $metadata
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\TrainingModule $trainingModule
 * @property-read \App\Models\User $user
 * @property-read \App\Models\VrSession $vrSession
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VrSessionStageResult newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VrSessionStageResult newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VrSessionStageResult query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VrSessionStageResult whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VrSessionStageResult whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VrSessionStageResult whereMetadata($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VrSessionStageResult wherePassed($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VrSessionStageResult whereStageName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VrSessionStageResult whereStageScore($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VrSessionStageResult whereSubmittedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VrSessionStageResult whereTrainingModuleId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VrSessionStageResult whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VrSessionStageResult whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VrSessionStageResult whereVrSessionId($value)
 * @mixin \Eloquent
 */
class VrSessionStageResult extends Model
{
    use HasFactory;

    protected $fillable = [
        'vr_session_id',
        'user_id',
        'training_module_id',
        'stage_name',
        'stage_score',
        'passed',
        'submitted_at',
        'metadata',
    ];

    protected $casts = [
        'passed' => 'boolean',
        'submitted_at' => 'datetime',
        'metadata' => 'array',
        'stage_score' => 'decimal:2',
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
