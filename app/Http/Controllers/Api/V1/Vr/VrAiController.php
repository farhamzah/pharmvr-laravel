<?php

namespace App\Http\Controllers\Api\V1\Vr;

use App\Http\Controllers\Controller;
use App\Models\VrSession;
use App\Services\Ai\VrAiGuideService;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;

class VrAiController extends Controller
{
    use ApiResponse;

    protected $vrAiService;

    public function __construct(VrAiGuideService $vrAiService)
    {
        $this->vrAiService = $vrAiService;
    }

    /**
     * Generate context-aware hint for VR headset.
     * 
     * Endpoint: POST /api/v1/vr/ai/hint
     */
    public function generateHint(Request $request)
    {
        $request->validate([
            'session_id' => 'required|exists:vr_sessions,id',
            'module_slug' => 'nullable|string',
            'current_step' => 'nullable|string',
            'progress_percentage' => 'nullable|numeric',
            'recent_events' => 'required|array',
        ]);

        $session = VrSession::findOrFail($request->session_id);
        
        if (!in_array($session->session_status, ['starting', 'playing'])) {
            return $this->errorResponse('Cannot generate hints for inactive sessions.', 403);
        }

        $context = $request->only(['module_slug', 'current_step', 'progress_percentage']);
        $interaction = $this->vrAiService->generateHint($session, $request->recent_events, $context);

        return $this->successResponse($this->formatVrAiResponse($interaction), 'AI hint generated.');
    }

    /**
     * Generate a passive reminder.
     * 
     * Endpoint: POST /api/v1/vr/ai/reminder
     */
    public function generateReminder(Request $request)
    {
        $request->validate([
            'session_id' => 'required|exists:vr_sessions,id',
            'module_slug' => 'nullable|string',
            'current_step' => 'nullable|string',
            'progress_percentage' => 'nullable|numeric',
            'topic' => 'required|string',
        ]);

        $session = VrSession::findOrFail($request->session_id);
        
        $context = $request->only(['module_slug', 'current_step', 'progress_percentage']);
        $interaction = $this->vrAiService->generateReminder($session, $request->topic, $context);

        return $this->successResponse($this->formatVrAiResponse($interaction), 'AI reminder generated.');
    }

    /**
     * Generate event feedback.
     * 
     * Endpoint: POST /api/v1/vr/ai/feedback
     */
    public function generateFeedback(Request $request)
    {
        $request->validate([
            'session_id' => 'required|exists:vr_sessions,id',
            'module_slug' => 'nullable|string',
            'current_step' => 'nullable|string',
            'progress_percentage' => 'nullable|numeric',
            'event_type' => 'required|string',
            'event' => 'required|array',
            'user_action_summary' => 'nullable|string',
        ]);

        $session = VrSession::findOrFail($request->session_id);
        
        $context = $request->only(['module_slug', 'current_step', 'progress_percentage', 'user_action_summary', 'event_type']);
        $interaction = $this->vrAiService->generateFeedback($session, $request->event, $context);

        return $this->successResponse($this->formatVrAiResponse($interaction), 'AI feedback generated.');
    }

    /**
     * Standardize response for VR Consumption.
     */
    private function formatVrAiResponse(\App\Models\VrAiInteraction $interaction): array
    {
        return [
            'interaction_id' => $interaction->id,
            'mode' => $interaction->hint_type,
            'short_text' => $interaction->output_text,
            'display_text' => $interaction->display_text,
            'speech_text' => $interaction->speech_text,
            'severity' => $interaction->severity,
            'recommended_next_action' => $interaction->recommended_next_action,
            'metadata' => $interaction->metadata,
        ];
    }
}
