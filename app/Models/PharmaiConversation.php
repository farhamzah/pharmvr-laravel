<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
class PharmaiConversation extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'title',
        'last_message_at',
        'status',
    ];

    protected $casts = [
        'last_message_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function messages()
    {
        return $this->hasMany(PharmaiMessage::class, 'conversation_id');
    }
}
