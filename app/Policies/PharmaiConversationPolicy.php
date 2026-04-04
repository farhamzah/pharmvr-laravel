<?php

namespace App\Policies;

use App\Models\PharmaiConversation;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class PharmaiConversationPolicy
{
    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, PharmaiConversation $pharmaiConversation): bool
    {
        return $user->id === $pharmaiConversation->user_id;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, PharmaiConversation $pharmaiConversation): bool
    {
        return $user->id === $pharmaiConversation->user_id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, PharmaiConversation $pharmaiConversation): bool
    {
        return $user->id === $pharmaiConversation->user_id;
    }
}
