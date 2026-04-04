<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $user_id
 * @property int $training_module_id
 * @property int $completion_percentage
 * @property string $status
 * @property string $pre_test_status
 * @property string $vr_status
 * @property string $post_test_status
 * @property string|null $last_active_step
 * @property \Illuminate\Support\Carbon|null $last_accessed_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\TrainingModule $trainingModule
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserTrainingProgress newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserTrainingProgress newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserTrainingProgress query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserTrainingProgress whereCompletionPercentage($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserTrainingProgress whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserTrainingProgress whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserTrainingProgress whereLastAccessedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserTrainingProgress whereLastActiveStep($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserTrainingProgress wherePostTestStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserTrainingProgress wherePreTestStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserTrainingProgress whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserTrainingProgress whereTrainingModuleId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserTrainingProgress whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserTrainingProgress whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserTrainingProgress whereVrStatus($value)
 * @mixin \Eloquent
 */
class UserTrainingProgress extends Model
{
    use HasFactory;

    protected $table = 'user_training_progress';

    protected $fillable = [
        'user_id',
        'training_module_id',
        'completion_percentage',
        'status',
        'pre_test_status',
        'vr_status',
        'post_test_status',
        'last_active_step',
        'last_accessed_at',
    ];

    /**
     * Check if a specific step in the journey is unlocked.
     */
    public function isStepUnlocked($step)
    {
        switch ($step) {
            case 'pre_test':
                return $this->pre_test_status !== 'locked';
            case 'vr_sim':
                return $this->pre_test_status === 'passed';
            case 'post_test':
                return $this->vr_status === 'completed';
            default:
                return false;
        }
    }

    protected $casts = [
        'last_accessed_at' => 'datetime',
    ];

    /**
     * Get the user associated with this progress.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the training module associated with this progress.
     */
    public function trainingModule()
    {
        return $this->belongsTo(TrainingModule::class);
    }
}
