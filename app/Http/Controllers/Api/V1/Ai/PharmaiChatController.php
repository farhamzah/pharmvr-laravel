<?php

namespace App\Http\Controllers\Api\V1\Ai;

use App\Http\Controllers\Controller;
use App\Models\PharmaiConversation;
use App\Services\Ai\AiChatService;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;

class PharmaiChatController extends Controller
{
    use ApiResponse;

    protected $aiChatService;

    public function __construct(AiChatService $aiChatService)
    {
        $this->aiChatService = $aiChatService;
    }

    /**
     * List user conversations.
     */
    public function index(Request $request)
    {
        $conversations = $request->user()->pharmaiConversations()
            ->orderBy('last_message_at', 'desc')
            ->get();

        return $this->successResponse($conversations, 'Conversations retrieved successfully.');
    }

    /**
     * Start a new conversation.
     */
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'nullable|string|max:255',
        ]);

        $conversation = $request->user()->pharmaiConversations()->create([
            'title' => $request->title ?? 'New Chat',
            'status' => 'active',
        ]);

        return $this->successResponse($conversation, 'Conversation created successfully.', 201);
    }

    /**
     * Show conversation details and messages.
     */
    public function show(PharmaiConversation $conversation)
    {
        $this->authorize('view', $conversation);

        $conversation->load(['messages' => function ($query) {
            $query->orderBy('created_at', 'asc');
        }]);

        return $this->successResponse($conversation, 'Conversation details retrieved.');
    }

    /**
     * Send message and get AI response.
     */
    public function sendMessage(Request $request, PharmaiConversation $conversation)
    {
        $this->authorize('update', $conversation);

        $request->validate([
            'message' => 'required|string',
        ]);

        $aiMessage = $this->aiChatService->sendMessage($conversation, $request->message);

        return $this->successResponse($aiMessage, 'AI response generated.');
    }

    /**
     * Stateless chat (no conversation history).
     */
    public function statelessChat(Request $request)
    {
        $request->validate([
            'message' => 'required|string',
            'context' => 'nullable|array',
        ]);

        $response = $this->aiChatService->statelessChat($request->message, $request->context ?? []);

        return $this->successResponse($response, 'AI response generated (stateless).');
    }
}
