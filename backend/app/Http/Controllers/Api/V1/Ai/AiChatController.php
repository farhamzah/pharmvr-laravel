<?php

namespace App\Http\Controllers\Api\V1\Ai;

use App\Http\Controllers\Controller;
use App\Models\AiChatSession;
use App\Models\AiChatMessage;
use App\Http\Requests\Api\Ai\StartAiChatRequest;
use App\Http\Requests\Api\Ai\AskAiChatRequest;
use App\Services\Ai\AiAnswerService;
use App\Enums\ChatSender;
use App\Enums\ChatSessionStatus;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AiChatController extends Controller
{
    use ApiResponse;

    protected $answerService;

    public function __construct(AiAnswerService $answerService)
    {
        $this->answerService = $answerService;
    }

    public function start(StartAiChatRequest $request)
    {
        $session = AiChatSession::create([
            'user_id' => Auth::id(),
            'platform' => $request->platform,
            'module_id' => $request->module_id,
            'session_title' => $request->session_title ?? 'New Chat Session',
            'assistant_mode' => $request->assistant_mode,
            'status' => ChatSessionStatus::ACTIVE
        ]);

        return $this->successResponse($session, 'Chat session started.', 201);
    }

    public function ask(AskAiChatRequest $request)
    {
        $session = AiChatSession::findOrFail($request->session_id);
        
        // 1. Store user message
        AiChatMessage::create([
            'session_id' => $session->id,
            'sender' => ChatSender::USER,
            'message_text' => $request->question,
        ]);

        $startTime = microtime(true);

        // 2. Generate answer
        $answerData = $this->answerService->generateAnswer($request->question, $session);

        $responseTimeMs = (int)((microtime(true) - $startTime) * 1000);

        // 3. Store assistant message
        $assistantMessage = AiChatMessage::create([
            'session_id' => $session->id,
            'sender' => ChatSender::ASSISTANT,
            'message_text' => $answerData['answer'],
            'cited_sources_json' => $answerData['cited_sources'],
            'response_time_ms' => $responseTimeMs,
            'confidence_score' => $answerData['confidence_score'] ?? null,
            'response_mode' => $answerData['response_mode'] ?? 'grounded',
            'suggested_followups' => $answerData['suggested_followups'] ?? [],
        ]);

        return $this->successResponse($assistantMessage, 'AI response generated.');
    }

    public function sessions()
    {
        $sessions = AiChatSession::where('user_id', Auth::id())
            ->withCount('messages')
            ->latest()
            ->paginate(10);
            
        return $this->successResponse($sessions, 'AI sessions retrieved.');
    }

    public function showSession(AiChatSession $session)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        
        if ($session->user_id !== Auth::id() && !$user->is_admin) {
            abort(403);
        }

        $session->load(['messages' => function ($q) {
            $q->oldest();
        }]);
        
        return $this->successResponse($session, 'Session details retrieved.');
    }

    public function messages(AiChatSession $session)
    {
        if ($session->user_id !== Auth::id() && !Auth::user()->is_admin) {
            abort(403);
        }

        $messages = $session->messages()->oldest()->get();
        return $this->successResponse($messages, 'Messages retrieved.');
    }
}
