<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @mixin \Illuminate\Database\Eloquent\Builder
 * @property int $id
 * @property int $user_id
 * @property int $device_id
 * @property int $training_module_id
 * @property int|null $pairing_id
 * @property string $session_status
 * @property \Illuminate\Support\Carbon $started_at
 * @property \Illuminate\Support\Carbon|null $last_activity_at
 * @property \Illuminate\Support\Carbon|null $completed_at
 * @property \Illuminate\Support\Carbon|null $interrupted_at
 * @property string|null $current_step
 * @property int $progress_percentage
 * @property array<array-key, mixed>|null $summary_json
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\SessionAnalytics|null $analytics
 * @property-read \App\Models\VrDevice $device
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\VrSessionEvent> $events
 * @property-read int|null $events_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\VrSessionHint> $hints
 * @property-read int|null $hints_count
 * @property-read \App\Models\VrPairing|null $pairing
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\VrSessionStageResult> $stageResults
 * @property-read int|null $stage_results_count
 * @property-read \App\Models\TrainingModule $trainingModule
 * @property-read \App\Models\User $user
 * @method static \Database\Factories\VrSessionFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VrSession newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VrSession newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VrSession query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VrSession whereCompletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VrSession whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VrSession whereCurrentStep($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VrSession whereDeviceId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VrSession whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VrSession whereInterruptedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VrSession whereLastActivityAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VrSession wherePairingId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VrSession whereProgressPercentage($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VrSession whereSessionStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VrSession whereStartedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VrSession whereSummaryJson($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VrSession whereTrainingModuleId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VrSession whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VrSession whereUserId($value)
 * @mixin \Eloquent
 */
class VrSession extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'device_id',
        'training_module_id',
        'pairing_id',
        'session_status',
        'started_at',
        'last_activity_at',
        'completed_at',
        'interrupted_at',
        'current_step',
        'progress_percentage',
        'summary_json',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'last_activity_at' => 'datetime',
        'completed_at' => 'datetime',
        'interrupted_at' => 'datetime',
        'summary_json' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function device()
    {
        return $this->belongsTo(VrDevice::class, 'device_id');
    }

    public function trainingModule()
    {
        return $this->belongsTo(TrainingModule::class);
    }

    public function pairing()
    {
        return $this->belongsTo(VrPairing::class, 'pairing_id');
    }

    public function events()
    {
        return $this->hasMany(VrSessionEvent::class);
    }

    public function stageResults()
    {
        return $this->hasMany(VrSessionStageResult::class);
    }

    public function hints()
    {
        return $this->hasMany(VrSessionHint::class);
    }

    public function analytics()
    {
        return $this->hasOne(SessionAnalytics::class);
    }
}
