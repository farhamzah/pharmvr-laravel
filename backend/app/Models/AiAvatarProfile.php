<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class AiAvatarProfile extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'role_title',
        'persona_text',
        'greeting_text',
        'default_module_id',
        'allowed_topics_json',
        'avatar_model_path',
        'voice_style',
        'is_active',
    ];

    protected $casts = [
        'allowed_topics_json' => 'array',
        'is_active' => 'boolean',
    ];

    public function defaultModule()
    {
        return $this->belongsTo(TrainingModule::class, 'default_module_id');
    }

    public function scenePrompts()
    {
        return $this->hasMany(AiAvatarScenePrompt::class, 'avatar_profile_id');
    }

    /**
     * Scope a query to only include active profiles.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
