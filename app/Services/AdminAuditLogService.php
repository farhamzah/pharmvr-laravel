<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class AdminAuditLogService
{
    private const SENSITIVE_KEYS = [
        'password',
        'password_confirmation',
        'token',
        'access_token',
        'refresh_token',
        'remember_token',
        'authorization',
        'bearer',
        'secret',
        'correct_answer',
        'is_correct',
    ];

    public function record(
        ?Request $request,
        ?User $actor,
        string $action,
        string $targetType,
        int|string|null $targetId = null,
        ?string $targetLabel = null,
        ?array $before = null,
        ?array $after = null
    ): AuditLog {
        $before = $this->withTargetLabel($before, $targetLabel);
        $after = $this->withTargetLabel($after, $targetLabel);

        return AuditLog::create([
            'user_id' => $actor?->id,
            'action' => $action,
            'model_type' => $targetType,
            'model_id' => is_numeric($targetId) ? (int) $targetId : null,
            'old_values' => self::sanitize($before),
            'new_values' => self::sanitize($after),
            'ip_address' => $request?->ip(),
            'user_agent' => $request?->userAgent(),
        ]);
    }

    public function recordModelAction(
        ?Request $request,
        ?User $actor,
        Model $model,
        string $action,
        ?array $before = null,
        ?array $after = null
    ): AuditLog {
        return $this->record(
            $request,
            $actor,
            $action,
            get_class($model),
            $model->getKey(),
            $this->targetLabel($model),
            $before,
            $after
        );
    }

    public static function sanitize(?array $values): ?array
    {
        if ($values === null) {
            return null;
        }

        return self::sanitizeArray($values);
    }

    private function withTargetLabel(?array $values, ?string $targetLabel): ?array
    {
        if ($values === null || $targetLabel === null || array_key_exists('target_label', $values)) {
            return $values;
        }

        return array_merge(['target_label' => $targetLabel], $values);
    }

    private function targetLabel(Model $model): ?string
    {
        foreach (['name', 'title', 'email', 'slug', 'question_text'] as $field) {
            $value = $model->getAttribute($field);

            if (is_string($value) && $value !== '') {
                return $value;
            }
        }

        return null;
    }

    private static function sanitizeArray(array $values): array
    {
        $safe = [];

        foreach ($values as $key => $value) {
            if (self::isSensitiveKey((string) $key)) {
                continue;
            }

            $safe[$key] = is_array($value) ? self::sanitizeArray($value) : $value;
        }

        return $safe;
    }

    private static function isSensitiveKey(string $key): bool
    {
        $normalized = strtolower($key);

        foreach (self::SENSITIVE_KEYS as $sensitiveKey) {
            if ($normalized === $sensitiveKey || str_contains($normalized, $sensitiveKey)) {
                return true;
            }
        }

        return false;
    }
}
