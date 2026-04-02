<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $user_id
 * @property int $assessment_id
 * @property int|null $score
 * @property int $passed
 * @property string $status
 * @property \Illuminate\Support\Carbon|null $started_at
 * @property \Illuminate\Support\Carbon|null $completed_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\UserAnswer> $answers
 * @property-read int|null $answers_count
 * @property-read \App\Models\Assessment $assessment
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AssessmentAttempt newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AssessmentAttempt newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AssessmentAttempt query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AssessmentAttempt whereAssessmentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AssessmentAttempt whereCompletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AssessmentAttempt whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AssessmentAttempt whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AssessmentAttempt wherePassed($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AssessmentAttempt whereScore($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AssessmentAttempt whereStartedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AssessmentAttempt whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AssessmentAttempt whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AssessmentAttempt whereUserId($value)
 * @mixin \Eloquent
 */
class AssessmentAttempt extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'assessment_id',
        'score',
        'passed',
        'status',
        'started_at',
        'completed_at',
    ];

    protected $casts = [
        'started_at'   => 'datetime',
        'completed_at' => 'datetime',
    ];

    /**
     * Get the user that owns the attempt.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the assessment that owns the attempt.
     */
    public function assessment()
    {
        return $this->belongsTo(Assessment::class);
    }

    /**
     * Get the answers for the attempt.
     */
    public function answers()
    {
        return $this->hasMany(UserAnswer::class);
    }
}
