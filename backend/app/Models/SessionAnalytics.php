<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $vr_session_id
 * @property int $total_score
 * @property int $accuracy_score
 * @property int $speed_score
 * @property int $breach_count
 * @property int $duration_seconds
 * @property int $completed_steps
 * @property int $total_steps
 * @property array<array-key, mixed>|null $metrics_json
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\VrSession $vrSession
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SessionAnalytics newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SessionAnalytics newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SessionAnalytics query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SessionAnalytics whereAccuracyScore($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SessionAnalytics whereBreachCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SessionAnalytics whereCompletedSteps($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SessionAnalytics whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SessionAnalytics whereDurationSeconds($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SessionAnalytics whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SessionAnalytics whereMetricsJson($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SessionAnalytics whereSpeedScore($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SessionAnalytics whereTotalScore($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SessionAnalytics whereTotalSteps($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SessionAnalytics whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SessionAnalytics whereVrSessionId($value)
 * @mixin \Eloquent
 */
class SessionAnalytics extends Model
{
    use HasFactory;

    protected $table = 'session_analytics';

    protected $fillable = [
        'vr_session_id',
        'total_score',
        'accuracy_score',
        'speed_score',
        'breach_count',
        'duration_seconds',
        'completed_steps',
        'total_steps',
        'metrics_json',
    ];

    protected $casts = [
        'metrics_json' => 'array',
    ];

    public function vrSession()
    {
        return $this->belongsTo(VrSession::class);
    }
}
