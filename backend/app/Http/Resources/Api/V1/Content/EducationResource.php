<?php

namespace App\Http\Resources\Api\V1\Content;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class EducationResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $thumbnailUrl = $this->thumbnail_url;
        $typeNormalized = strtolower($this->type ?? '');
        
        // If it's a module type, prefer the parent TrainingModule's cover image
        if ($typeNormalized === 'module' && $this->trainingModule) {
            $thumbnailUrl = $this->trainingModule->cover_image_url ?? $thumbnailUrl;
        }

        // Handle path prepending for relative paths
        if ($thumbnailUrl && !filter_var($thumbnailUrl, FILTER_VALIDATE_URL)) {
            // Remove storage/ if already present to avoid double prefix
            $cleanPath = str_replace('storage/', '', $thumbnailUrl);
            $thumbnailUrl = Storage::disk('public')->url($cleanPath);
        }

        // Auto-generate YouTube thumbnail if missing
        if ($typeNormalized === 'video' && !$thumbnailUrl && $this->video_id && strtolower($this->platform ?? '') === 'youtube') {
            $thumbnailUrl = "https://i.ytimg.com/vi/{$this->video_id}/hqdefault.jpg";
        }

        // Emulator Bridge: Standardize localhost to 10.0.2.2 if accessed from emulator
        // but dynamic URL should handle this if request host is 10.0.2.2

        $data = [
            'id'                 => $this->id,
            'training_module_id' => $this->training_module_id,
            'title'              => $this->title,
            'slug'               => ($typeNormalized === 'module' && $this->trainingModule) ? $this->trainingModule->slug : $this->slug,
            'description'        => $this->description,
            'code'               => $this->code,
            'type'               => $this->type,
            'category'           => $this->category,
            'level'              => $this->level,
            'thumbnail_url'      => $thumbnailUrl,
            'video_url'          => $this->video_url,
            'video_id'           => $this->video_id,
            'platform'           => $this->platform,
            'duration_minutes'   => $this->duration_minutes,
            'created_at'         => $this->created_at?->toIso8601String(),
            'updated_at'         => $this->updated_at?->toIso8601String(),
        ];

        // CTA & Recommendations
        $data['recommended_action'] = [
            'label'  => $this->next_step_label ?? 'Lanjut Belajar',
            'action' => $this->next_step_action ?? 'open_next',
        ];

        if ($typeNormalized === 'module') {
            $data['learning_path'] = $this->learning_path ?? [
                'has_pre_test'  => true,
                'has_vr_sim'    => true,
                'has_post_test' => true,
            ];
            $data['cta_label'] = 'Mulai Belajar';

            // Journey Progress from UserTrainingProgress
            $user = $request->user();
            if ($user && $this->training_module_id) {
                $progress = \App\Models\UserTrainingProgress::where('user_id', $user->id)
                    ->where('training_module_id', $this->training_module_id)
                    ->first();

                $data['journey'] = [
                    'pre_test' => [
                        'status' => $progress?->pre_test_status ?? 'available',
                        'label'  => 'Pre-Test',
                    ],
                    'vr_sim' => [
                        'status' => $progress?->vr_status ?? 'locked',
                        'label'  => 'VR Sim',
                    ],
                    'post_test' => [
                        'status' => $progress?->post_test_status ?? 'locked',
                        'label'  => 'Post-Test',
                    ],
                    'last_active_step' => $progress?->last_active_step ?? null,
                ];

                if (!$this->next_step_label) {
                    $nextAction = 'start_assessment';
                    $nextLabel  = 'Mulai Pre-Test';

                    if (($progress?->pre_test_status ?? '') === 'passed') {
                        $nextAction = 'open_vr';
                        $nextLabel  = 'Mulai Simulasi VR';
                    }

                    if (($progress?->vr_status ?? '') === 'completed') {
                        $nextAction = 'start_assessment'; // for post-test
                        $nextLabel  = 'Mulai Post-Test';
                    }

                    if (($progress?->post_test_status ?? '') === 'passed') {
                        $nextAction = 'open_summary';
                        $nextLabel  = 'Lihat Hasil';
                    }

                    $data['recommended_action'] = [
                        'label'  => $nextLabel,
                        'action' => $nextAction,
                    ];
                }
            }
        }

        if ($typeNormalized === 'video') {
            $data['cta_label'] = 'Tonton Video';
            if (strtolower($this->platform ?? '') === 'youtube' && $this->video_id) {
                $data['video_url'] = "https://www.youtube.com/watch?v={$this->video_id}";
            }
            if (!$this->next_step_label) {
                $data['recommended_action'] = [
                    'label'  => 'Coba Simulasi Terkait',
                    'action' => 'open_simulation',
                ];
            }
        }

        if ($typeNormalized === 'document') {
            $data['cta_label'] = 'Buka Dokumen';
            if (!$this->next_step_label) {
                $data['recommended_action'] = [
                    'label'  => 'Tanya PharmAI',
                    'action' => 'open_ai',
                ];
            }
        }

        return $data;
    }
}
