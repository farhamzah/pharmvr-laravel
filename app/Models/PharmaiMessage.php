<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
class PharmaiMessage extends Model
{
    use HasFactory;

    protected $fillable = [
        'conversation_id',
        'role',
        'content',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
    ];

    public function conversation()
    {
        return $this->belongsTo(PharmaiConversation::class);
    }
}
