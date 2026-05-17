<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * VrStepCompletion - Records completion of individual steps within a VR session.
 *
 * @property int $id
 * @property int $vr_session_id
 * @property int $scene_step_id
 * @property float $score
 * @property int $time_seconds
 * @property int $mistakes_count
 * @property \Carbon\Carbon $completed_at
 * @property array|null $metadata
 */
class VrStepCompletion extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'vr_session_id',
        'scene_step_id',
        'score',
        'time_seconds',
        'mistakes_count',
        'completed_at',
        'metadata',
    ];

    protected $casts = [
        'score' => 'decimal:2',
        'time_seconds' => 'integer',
        'mistakes_count' => 'integer',
        'completed_at' => 'datetime',
        'metadata' => 'array',
    ];

    // ─── Relationships ─────────────────────────────────────

    public function session(): BelongsTo
    {
        return $this->belongsTo(VrSession::class, 'vr_session_id');
    }

    public function step(): BelongsTo
    {
        return $this->belongsTo(SceneStep::class, 'scene_step_id');
    }
}
