<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QuestionBankOption extends Model
{
    use HasFactory;

    protected $fillable = [
        'question_bank_item_id',
        'option_label',
        'option_text',
        'is_correct',
        'sort_order',
    ];

    protected $casts = [
        'is_correct' => 'boolean',
    ];

    /**
     * Get the question that owns the option.
     */
    public function questionBankItem()
    {
        return $this->belongsTo(QuestionBankItem::class);
    }
}
