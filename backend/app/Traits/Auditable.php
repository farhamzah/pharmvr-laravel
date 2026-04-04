<?php

namespace App\Traits;

use App\Models\AuditLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

trait Auditable
{
    protected static function bootAuditable()
    {
        static::created(function ($model) {
            static::logAuditAction($model, 'Created', null, $model->getAttributes());
        });

        static::updated(function ($model) {
            $changes = $model->getChanges();
            unset($changes['updated_at']);
            
            if (!empty($changes)) {
                $old = array_intersect_key($model->getOriginal(), $changes);
                static::logAuditAction($model, 'Updated', $old, $changes);
            }
        });

        static::deleted(function ($model) {
            static::logAuditAction($model, 'Deleted', $model->getAttributes(), null);
        });
    }

    protected static function logAuditAction($model, string $action, ?array $old, ?array $new)
    {
        AuditLog::create([
            'user_id' => Auth::id(),
            'action' => class_basename($model) . ' ' . $action,
            'model_type' => get_class($model),
            'model_id' => $model->id,
            'old_values' => $old,
            'new_values' => $new,
            'ip_address' => Request::ip(),
            'user_agent' => Request::userAgent(),
        ]);
    }
}
