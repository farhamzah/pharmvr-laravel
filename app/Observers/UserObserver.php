<?php

namespace App\Observers;

use App\Models\User;
use App\Models\AuditLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

class UserObserver
{
    /**
     * Handle the User "created" event.
     */
    public function created(User $user): void
    {
        $this->logAction($user, 'User Created', null, $user->getAttributes());
    }

    /**
     * Handle the User "updated" event.
     */
    public function updated(User $user): void
    {
        $changes = $user->getChanges();
        
        // Don't log if only timestamps changed
        unset($changes['updated_at']);
        if (empty($changes)) return;

        $old = array_intersect_key($user->getOriginal(), $changes);

        $this->logAction($user, 'User Updated', $old, $changes);
    }

    /**
     * Handle the User "deleted" event.
     */
    public function deleted(User $user): void
    {
        $this->logAction($user, 'User Deleted', $user->getAttributes(), null);
    }

    /**
     * Helper to log the action.
     */
    protected function logAction(User $model, string $action, ?array $old, ?array $new): void
    {
        AuditLog::create([
            'user_id' => Auth::id(),
            'action' => $action,
            'model_type' => get_class($model),
            'model_id' => $model->id,
            'old_values' => $old,
            'new_values' => $new,
            'ip_address' => Request::ip(),
            'user_agent' => Request::userAgent(),
        ]);
    }
}
