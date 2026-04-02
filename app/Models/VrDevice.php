<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int|null $user_id
 * @property string $device_type
 * @property string|null $device_name
 * @property string $headset_identifier
 * @property string|null $platform_name
 * @property string|null $app_version
 * @property string|null $device_token_hash
 * @property string $status
 * @property \Illuminate\Support\Carbon|null $last_seen_at
 * @property int|null $current_pairing_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\VrPairing|null $currentPairing
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\VrSession> $sessions
 * @property-read int|null $sessions_count
 * @property-read \App\Models\User|null $user
 * @method static \Database\Factories\VrDeviceFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VrDevice newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VrDevice newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VrDevice query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VrDevice whereAppVersion($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VrDevice whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VrDevice whereCurrentPairingId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VrDevice whereDeviceName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VrDevice whereDeviceTokenHash($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VrDevice whereDeviceType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VrDevice whereHeadsetIdentifier($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VrDevice whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VrDevice whereLastSeenAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VrDevice wherePlatformName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VrDevice whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VrDevice whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VrDevice whereUserId($value)
 * @mixin \Eloquent
 */
class VrDevice extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'device_type',
        'device_name',
        'headset_identifier',
        'platform_name',
        'app_version',
        'device_token_hash',
        'status',
        'last_seen_at',
        'current_pairing_id',
    ];

    protected $casts = [
        'last_seen_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function currentPairing()
    {
        return $this->belongsTo(VrPairing::class, 'current_pairing_id');
    }

    public function sessions()
    {
        return $this->hasMany(VrSession::class, 'device_id');
    }
}
