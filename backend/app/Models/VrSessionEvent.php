<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $vr_session_id
 * @property int $user_id
 * @property int $training_module_id
 * @property int|null $device_id
 * @property string $event_type
 * @property \Illuminate\Support\Carbon $event_timestamp
 * @property array<array-key, mixed>|null $event_payload
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\VrDevice|null $device
 * @property-read \App\Models\TrainingModule $trainingModule
 * @property-read \App\Models\User $user
 * @property-read \App\Models\VrSession $vrSession
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VrSessionEvent newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VrSessionEvent newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VrSessionEvent query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VrSessionEvent whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VrSessionEvent whereDeviceId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VrSessionEvent whereEventPayload($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VrSessionEvent whereEventTimestamp($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VrSessionEvent whereEventType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VrSessionEvent whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VrSessionEvent whereTrainingModuleId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VrSessionEvent whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VrSessionEvent whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VrSessionEvent whereVrSessionId($value)
 * @mixin \Eloquent
 */
class VrSessionEvent extends Model
{
    use HasFactory;

    protected $fillable = [
        'vr_session_id',
        'user_id',
        'training_module_id',
        'device_id',
        'event_type',
        'event_timestamp',
        'event_payload',
    ];

    protected $casts = [
        'event_timestamp' => 'datetime',
        'event_payload' => 'array',
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

    public function device()
    {
        return $this->belongsTo(VrDevice::class, 'device_id');
    }
}
