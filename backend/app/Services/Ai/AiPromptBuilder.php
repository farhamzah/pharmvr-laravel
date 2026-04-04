<?php

namespace App\Services\Ai;

class AiPromptBuilder
{
    /**
     * Get system prompt based on mode.
     */
    public function getSystemPrompt(string $mode): string
    {
        return match ($mode) {
            'app_chat' => $this->getAppSystemPrompt(),
            'vr_hint' => $this->getVrSystemPrompt(),
            'vr_reminder' => $this->getVrReminderPrompt(),
            'vr_feedback' => $this->getVrFeedbackPrompt(),
            default => $this->getAppSystemPrompt(),
        };
    }

    public function getAppSystemPrompt(): string
    {
        return "You are PharmAI, a senior Pharmaceutical Consultant and CPOB/GMP Auditor.
        Focus: Pharmaceutical learning, GMP, CPOB, cleanroom procedures, production workflow, hygiene, QA/QC, and documentation.
        Rules:
        1. Strictly stay within the pharmaceutical domain.
        2. If a question is outside domain, respond with a polite refusal.
        3. Never fabricate regulations.
        4. Answer with moderate detail for educational purposes.
        5. Use Indonesian as primary language.";
    }

    public function getVrSystemPrompt(): string
    {
        return "You are the PharmVR Guide (Cleanroom Supervisor). 
        Role: Provide immediate, firm, and concise hints in VR.
        Rules: 
        1. MAX 20 words per response.
        2. Focus on sterile protocols and immediate actions.
        3. Mention the severity (info/warning) if appropriate.
        4. Use Indonesian.";
    }

    public function getVrReminderPrompt(): string
    {
        return "You are the PharmVR Hygiene Reminder service.
        Role: Give brief reminders about hygiene and protocol maintenance.
        Rules: Max 15 words. Focus on hand sanitization, gowning, and movement speed.";
    }

    public function getVrFeedbackPrompt(): string
    {
        return "You are the PharmVR Session Evaluator.
        Role: Provide brief evaluation for a specific action or event.
        Goal: Explain WHY an action was right or wrong based on CPOB.
        Rules: Max 30 words. State the impact on product safety and recommend a fix if wrong.";
    }

    public function buildVrContext(array $events, array $meta = []): string
    {
        $module = $meta['module_slug'] ?? 'Unknown Module';
        $step = $meta['current_step'] ?? 'Unknown Step';
        $progress = $meta['progress_percentage'] ?? '0';

        $context = "Situational Context:\n- Module: $module\n- Current Step: $step\n- Progress: $progress%\n\n";
        
        if (!empty($events)) {
            $context .= "Recent events in VR session:\n";
            foreach ($events as $event) {
                $type = $event['event_type'] ?? ($meta['event_type'] ?? 'action');
                $context .= "- {$type}: " . json_encode($event['event_payload'] ?? ($event ?: [])) . "\n";
            }
        }

        if (isset($meta['user_action_summary'])) {
            $context .= "\nUser Action Summary: " . $meta['user_action_summary'];
        }

        if (isset($meta['factual_description'])) {
            $status = ($meta['is_correct'] ?? true) ? 'SUCCESS' : 'FAILURE';
            $context .= "\n\nGROUND TRUTH (Rule Engine):\n";
            $context .= "- Status: $status\n";
            $context .= "- Rule ID: " . ($meta['rule_id'] ?? 'GENERIC') . "\n";
            $context .= "- Fact: " . $meta['factual_description'] . "\n";
            $context .= "Narrative Requirement: Explain this fact based on CPOB/GMP principles.";
        }

        return $context;
    }
}
