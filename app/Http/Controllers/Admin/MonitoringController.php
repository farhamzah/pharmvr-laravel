<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\VrSession;
use App\Models\VrAiInteraction;
use App\Models\PharmaiConversation;
use Illuminate\Http\Request;

class MonitoringController extends Controller
{
    /**
     * Display VR sessions.
     */
    public function vrSessions()
    {
        $sessions = VrSession::with('user')->latest()->paginate(15);
        return view('admin.monitoring.vr-sessions', compact('sessions'));
    }

    /**
     * Display AI interactions.
     */
    public function aiInteractions()
    {
        $interactions = VrAiInteraction::with(['user', 'vrSession'])->latest()->paginate(15);
        $conversations = PharmaiConversation::with('user')->latest()->paginate(15);
        
        return view('admin.monitoring.ai-interactions', compact('interactions', 'conversations'));
    }

    /**
     * Display student progress matrix.
     */
    public function studentProgress()
    {
        $users = \App\Models\User::whereHas('roles', function($q) {
            $q->where('name', 'student');
        })->with(['trainingProgress.trainingModule'])->get();

        $modules = \App\Models\TrainingModule::where('is_active', true)->get();

        return view('admin.monitoring.student-progress', compact('users', 'modules'));
    }

    /**
     * Show details of a specific session.
     */
    public function vrSessionDetail(VrSession $session)
    {
        $session->load(['user', 'aiInteractions']);
        return view('admin.monitoring.vr-session-detail', compact('session'));
    }
}
