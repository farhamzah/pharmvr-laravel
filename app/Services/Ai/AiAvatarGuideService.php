<?php

namespace App\Services\Ai;

use App\Models\AiAvatarProfile;
use App\Models\AiAvatarScenePrompt;
use App\Models\TrainingModule;
use Illuminate\Support\Str;

class AiAvatarGuideService
{
    protected $answerService;
    protected $retrievalService;

    public function __construct(AiAnswerService $answerService, AiRetrievalService $retrievalService)
    {
        $this->answerService = $answerService;
        $this->retrievalService = $retrievalService;
    }

    /**
     * Provide concise guidance for the VR Avatar based on interaction mode.
     */
    public function getGuidance(AiAvatarProfile $avatar, string $sceneKey, ?string $objectKey = null, string $mode = 'greeting'): array
    {
        $query = AiAvatarScenePrompt::where('avatar_profile_id', $avatar->id)
            ->where('scene_key', $sceneKey)
            ->where('is_active', true);

        if ($objectKey) {
            $query->where('object_key', $objectKey);
        }

        $activePrompt = $query->first();

        // Mode-based logic
        $answer = "";
        $followups = [];

        if ($mode === 'greeting') {
            $answer = $avatar->greeting_text ?? "Halo, saya " . $avatar->name . ". Ada yang bisa saya bantu di " . Str::title(str_replace('_', ' ', $sceneKey)) . "?";
            $followups = ['Apa tugas Anda?', 'Jelaskan ruangan ini'];
        } elseif ($activePrompt) {
            $answer = $activePrompt->prompt_text;
            $followups = $activePrompt->suggested_questions_json ?? [];
        } else {
            $answer = "Saya tidak menemukan informasi spesifik tentang ini, namun saya siap membantu pertanyaan Anda seputar GMP.";
            $followups = ['Apa itu GMP?'];
        }

        return [
            'avatar' => [
                'name' => $avatar->name,
                'slug' => $avatar->slug,
                'role' => $avatar->role_title
            ],
            'answer' => Str::limit($answer, 200),
            'cited_sources' => [], // Guidance usually doesn't have citations unless grounded
            'suggested_followups' => $followups,
            'scene_context' => $sceneKey,
            'interaction_mode' => $mode,
            'response_mode' => 'vr_concise'
        ];
    }

    /**
     * Answer a specific question in the character of the Avatar.
     */
    public function askAvatar(AiAvatarProfile $avatar, string $question, ?TrainingModule $module = null, string $mode = 'ask'): array
    {
        $result = $this->answerService->generateAnswer($question, new \App\Models\AiChatSession([
            'module_id' => $module?->id ?? $avatar->default_module_id,
            'platform' => \App\Enums\ChatPlatform::VR,
            'assistant_mode' => 'vr_concise'
        ]));

        // Wrap the answer in the persona's voice if necessary
        $result['answer'] = "Sebagai " . $avatar->role_title . ": " . $result['answer'];
        
        return [
            'avatar' => [
                'name' => $avatar->name,
                'slug' => $avatar->slug
            ],
            'answer' => $result['answer'],
            'cited_sources' => $result['cited_sources'] ?? [],
            'suggested_followups' => $result['suggested_followups'] ?? [],
            'interaction_mode' => $mode,
            'response_mode' => $result['response_mode']
        ];
    }
}
