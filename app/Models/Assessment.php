<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use App\Traits\Auditable;

/**
 * @property int $id
 * @property int $module_id
 * @property \App\Enums\AssessmentType $type
 * @property string $title
 * @property string|null $description
 * @property int $min_score
 * @property int $duration_minutes
 * @property \App\Enums\AssessmentStatus $status
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\AssessmentAttempt> $attempts
 * @property-read int|null $attempts_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Question> $questions
 * @property-read int|null $questions_count
 * @property-read \App\Models\TrainingModule $trainingModule
 * @method static \Database\Factories\AssessmentFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Assessment newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Assessment newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Assessment query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Assessment whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Assessment whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Assessment whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Assessment whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Assessment whereModuleId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Assessment whereNumberOfQuestionsToTake($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Assessment wherePassingScore($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Assessment whereRandomizeOptions($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Assessment whereRandomizeQuestions($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Assessment whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Assessment whereTimeLimitMinutes($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Assessment whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Assessment whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Assessment whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Assessment extends Model
{
    use HasFactory, Auditable;

    protected $fillable = [
        'module_id',
        'type',
        'title',
        'description',
        'status',
        'number_of_questions_to_take',
        'randomize_questions',
        'randomize_options',
        'passing_score',
        'time_limit_minutes',
        'created_by'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'type' => \App\Enums\AssessmentType::class,
        'status' => \App\Enums\AssessmentStatus::class,
        'randomize_questions' => 'boolean',
        'randomize_options' => 'boolean',
        'passing_score' => 'integer',
        'number_of_questions_to_take' => 'integer',
        'time_limit_minutes' => 'integer',
    ];

    /**
     * Get the module that owns the assessment.
     */
    public function trainingModule()
    {
        return $this->belongsTo(TrainingModule::class, 'module_id');
    }

    /**
     * Get the questions for the assessment (Question Bank items).
     */
    public function questionBankItems()
    {
        return $this->hasMany(QuestionBankItem::class, 'module_id', 'module_id')
                    ->where(function($q) {
                        $q->where('usage_scope', $this->type)
                          ->orWhere('usage_scope', \App\Enums\QuestionUsageScope::BOTH);
                    });
    }

    /**
     * Alias for questionBankItems for backward compatibility with API resources.
     */
    public function questions()
    {
        return $this->questionBankItems();
    }

    /**
     * Get the count of eligible active questions.
     */
    public function getEligibleQuestionsCountAttribute()
    {
        return $this->questionBankItems()->active()->count();
    }

    /**
     * Get the attempts for the assessment.
     */
    public function attempts()
    {
        return $this->hasMany(AssessmentAttempt::class);
    }
}
