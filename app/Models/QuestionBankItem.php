<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QuestionBankItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'module_id',
        'question_text',
        'usage_scope',
        'difficulty',
        'explanation',
        'is_active',
        'created_by',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'is_active' => 'boolean',
        'usage_scope' => \App\Enums\QuestionUsageScope::class,
    ];

    /**
     * Get the module that owns the question.
     */
    public function trainingModule()
    {
        return $this->belongsTo(TrainingModule::class, 'module_id');
    }

    /**
     * Get the options for the question.
     */
    public function options()
    {
        return $this->hasMany(QuestionBankOption::class);
    }

    /**
     * Get the creator of the question.
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Scope a query to only include questions for a specific scope.
     */
    public function scopeForScope($query, $scope)
    {
        return $query->whereIn('usage_scope', [$scope, 'both']);
    }

    /**
     * Scope a query to only include active questions.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
