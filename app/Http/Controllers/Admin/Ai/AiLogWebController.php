<?php

namespace App\Http\Controllers\Admin\Ai;

use App\Http\Controllers\Controller;
use App\Models\AiChatMessage;
use App\Models\AiChatSession;
use Illuminate\Http\Request;

class AiLogWebController extends Controller
{
    public function index(Request $request)
    {
        $query = AiChatMessage::with(['session.user', 'session.module'])
            ->where('sender', 'assistant') // Usually logs focus on AI responses
            ->latest();

        // Filters
        if ($request->filled('platform')) {
            $query->whereHas('session', function($q) use ($request) {
                $q->where('platform', $request->platform);
            });
        }

        if ($request->filled('mode')) {
            $query->whereHas('session', function($q) use ($request) {
                $q->where('assistant_mode', $request->mode);
            });
        }

        if ($request->filled('response_mode')) {
            $query->where('response_mode', $request->response_mode);
        }

        if ($request->filled('module_id')) {
            $query->whereHas('session', function($q) use ($request) {
                $q->where('module_id', $request->module_id);
            });
        }

        $logs = $query->paginate(30)->withQueryString();

        return view('admin.ai.logs.index', compact('logs'));
    }
}
