<?php

namespace App\Services\Ai;

use App\Models\VrSession;
use App\Models\VrAiInteraction;
use App\Models\AiUsageLog;
use App\Services\Ai\Providers\AiProviderInterface;

class VrAiGuideService
{
    protected AiProviderInterface $provider;
    protected AiPromptBuilder $promptBuilder;
    protected \App\Services\VrRuleService $ruleService;

    public function __construct(
        AiProviderInterface $provider, 
        AiPromptBuilder $promptBuilder,
        \App\Services\VrRuleService $ruleService
    ) {
        $this->provider = $provider;
        $this->promptBuilder = $promptBuilder;
        $this->ruleService = $ruleService;
    }

    /**
     * Generate a contextual hint in VR.
     */
    public function generateHint(VrSession $session, array $recentEvents, array $context = []): VrAiInteraction
    {
        $contextString = $this->promptBuilder->buildVrContext($recentEvents, $context);
        
        $messages = [
            ['role' => 'system', 'content' => $this->promptBuilder->getSystemPrompt('vr_hint')],
            ['role' => 'user', 'content' => "Context: $contextString. Give a hint."]
        ];

        $aiResponse = $this->provider->generateResponse($messages, ['is_vr' => true]);

        $interaction = VrAiInteraction::create([
            'user_id' => $session->user_id,
            'vr_session_id' => $session->id,
            'training_module_id' => $session->training_module_id,
            'trigger_event_type' => $recentEvents[0]['event_type'] ?? 'unknown',
            'hint_type' => 'hint',
            'input_context' => array_merge($recentEvents, ['meta' => $context]),
            'output_text' => $aiResponse->content,
            'display_text' => $aiResponse->content,
            'speech_text' => $aiResponse->content,
            'severity' => 'info',
            'recommended_next_action' => 'Proceed with caution',
            'is_voice_suitable' => true,
            'metadata' => $aiResponse->metadata
        ]);

        $this->logUsage($session->user_id, 'vr_hint', $interaction, [
            'vr_session_id' => $session->id,
            'ai_response' => $aiResponse,
            'domain_mode' => 'cleanroom_supervisor'
        ]);

        return $interaction;
    }

    /**
     * Generate a passive reminder.
     */
    public function generateReminder(VrSession $session, string $topic, array $context = []): VrAiInteraction
    {
        $contextString = $this->promptBuilder->buildVrContext([], $context);
        
        $messages = [
            ['role' => 'system', 'content' => $this->promptBuilder->getSystemPrompt('vr_reminder')],
            ['role' => 'user', 'content' => "Topic: $topic. Context: $contextString"]
        ];

        $aiResponse = $this->provider->generateResponse($messages, ['is_vr' => true]);

        $interaction = VrAiInteraction::create([
            'user_id' => $session->user_id,
            'vr_session_id' => $session->id,
            'training_module_id' => $session->training_module_id,
            'trigger_event_type' => 'timer_reminder',
            'hint_type' => 'reminder',
            'input_context' => ['topic' => $topic, 'meta' => $context],
            'output_text' => $aiResponse->content,
            'display_text' => $aiResponse->content,
            'speech_text' => $aiResponse->content,
            'severity' => 'info',
            'is_voice_suitable' => true,
            'metadata' => $aiResponse->metadata
        ]);

        $this->logUsage($session->user_id, 'vr_reminder', $interaction, [
            'vr_session_id' => $session->id,
            'ai_response' => $aiResponse,
            'domain_mode' => 'hygiene_reminder'
        ]);

        return $interaction;
    }

    /**
     * Generate event feedback.
     */
    public function generateFeedback(VrSession $session, array $event, array $context = []): VrAiInteraction
    {
        // 1. Logic Layer: Deterministic Evaluation
        $eventType = $context['event_type'] ?? ($event['event_type'] ?? 'user_action');
        $ruleResult = $this->ruleService->evaluate($eventType, $event);

        // 2. Narrative Layer: AI Explanation based on Facts
        $augmentedContext = array_merge($context, [
            'rule_id' => $ruleResult['rule_id'],
            'factual_description' => $ruleResult['factual_description'],
            'is_correct' => $ruleResult['is_correct']
        ]);

        $contextString = $this->promptBuilder->buildVrContext([$event], $augmentedContext);
        
        $messages = [
            ['role' => 'system', 'content' => $this->promptBuilder->getSystemPrompt('vr_feedback')],
            ['role' => 'user', 'content' => "Action Evaluate: $contextString."]
        ];

        $aiResponse = $this->provider->generateResponse($messages, ['is_vr' => true]);

        // 3. Persist Final Result
        $interaction = VrAiInteraction::create([
            'user_id' => $session->user_id,
            'vr_session_id' => $session->id,
            'training_module_id' => $session->training_module_id,
            'trigger_event_type' => $eventType,
            'hint_type' => 'feedback',
            'input_context' => array_merge($event, ['meta' => $augmentedContext]),
            'output_text' => $aiResponse->content,
            'display_text' => $aiResponse->content,
            'speech_text' => $aiResponse->content,
            'severity' => $ruleResult['severity'], // Logic-driven severity
            'is_voice_suitable' => true,
            'metadata' => array_merge($aiResponse->metadata, ['rule_result' => $ruleResult])
        ]);

        $this->logUsage($session->user_id, 'vr_feedback', $interaction, [
            'vr_session_id' => $session->id,
            'ai_response' => $aiResponse,
            'domain_mode' => 'session_evaluator'
        ]);

        return $interaction;
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
            'vr_session_id' => $params['vr_session_id'] ?? null,
            'metadata' => array_merge($params['metadata'] ?? [], [
                'is_voice' => true
            ])
        ]);
    }
}
