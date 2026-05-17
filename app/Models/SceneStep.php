<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * SceneStep - Individual step within a WebXR scene.
 * Steps define the required interactions, scoring, and validation rules.
 *
 * @property int $id
 * @property int $scene_id
 * @property string $slug
 * @property string $title
 * @property string|null $description
 * @property int $order_index
 * @property bool $is_required
 * @property float $scoring_weight
 * @property int $max_score
 * @property int $mistake_penalty
 * @property string $interaction_type
 * @property array|null $validation_rule
 */
class SceneStep extends Model
{
    use HasFactory;

    protected $fillable = [
        'scene_id',
        'slug',
        'title',
        'description',
        'order_index',
        'is_required',
        'scoring_weight',
        'max_score',
        'mistake_penalty',
        'interaction_type',
        'validation_rule',
    ];

    protected $casts = [
        'is_required' => 'boolean',
        'scoring_weight' => 'decimal:2',
        'max_score' => 'integer',
        'mistake_penalty' => 'integer',
        'order_index' => 'integer',
        'validation_rule' => 'array',
    ];

    // ─── Relationships ─────────────────────────────────────

    public function scene(): BelongsTo
    {
        return $this->belongsTo(Scene::class);
    }

    public function completions(): HasMany
    {
        return $this->hasMany(VrStepCompletion::class);
    }
}
