<?php

namespace App\Services;

use App\Models\User;
use App\Models\VrSceneLayout;
use Illuminate\Support\Facades\DB;

class VrSceneLayoutPublicationService
{
    public function enforcePublishedState(VrSceneLayout $layout, ?User $user): VrSceneLayout
    {
        if ($layout->status !== VrSceneLayout::STATUS_PUBLISHED) {
            return $layout->refresh();
        }

        return $this->publish($layout, $user, $layout->validation_warnings_json ?? []);
    }

    public function publish(VrSceneLayout $layout, ?User $user, array $warnings = []): VrSceneLayout
    {
        return DB::transaction(function () use ($layout, $user, $warnings) {
            VrSceneLayout::query()
                ->where('scene_slug', $layout->scene_slug)
                ->where('id', '!=', $layout->id)
                ->published()
                ->update([
                    'status' => VrSceneLayout::STATUS_ARCHIVED,
                    'updated_by' => $user?->id,
                    'updated_at' => now(),
                ]);

            $layout->forceFill([
                'status' => VrSceneLayout::STATUS_PUBLISHED,
                'validation_warnings_json' => $warnings,
                'published_by' => $layout->published_by ?? $user?->id,
                'published_at' => $layout->published_at ?? now(),
                'updated_by' => $user?->id,
            ])->save();

            return $layout->refresh();
        });
    }

    public function archive(VrSceneLayout $layout, ?User $user): VrSceneLayout
    {
        $layout->forceFill([
            'status' => VrSceneLayout::STATUS_ARCHIVED,
            'updated_by' => $user?->id,
        ])->save();

        return $layout->refresh();
    }
}
