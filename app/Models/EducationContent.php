<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @mixin \Illuminate\Database\Eloquent\Builder
 * @property int $id
 * @property string|null $code
 * @property string $title
 * @property string $slug
 * @property string $type
 * @property string $source_type
 * @property int|null $training_module_id
 * @property string|null $category
 * @property string|null $related_topic
 * @property string $level
 * @property array<array-key, mixed>|null $tags
 * @property array<array-key, mixed>|null $learning_path
 * @property string|null $next_step_label
 * @property string|null $next_step_action
 * @property string|null $description
 * @property string|null $short_summary
 * @property string|null $prerequisites
 * @property string|null $related_materials
 * @property string|null $ai_context
 * @property string|null $thumbnail_url
 * @property string|null $file_url
 * @property string|null $file_type
 * @property string|null $video_id
 * @property string|null $platform
 * @property int|null $duration_minutes
 * @property int|null $pages_count
 * @property bool $is_active
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\TrainingModule|null $trainingModule
 * @method static \Database\Factories\EducationContentFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EducationContent newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EducationContent newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EducationContent query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EducationContent whereAiContext($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EducationContent whereCategory($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EducationContent whereCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EducationContent whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EducationContent whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EducationContent whereDurationMinutes($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EducationContent whereFileType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EducationContent whereFileUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EducationContent whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EducationContent whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EducationContent whereLearningPath($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EducationContent whereLevel($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EducationContent whereNextStepAction($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EducationContent whereNextStepLabel($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EducationContent wherePagesCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EducationContent wherePlatform($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EducationContent wherePrerequisites($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EducationContent whereRelatedMaterials($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EducationContent whereRelatedTopic($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EducationContent whereShortSummary($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EducationContent whereSlug($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EducationContent whereSourceType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EducationContent whereTags($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EducationContent whereThumbnailUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EducationContent whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EducationContent whereTrainingModuleId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EducationContent whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EducationContent whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EducationContent whereVideoId($value)
 * @mixin \Eloquent
 */
class EducationContent extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'training_module_id',
        'title',
        'slug',
        'type',
        'source_type',
        'category',
        'related_topic',
        'level',
        'tags',
        'learning_path',
        'next_step_label',
        'next_step_action',
        'description',
        'short_summary',
        'prerequisites',
        'related_materials',
        'ai_context',
        'thumbnail_url',
        'file_url',
        'file_type',
        'video_id',
        'platform',
        'duration_minutes',
        'pages_count',
        'is_active',
    ];

    protected $casts = [
        'is_active'     => 'boolean',
        'tags'          => 'array',
        'learning_path' => 'array',
    ];

    /**
     * Get the training module associated with this education content.
     */
    public function trainingModule()
    {
        return $this->belongsTo(TrainingModule::class);
    }
}
