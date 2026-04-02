<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $user_id
 * @property int|null $device_id
 * @property string $pairing_code_hash
 * @property string $pairing_token_hash
 * @property string $status
 * @property \Illuminate\Support\Carbon $expires_at
 * @property \Illuminate\Support\Carbon|null $confirmed_at
 * @property \Illuminate\Support\Carbon|null $cancelled_at
 * @property \Illuminate\Support\Carbon|null $failed_at
 * @property int|null $requested_module_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\VrDevice|null $device
 * @property-read \App\Models\TrainingModule|null $requestedModule
 * @property-read \App\Models\User $user
 * @method static \Database\Factories\VrPairingFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VrPairing newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VrPairing newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VrPairing query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VrPairing whereCancelledAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VrPairing whereConfirmedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VrPairing whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VrPairing whereDeviceId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VrPairing whereExpiresAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VrPairing whereFailedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VrPairing whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VrPairing wherePairingCodeHash($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VrPairing wherePairingTokenHash($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VrPairing whereRequestedModuleId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VrPairing whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VrPairing whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VrPairing whereUserId($value)
 * @mixin \Eloquent
 */
class VrPairing extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'device_id',
        'pairing_code_hash',
        'pairing_token_hash',
        'status',
        'expires_at',
        'confirmed_at',
        'cancelled_at',
        'failed_at',
        'requested_module_id',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'confirmed_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'failed_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function device()
    {
        return $this->belongsTo(VrDevice::class, 'device_id');
    }

    public function requestedModule()
    {
        return $this->belongsTo(TrainingModule::class, 'requested_module_id');
    }

    public function isExpired()
    {
        return $this->expires_at->isPast() && $this->status === 'pending';
    }
}
