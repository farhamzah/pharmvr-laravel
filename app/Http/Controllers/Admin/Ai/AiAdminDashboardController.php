<?php

namespace App\Http\Controllers\Admin\Ai;

use App\Http\Controllers\Controller;
use App\Models\AiKnowledgeSource;
use App\Models\AiKnowledgeChunk;
use App\Models\AiChatSession;
use App\Models\AiChatMessage;
use App\Models\AiAvatarProfile;
use App\Models\VrAiInteraction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AiAdminDashboardController extends Controller
{
    public function index()
    {
        $totalSources = AiKnowledgeSource::count();
        $activeSources = AiKnowledgeSource::where('is_active', true)->count();
        $totalChunks = AiKnowledgeChunk::count();
        $totalSessions = AiChatSession::count();
        
        // Recent Questions (from Chat Messages or Sessions)
        // We'll use AiUsageLog or similar if available, otherwise Chat messages.
        // For now, let's assume we want recent sessions with their last message.
        $recentSessions = AiChatSession::with(['user', 'module'])
            ->latest()
            ->take(10)
            ->get();
            
        $avatarCount = AiAvatarProfile::count();
        
        // Advanced Metrics
        $totalAssistantMessages = AiChatMessage::where('sender', 'assistant')->count();
        $groundedCount = AiChatMessage::where('sender', 'assistant')->where('response_mode', 'grounded')->count();
        $insufficientContextCount = AiChatMessage::where('sender', 'assistant')->where('response_mode', 'neutral')->count();
        $restrictedQueryCount = AiChatMessage::where('sender', 'assistant')->where('response_mode', 'restricted')->count();
        
        $groundedRate = $totalAssistantMessages > 0 
            ? round(($groundedCount / $totalAssistantMessages) * 100, 1) 
            : 0;

        // Top queried topics (grouped by module or topic field in sources)
        $topTopics = AiKnowledgeSource::select('topic', DB::raw('count(*) as total'))
            ->whereNotNull('topic')
            ->groupBy('topic')
            ->orderBy('total', 'desc')
            ->take(5)
            ->get();

        return view('admin.ai.dashboard', compact(
            'totalSources',
            'activeSources',
            'totalChunks',
            'totalSessions',
            'recentSessions',
            'avatarCount',
            'topTopics',
            'groundedRate',
            'insufficientContextCount',
            'restrictedQueryCount'
        ));
    }
}
