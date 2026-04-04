<?php

namespace App\Services\Ai;

use App\Models\PharmaiConversation;
use App\Models\PharmaiMessage;
use App\Models\AiUsageLog;
use App\Services\Ai\Providers\AiProviderInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class AiChatService
{
    protected AiProviderInterface $provider;
    protected AiPromptBuilder $promptBuilder;

    public function __construct(AiProviderInterface $provider, AiPromptBuilder $promptBuilder)
    {
        $this->provider = $provider;
        $this->promptBuilder = $promptBuilder;
    }

    /**
     * Handle persistent multi-turn chat.
     */
    public function sendMessage(PharmaiConversation $conversation, string $userInput)
    {
        // 1. Get History
        $history = $conversation->messages()
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get()
            ->reverse()
            ->map(fn($m) => ['role' => $m->role, 'content' => $m->content])
            ->toArray();

        // 2. Prepare Payload
        $messages = [
            ['role' => 'system', 'content' => $this->promptBuilder->getSystemPrompt('app_chat')]
        ];
        $messages = array_merge($messages, $history);
        $messages[] = ['role' => 'user', 'content' => $userInput];

        // 3. Call Provider
        $aiResponse = $this->provider->generateResponse($messages);

        // 4. Persistence
        return DB::transaction(function () use ($conversation, $userInput, $aiResponse) {
            $conversation->messages()->create([
                'role' => 'user',
                'content' => $userInput
            ]);

            $aiMsg = $conversation->messages()->create([
                'role' => 'assistant',
                'content' => $aiResponse->content,
                'metadata' => $aiResponse->metadata
            ]);

            $this->logUsage($conversation->user_id, 'app_chat', $aiMsg, [
                'conversation_id' => $conversation->id,
                'ai_response' => $aiResponse
            ]);

            $conversation->update(['last_message_at' => now()]);

            return $aiMsg;
        });
    }

    /**
     * Stateless quick chat.
     */
    public function statelessChat(string $userInput, array $context = [])
    {
        $messages = [
            ['role' => 'system', 'content' => $this->promptBuilder->getSystemPrompt('app_chat')],
        ];

        if (!empty($context)) {
            $messages[] = ['role' => 'system', 'content' => "Context: " . json_encode($context)];
        }

        $messages[] = ['role' => 'user', 'content' => $userInput];

        $aiResponse = $this->provider->generateResponse($messages);

        $this->logUsage(Auth::id() ?? 0, 'app_stateless_chat', null, [
            'ai_response' => $aiResponse,
            'metadata' => $context
        ]);

        return $aiResponse->toArray();
    }

    /**
     * Generate post-session evaluation.
     */
    public function generateEvaluation(\App\Models\SessionAnalytics $analytics)
    {
        $analytics->load(['vrSession.trainingModule', 'vrSession.user']);
        $session = $analytics->vrSession;

        $messages = [
            ['role' => 'system', 'content' => "You are a senior GMP trainer. Provide a constructive, professional evaluation of the learner's VR session performance. Highlight strengths and areas for improvement based on the provided metrics. Keep it under 100 words. Use Indonesian."],
            ['role' => 'user', 'content' => "Session Stats:
            - Module: {$session->trainingModule->title}
            - Accuracy: {$analytics->accuracy_score}/100
            - Speed score: {$analytics->speed_score}/100
            - Total Breaches: {$analytics->breach_count}
            - Duration: " . floor($analytics->duration_seconds / 60) . "m " . ($analytics->duration_seconds % 60) . "s"]
        ];

        $aiResponse = $this->provider->generateResponse($messages);

        $this->logUsage($session->user_id, 'session_evaluation', $analytics, [
            'vr_session_id' => $session->id,
            'ai_response' => $aiResponse
        ]);

        return $analytics->update([
            'metrics_json' => array_merge($analytics->metrics_json ?? [], [
                'ai_evaluation' => $aiResponse->content,
                'ai_metadata' => $aiResponse->metadata
            ])
        ]);
    }

    /**
     * Internal telemetry logging.
     */
    private function logUsage(int $userId, string $type, $source, array $params)
    {
        $aiResponse = $params['ai_response'] ?? null;
        $meta = $aiResponse ? $aiResponse->metadata : [];

        AiUsageLog::create([
            'user_id' => $userId,
            'interaction_type' => $type,
            'source_type' => $source ? get_class($source) : 'stateless',
            'source_id' => $source ? $source->id : 0,
            'provider_name' => $meta['provider'] ?? 'unknown',
            'model_name' => $meta['model'] ?? 'unknown',
            'latency_ms' => $meta['latency'] ?? null,
            'prompt_tokens' => $meta['tokens']['prompt'] ?? null,
            'completion_tokens' => $meta['tokens']['completion'] ?? null,
            'total_tokens' => $meta['tokens']['total'] ?? null,
            'domain_mode' => $params['domain_mode'] ?? null,
            'conversation_id' => $params['conversation_id'] ?? null,
            'vr_session_id' => $params['vr_session_id'] ?? null,
            'metadata' => array_merge($params['metadata'] ?? [], [
                'is_voice' => $params['is_voice'] ?? false
            ])
        ]);
    }
}
