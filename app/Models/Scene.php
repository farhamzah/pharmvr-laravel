<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Scene - Represents a WebXR simulation scene (e.g., gowning, airlock, weighing).
 * Each scene belongs to a training module and contains ordered steps.
 *
 * @property int $id
 * @property int $training_module_id
 * @property string $slug
 * @property string $title
 * @property string|null $description
 * @property array|null $learning_objectives
 * @property int $order_index
 * @property string $priority
 * @property string $difficulty
 * @property int $estimated_minutes
 * @property string|null $environment_asset
 * @property bool $is_active
 * @property int|null $required_previous_scene_id
 */
class Scene extends Model
{
    use HasFactory;

    public const CANONICAL_SLUGS = [
        'lobby',
        'training_room',
        'hygiene',
        'gowning',
        'airlock',
        'production_corridor',
        'weighing',
        'granulation',
        'final_mixing',
        'tabletting',
        'coating',
        'blistering',
        'secondary_packing',
        'qc_lab',
        'qa_office',
        'warehouse',
        'ppic',
        'purchasing',
        'engineering',
    ];

    public const LEGACY_SLUG_ALIASES = [
        'qc' => 'qc_lab',
        'qc-lab' => 'qc_lab',
        'qa' => 'qa_office',
        'training-safety' => 'training_room',
        'production-corridor' => 'production_corridor',
        'final-mixing' => 'final_mixing',
        'mixing' => 'final_mixing',
        'gudang' => 'warehouse',
        'secondary-packing' => 'secondary_packing',
    ];

    protected $fillable = [
        'training_module_id',
        'slug',
        'title',
        'description',
        'learning_objectives',
        'order_index',
        'priority',
        'difficulty',
        'estimated_minutes',
        'environment_asset',
        'is_active',
        'required_previous_scene_id',
    ];

    protected $casts = [
        'learning_objectives' => 'array',
        'is_active' => 'boolean',
        'order_index' => 'integer',
        'estimated_minutes' => 'integer',
    ];

    // ─── Relationships ─────────────────────────────────────

    public function trainingModule(): BelongsTo
    {
        return $this->belongsTo(TrainingModule::class);
    }

    public function steps(): HasMany
    {
        return $this->hasMany(SceneStep::class)->orderBy('order_index');
    }

    public function requiredPreviousScene(): BelongsTo
    {
        return $this->belongsTo(Scene::class, 'required_previous_scene_id');
    }

    public function sessions(): HasMany
    {
        return $this->hasMany(VrSession::class);
    }

    // ─── Scopes ────────────────────────────────────────────

    public function scopeActive(\Illuminate\Database\Eloquent\Builder $query): \Illuminate\Database\Eloquent\Builder
    {
        return $query->where('is_active', true);
    }

    public function scopePriority(\Illuminate\Database\Eloquent\Builder $query, string $priority): \Illuminate\Database\Eloquent\Builder
    {
        return $query->where('priority', $priority);
    }

    public function scopeOrdered(\Illuminate\Database\Eloquent\Builder $query): \Illuminate\Database\Eloquent\Builder
    {
        return $query->orderBy('order_index');
    }

    public function scopeCanonical(\Illuminate\Database\Eloquent\Builder $query): \Illuminate\Database\Eloquent\Builder
    {
        return $query->whereIn('slug', self::CANONICAL_SLUGS);
    }

    // ─── Helpers ───────────────────────────────────────────

    public static function resolveCanonicalSlug(string $slug): string
    {
        return self::LEGACY_SLUG_ALIASES[$slug] ?? $slug;
    }

    public static function isLegacySlug(string $slug): bool
    {
        return array_key_exists($slug, self::LEGACY_SLUG_ALIASES);
    }

    public function canonicalSlug(): string
    {
        return self::resolveCanonicalSlug($this->slug);
    }

    /**
     * Check if a user has completed this scene's prerequisite.
     */
    public function isUnlockedFor(int|User $user): bool
    {
        $userModel = $user instanceof User ? $user : User::find($user);

        if ($userModel && ($userModel->isSuperAdmin() || $userModel->can_bypass_prerequisites)) {
            return true;
        }

        $userId = $userModel?->id ?? (int) $user;

        if (!$this->required_previous_scene_id) {
            return true;
        }

        return VrSession::where('user_id', $userId)
            ->where('scene_id', $this->required_previous_scene_id)
            ->where('session_status', 'completed')
            ->exists();
    }

    /**
     * Get best score for a user on this scene.
     */
    public function bestScoreFor(int $userId): ?int
    {
        return VrSession::where('user_id', $userId)
            ->where('scene_id', $this->id)
            ->where('session_status', 'completed')
            ->max('total_score');
    }

    /**
     * Get attempt count for a user.
     */
    public function attemptsFor(int $userId): int
    {
        return VrSession::where('user_id', $userId)
            ->where('scene_id', $this->id)
            ->count();
    }

    public function progressFor(int $userId): array
    {
        $latestSession = VrSession::where('user_id', $userId)
            ->where('scene_id', $this->id)
            ->orderByDesc('last_activity_at')
            ->orderByDesc('id')
            ->first();

        return [
            'best_score' => $this->bestScoreFor($userId),
            'attempts' => $this->attemptsFor($userId),
            'latest_status' => $latestSession?->session_status,
            'latest_progress_percentage' => $latestSession?->progress_percentage,
            'completed' => VrSession::where('user_id', $userId)
                ->where('scene_id', $this->id)
                ->where('session_status', 'completed')
                ->exists(),
        ];
    }
}
