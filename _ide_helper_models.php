<?php

// @formatter:off
// phpcs:ignoreFile
/**
 * A helper file for your Eloquent Models
 * Copy the phpDocs from this file to the correct Model,
 * And remove them from this file, to prevent double declarations.
 *
 * @author Barry vd. Heuvel <barryvdh@gmail.com>
 */


namespace App\Models{
/**
 * @property int $id
 * @property int $user_id
 * @property string $interaction_type
 * @property string $source_type
 * @property int $source_id
 * @property string $provider_name
 * @property string|null $model_name
 * @property int|null $latency_ms
 * @property int|null $prompt_tokens
 * @property int|null $completion_tokens
 * @property int|null $total_tokens
 * @property string|null $domain_mode
 * @property bool $is_safe_response
 * @property int|null $conversation_id
 * @property int|null $vr_session_id
 * @property array<array-key, mixed>|null $metadata
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\PharmaiConversation|null $conversation
 * @property-read Model|\Eloquent $source
 * @property-read \App\Models\User $user
 * @property-read \App\Models\VrSession|null $vrSession
 * @method static \Database\Factories\AiUsageLogFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AiUsageLog newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AiUsageLog newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AiUsageLog query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AiUsageLog whereCompletionTokens($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AiUsageLog whereConversationId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AiUsageLog whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AiUsageLog whereDomainMode($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AiUsageLog whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AiUsageLog whereInteractionType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AiUsageLog whereIsSafeResponse($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AiUsageLog whereLatencyMs($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AiUsageLog whereMetadata($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AiUsageLog whereModelName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AiUsageLog wherePromptTokens($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AiUsageLog whereProviderName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AiUsageLog whereSourceId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AiUsageLog whereSourceType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AiUsageLog whereTotalTokens($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AiUsageLog whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AiUsageLog whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AiUsageLog whereVrSessionId($value)
 * @mixin \Eloquent
 */
	class AiUsageLog extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $training_module_id
 * @property string $type
 * @property string $title
 * @property string|null $description
 * @property int $min_score
 * @property int $duration_minutes
 * @property bool $is_active
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
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Assessment whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Assessment whereDurationMinutes($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Assessment whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Assessment whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Assessment whereMinScore($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Assessment whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Assessment whereTrainingModuleId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Assessment whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Assessment whereUpdatedAt($value)
 * @mixin \Eloquent
 */
	class Assessment extends \Eloquent {}
}

namespace App\Models{
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
	class AssessmentAttempt extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int|null $user_id
 * @property string $action
 * @property string|null $model_type
 * @property int|null $model_id
 * @property array<array-key, mixed>|null $old_values
 * @property array<array-key, mixed>|null $new_values
 * @property string|null $ip_address
 * @property string|null $user_agent
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\User|null $user
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AuditLog newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AuditLog newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AuditLog query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AuditLog whereAction($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AuditLog whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AuditLog whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AuditLog whereIpAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AuditLog whereModelId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AuditLog whereModelType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AuditLog whereNewValues($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AuditLog whereOldValues($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AuditLog whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AuditLog whereUserAgent($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AuditLog whereUserId($value)
 * @mixin \Eloquent
 */
	class AuditLog extends \Eloquent {}
}

namespace App\Models{
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
	class EducationContent extends \Eloquent {}
}

namespace App\Models{
/**
 * @mixin \Illuminate\Database\Eloquent\Builder
 * @property int $id
 * @property string $title
 * @property string $slug
 * @property string|null $summary
 * @property string $content
 * @property string|null $image_url
 * @property string|null $category
 * @property \Illuminate\Support\Carbon|null $published_at
 * @property bool $is_active
 * @property bool $is_featured
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Database\Factories\NewsFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|News newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|News newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|News query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|News whereCategory($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|News whereContent($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|News whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|News whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|News whereImageUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|News whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|News whereIsFeatured($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|News wherePublishedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|News whereSlug($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|News whereSummary($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|News whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|News whereUpdatedAt($value)
 * @mixin \Eloquent
 */
	class News extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $question_id
 * @property string $option_text
 * @property bool $is_correct
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Question $question
 * @method static \Database\Factories\OptionFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Option newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Option newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Option query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Option whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Option whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Option whereIsCorrect($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Option whereOptionText($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Option whereQuestionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Option whereUpdatedAt($value)
 * @mixin \Eloquent
 */
	class Option extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $name
 * @property string|null $label
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Role> $roles
 * @property-read int|null $roles_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Permission newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Permission newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Permission query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Permission whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Permission whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Permission whereLabel($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Permission whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Permission whereUpdatedAt($value)
 * @mixin \Eloquent
 */
	class Permission extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $user_id
 * @property string|null $title
 * @property \Illuminate\Support\Carbon|null $last_message_at
 * @property string $status
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\PharmaiMessage> $messages
 * @property-read int|null $messages_count
 * @property-read \App\Models\User $user
 * @method static \Database\Factories\PharmaiConversationFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PharmaiConversation newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PharmaiConversation newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PharmaiConversation query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PharmaiConversation whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PharmaiConversation whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PharmaiConversation whereLastMessageAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PharmaiConversation whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PharmaiConversation whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PharmaiConversation whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PharmaiConversation whereUserId($value)
 * @mixin \Eloquent
 */
	class PharmaiConversation extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $conversation_id
 * @property string $role
 * @property string $content
 * @property array<array-key, mixed>|null $metadata
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\PharmaiConversation $conversation
 * @method static \Database\Factories\PharmaiMessageFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PharmaiMessage newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PharmaiMessage newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PharmaiMessage query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PharmaiMessage whereContent($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PharmaiMessage whereConversationId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PharmaiMessage whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PharmaiMessage whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PharmaiMessage whereMetadata($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PharmaiMessage whereRole($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PharmaiMessage whereUpdatedAt($value)
 * @mixin \Eloquent
 */
	class PharmaiMessage extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $assessment_id
 * @property string $question_text
 * @property string|null $image_url
 * @property string|null $explanation
 * @property int $order
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Assessment $assessment
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Option> $options
 * @property-read int|null $options_count
 * @method static \Database\Factories\QuestionFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Question newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Question newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Question query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Question whereAssessmentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Question whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Question whereExplanation($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Question whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Question whereImageUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Question whereOrder($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Question whereQuestionText($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Question whereUpdatedAt($value)
 * @mixin \Eloquent
 */
	class Question extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $name
 * @property string|null $label
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Permission> $permissions
 * @property-read int|null $permissions_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\User> $users
 * @property-read int|null $users_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Role newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Role newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Role query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Role whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Role whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Role whereLabel($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Role whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Role whereUpdatedAt($value)
 * @mixin \Eloquent
 */
	class Role extends \Eloquent {}
}

namespace App\Models{
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
	class SessionAnalytics extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $key
 * @property string|null $value
 * @property string $group
 * @property string $type
 * @property string|null $description
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereGroup($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereKey($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereValue($value)
 * @mixin \Eloquent
 */
	class Setting extends \Eloquent {}
}

namespace App\Models{
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
	class TrainingModule extends \Eloquent {}
}

namespace App\Models{
/**
 * @mixin \Illuminate\Database\Eloquent\Builder
 * @property int $id
 * @property string $name
 * @property string $email
 * @property \Illuminate\Support\Carbon|null $email_verified_at
 * @property string $password
 * @property string $role
 * @property string $status
 * @property bool $can_bypass_prerequisites
 * @property string|null $remember_token
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\UserAchievement> $achievements
 * @property-read int|null $achievements_count
 * @property-read \Illuminate\Notifications\DatabaseNotificationCollection<int, \Illuminate\Notifications\DatabaseNotification> $notifications
 * @property-read int|null $notifications_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\PharmaiConversation> $pharmaiConversations
 * @property-read int|null $pharmai_conversations_count
 * @property-read UserPreference|null $preferences
 * @property-read UserProfile|null $profile
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Role> $roles
 * @property-read int|null $roles_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Laravel\Sanctum\PersonalAccessToken> $tokens
 * @property-read int|null $tokens_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, UserTrainingProgress> $trainingProgress
 * @property-read int|null $training_progress_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\VrAiInteraction> $vrAiInteractions
 * @property-read int|null $vr_ai_interactions_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\VrSession> $vrSessions
 * @property-read int|null $vr_sessions_count
 * @method static \Database\Factories\UserFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereCanBypassPrerequisites($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereEmailVerifiedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User wherePassword($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereRememberToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereRole($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereUpdatedAt($value)
 * @mixin \Eloquent
 */
	class User extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $user_id
 * @property string $achievement_slug
 * @property array<array-key, mixed>|null $metadata
 * @property \Illuminate\Support\Carbon $earned_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserAchievement newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserAchievement newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserAchievement query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserAchievement whereAchievementSlug($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserAchievement whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserAchievement whereEarnedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserAchievement whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserAchievement whereMetadata($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserAchievement whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserAchievement whereUserId($value)
 * @mixin \Eloquent
 */
	class UserAchievement extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $assessment_attempt_id
 * @property int $question_id
 * @property int $option_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\AssessmentAttempt $attempt
 * @property-read \App\Models\Option $option
 * @property-read \App\Models\Question $question
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserAnswer newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserAnswer newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserAnswer query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserAnswer whereAssessmentAttemptId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserAnswer whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserAnswer whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserAnswer whereOptionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserAnswer whereQuestionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserAnswer whereUpdatedAt($value)
 * @mixin \Eloquent
 */
	class UserAnswer extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $user_id
 * @property array<array-key, mixed>|null $settings
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserPreference newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserPreference newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserPreference query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserPreference whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserPreference whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserPreference whereSettings($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserPreference whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserPreference whereUserId($value)
 * @mixin \Eloquent
 */
	class UserPreference extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $user_id
 * @property string|null $first_name
 * @property string|null $last_name
 * @property string|null $phone
 * @property string|null $avatar_url
 * @property string|null $bio
 * @property string|null $birth_date
 * @property string|null $gender
 * @property string|null $institution
 * @property string|null $university
 * @property int|null $semester
 * @property string|null $nim
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserProfile newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserProfile newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserProfile query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserProfile whereAvatarUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserProfile whereBio($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserProfile whereBirthDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserProfile whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserProfile whereFirstName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserProfile whereGender($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserProfile whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserProfile whereInstitution($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserProfile whereLastName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserProfile whereNim($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserProfile wherePhone($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserProfile whereSemester($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserProfile whereUniversity($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserProfile whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserProfile whereUserId($value)
 * @mixin \Eloquent
 */
	class UserProfile extends \Eloquent {}
}

namespace App\Models{
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
	class UserTrainingProgress extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $user_id
 * @property int|null $vr_session_id
 * @property int|null $training_module_id
 * @property string $trigger_event_type
 * @property string $hint_type
 * @property array<array-key, mixed>|null $input_context
 * @property string $output_text
 * @property string|null $display_text
 * @property string|null $speech_text
 * @property string $severity
 * @property string|null $recommended_next_action
 * @property bool $is_voice_suitable
 * @property array<array-key, mixed>|null $metadata
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\TrainingModule|null $trainingModule
 * @property-read \App\Models\User $user
 * @property-read \App\Models\VrSession|null $vrSession
 * @method static \Database\Factories\VrAiInteractionFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VrAiInteraction newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VrAiInteraction newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VrAiInteraction query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VrAiInteraction whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VrAiInteraction whereDisplayText($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VrAiInteraction whereHintType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VrAiInteraction whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VrAiInteraction whereInputContext($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VrAiInteraction whereIsVoiceSuitable($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VrAiInteraction whereMetadata($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VrAiInteraction whereOutputText($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VrAiInteraction whereRecommendedNextAction($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VrAiInteraction whereSeverity($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VrAiInteraction whereSpeechText($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VrAiInteraction whereTrainingModuleId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VrAiInteraction whereTriggerEventType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VrAiInteraction whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VrAiInteraction whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VrAiInteraction whereVrSessionId($value)
 * @mixin \Eloquent
 */
	class VrAiInteraction extends \Eloquent {}
}

namespace App\Models{
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
	class VrDevice extends \Eloquent {}
}

namespace App\Models{
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
	class VrPairing extends \Eloquent {}
}

namespace App\Models{
/**
 * @mixin \Illuminate\Database\Eloquent\Builder
 * @property int $id
 * @property int $user_id
 * @property int $device_id
 * @property int $training_module_id
 * @property int|null $pairing_id
 * @property string $session_status
 * @property \Illuminate\Support\Carbon $started_at
 * @property \Illuminate\Support\Carbon|null $last_activity_at
 * @property \Illuminate\Support\Carbon|null $completed_at
 * @property \Illuminate\Support\Carbon|null $interrupted_at
 * @property string|null $current_step
 * @property int $progress_percentage
 * @property array<array-key, mixed>|null $summary_json
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\SessionAnalytics|null $analytics
 * @property-read \App\Models\VrDevice $device
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\VrSessionEvent> $events
 * @property-read int|null $events_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\VrSessionHint> $hints
 * @property-read int|null $hints_count
 * @property-read \App\Models\VrPairing|null $pairing
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\VrSessionStageResult> $stageResults
 * @property-read int|null $stage_results_count
 * @property-read \App\Models\TrainingModule $trainingModule
 * @property-read \App\Models\User $user
 * @method static \Database\Factories\VrSessionFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VrSession newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VrSession newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VrSession query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VrSession whereCompletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VrSession whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VrSession whereCurrentStep($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VrSession whereDeviceId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VrSession whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VrSession whereInterruptedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VrSession whereLastActivityAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VrSession wherePairingId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VrSession whereProgressPercentage($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VrSession whereSessionStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VrSession whereStartedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VrSession whereSummaryJson($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VrSession whereTrainingModuleId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VrSession whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VrSession whereUserId($value)
 * @mixin \Eloquent
 */
	class VrSession extends \Eloquent {}
}

namespace App\Models{
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
	class VrSessionEvent extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $vr_session_id
 * @property int $user_id
 * @property int $training_module_id
 * @property string $hint_type
 * @property string $trigger_reason
 * @property string|null $related_step
 * @property string $displayed_text
 * @property \Illuminate\Support\Carbon $displayed_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\TrainingModule $trainingModule
 * @property-read \App\Models\User $user
 * @property-read \App\Models\VrSession $vrSession
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VrSessionHint newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VrSessionHint newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VrSessionHint query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VrSessionHint whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VrSessionHint whereDisplayedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VrSessionHint whereDisplayedText($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VrSessionHint whereHintType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VrSessionHint whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VrSessionHint whereRelatedStep($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VrSessionHint whereTrainingModuleId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VrSessionHint whereTriggerReason($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VrSessionHint whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VrSessionHint whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VrSessionHint whereVrSessionId($value)
 * @mixin \Eloquent
 */
	class VrSessionHint extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $vr_session_id
 * @property int $user_id
 * @property int $training_module_id
 * @property string $stage_name
 * @property numeric|null $stage_score
 * @property bool $passed
 * @property \Illuminate\Support\Carbon $submitted_at
 * @property array<array-key, mixed>|null $metadata
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\TrainingModule $trainingModule
 * @property-read \App\Models\User $user
 * @property-read \App\Models\VrSession $vrSession
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VrSessionStageResult newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VrSessionStageResult newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VrSessionStageResult query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VrSessionStageResult whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VrSessionStageResult whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VrSessionStageResult whereMetadata($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VrSessionStageResult wherePassed($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VrSessionStageResult whereStageName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VrSessionStageResult whereStageScore($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VrSessionStageResult whereSubmittedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VrSessionStageResult whereTrainingModuleId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VrSessionStageResult whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VrSessionStageResult whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VrSessionStageResult whereVrSessionId($value)
 * @mixin \Eloquent
 */
	class VrSessionStageResult extends \Eloquent {}
}

