<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class AiAvatarScenePrompt extends Model
{
    use HasFactory;

    protected $fillable = [
        'avatar_profile_id',
        'scene_key',
        'object_key',
        'prompt_title',
        'prompt_text',
        'suggested_questions_json',
        'is_active',
    ];

    protected $casts = [
        'suggested_questions_json' => 'array',
        'is_active' => 'boolean',
    ];

    public function avatar()
    {
        return $this->belongsTo(AiAvatarProfile::class, 'avatar_profile_id');
    }

    /**
     * Scope a query to only include active prompts.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
