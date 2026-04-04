<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\UserTrainingProgress;

use App\Traits\Auditable;

/**
 * @property int $id
 * @property string $title
 * @property string $slug
 * @property string|null $description
 * @property string|null $cover_image_path
 * @property string $difficulty
 * @property int|null $estimated_duration
 * @property bool $is_active
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Assessment> $assessments
 * @property-read int|null $assessments_count
 * @property-read mixed $cover_image_url
 * @property-read \Illuminate\Database\Eloquent\Collection<int, UserTrainingProgress> $userProgress
 * @property-read int|null $user_progress_count
 * @method static \Database\Factories\TrainingModuleFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TrainingModule newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TrainingModule newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TrainingModule query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TrainingModule whereCoverImagePath($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TrainingModule whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TrainingModule whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TrainingModule whereDifficulty($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TrainingModule whereEstimatedDuration($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TrainingModule whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TrainingModule whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TrainingModule whereSlug($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TrainingModule whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TrainingModule whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class TrainingModule extends Model
{
    use HasFactory, Auditable;

    protected $fillable = [
        'title',
        'slug',
        'description',
        'cover_image_path',
        'difficulty',
        'estimated_duration',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    protected $appends = ['cover_image_url'];

    public function getCoverImageUrlAttribute()
    {
        $path = $this->cover_image_path;
        if (!$path) return null;
        if (filter_var($path, FILTER_VALIDATE_URL)) return $path;
        
        // Remove 'storage/' prefix if we stored it that way
        $cleanPath = str_replace('storage/', '', $path);
        return \Illuminate\Support\Facades\Storage::disk('public')->url($cleanPath);
    }

    /**
     * Get the progress records for this training module.
     */
    public function userProgress()
    {
        return $this->hasMany(UserTrainingProgress::class);
    }

    /**
     * Get the assessments for this training module.
     */
    public function assessments()
    {
        return $this->hasMany(Assessment::class, 'module_id');
    }

    /**
     * Get the question bank items for this training module.
     */
    public function questionBankItems()
    {
        return $this->hasMany(QuestionBankItem::class, 'module_id');
    }

    /**
     * Get count of questions eligible for pre-test.
     */
    public function getPretestQuestionCountAttribute(): int
    {
        return $this->questionBankItems()
            ->whereIn('usage_scope', [\App\Enums\QuestionUsageScope::PRETEST, \App\Enums\QuestionUsageScope::BOTH])
            ->where('is_active', true)
            ->count();
    }

    /**
     * Get count of questions eligible for post-test.
     */
    public function getPosttestQuestionCountAttribute(): int
    {
        return $this->questionBankItems()
            ->whereIn('usage_scope', [\App\Enums\QuestionUsageScope::POSTTEST, \App\Enums\QuestionUsageScope::BOTH])
            ->where('is_active', true)
            ->count();
    }

    /**
     * Scope a query to only include active modules.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
