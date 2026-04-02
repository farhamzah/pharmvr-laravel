<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class AiChatSession extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'platform',
        'module_id',
        'session_title',
        'assistant_mode',
        'status',
    ];

    protected $casts = [
        'platform' => \App\Enums\ChatPlatform::class,
        'status' => \App\Enums\ChatSessionStatus::class,
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function module()
    {
        return $this->belongsTo(TrainingModule::class, 'module_id');
    }

    public function messages()
    {
        return $this->hasMany(AiChatMessage::class, 'session_id');
    }
}
