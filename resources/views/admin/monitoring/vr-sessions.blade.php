@extends('layouts.admin')

@section('header')
<div class="flex flex-col gap-1">
    <div class="flex items-center gap-2 text-[10px] font-black uppercase tracking-[0.3em] text-text-tertiary opacity-60 italic">
        <a href="{{ route('admin.dashboard') }}" class="hover:text-primary transition-colors">Intelligence</a>
        <span class="text-primary/30">/</span>
        <span class="text-white">Field Operations</span>
    </div>
    <h1 class="text-2xl font-black text-white tracking-tight font-display uppercase italic">VR Telemetry Monitoring</h1>
</div>
@endsection

@section('content')
<div class="bg-surface rounded-4xl border border-divider shadow-premium overflow-hidden transition-all hover:border-primary/10">
    <div class="p-10 border-b border-divider bg-surface-light/30 flex items-center justify-between">
        <div>
            <h3 class="font-black text-white text-lg tracking-tight uppercase italic underline decoration-primary/20 decoration-2 underline-offset-8">Active Data Stream</h3>
            <p class="text-[10px] text-text-tertiary font-black uppercase tracking-[0.2em] mt-3 opacity-60">Synchronizing with Node Cluster...</p>
        </div>
        <div class="flex items-center gap-3">
            <span class="w-2 h-2 rounded-full bg-primary animate-pulse shadow-cyan-glow"></span>
            <span class="text-[10px] font-black text-primary uppercase tracking-[0.3em]">{{ $sessions->total() }} Signals Captured</span>
        </div>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead class="bg-surface-light/50 text-[10px] font-black text-text-tertiary uppercase tracking-[0.3em] border-b border-divider">
                <tr>
                    <th class="px-10 py-5">Signal Identity</th>
                    <th class="px-10 py-5">Assigned Subject</th>
                    <th class="px-10 py-5">State</th>
                    <th class="px-10 py-5">Sync Depth</th>
                    <th class="px-10 py-5">Last Activity</th>
                    <th class="px-10 py-5 text-right">Protocol</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-divider/50">
                @forelse($sessions as $session)
                <tr class="hover:bg-primary/5 transition-all duration-300 group">
                    <td class="px-10 py-6">
                        <div class="text-sm font-bold text-white group-hover:text-primary transition-colors">SID_{{ strtoupper(substr($session->device_id, 0, 10)) }}</div>
                        <div class="text-[9px] text-text-tertiary font-mono opacity-50 uppercase tracking-tighter">{{ strtoupper($session->created_at->format('d M / H:i:s')) }}</div>
                    </td>
                    <td class="px-10 py-6">
                        <a href="{{ route('admin.reporting.user-report', $session->user) }}" class="group/link">
                            <div class="text-sm font-bold text-white group-hover/link:text-primary transition-colors">{{ $session->user->name }}</div>
                            <div class="text-[10px] text-text-tertiary font-mono opacity-40 uppercase tracking-tighter">{{ strtoupper($session->user->email) }}</div>
                        </a>
                    </td>
                    <td class="px-10 py-6">
                        @php
                            $badgeStyle = match($session->session_status) {
                                'starting', 'playing' => 'bg-primary/10 text-primary border-primary/20 shadow-cyan-glow',
                                'completed' => 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20',
                                'failed', 'interrupted' => 'bg-red-500/10 text-red-500 border-red-500/20',
                                default => 'bg-surface-light text-text-tertiary border-divider'
                            };
                        @endphp
                        <span class="inline-flex px-4 py-1.5 rounded-full text-[9px] font-black uppercase tracking-[0.2em] border {{ $badgeStyle }} italic">
                            {{ $session->session_status }}
                        </span>
                    </td>
                    <td class="px-10 py-6">
                        <div class="flex items-center gap-4">
                            <div class="w-24 bg-surface-light h-1.5 rounded-full overflow-hidden border border-divider">
                                <div class="bg-primary h-full rounded-full shadow-cyan-glow group-hover:brightness-125 transition-all" @style(['width' => ($session->progress_percentage ?? 0) . '%'])></div>
                            </div>
                            <span class="text-[10px] font-black text-white italic">{{ $session->progress_percentage }}%</span>
                        </div>
                    </td>
                    <td class="px-10 py-6 text-[10px] font-bold text-text-tertiary uppercase tracking-widest opacity-60 italic">
                        {{ strtoupper($session->last_activity_at ? $session->last_activity_at->diffForHumans() : 'NO_SIGNAL') }}
                    </td>
                    <td class="px-10 py-6 text-right">
                        <a href="{{ route('admin.monitoring.vr.detail', $session) }}" class="inline-flex items-center gap-3 px-5 py-2.5 bg-surface-light border border-divider hover:border-primary/50 text-text-secondary hover:text-primary rounded-xl text-[9px] font-black uppercase tracking-[0.3em] transition-all group/btn">
                            Decrypt
                            <svg class="w-3 h-3 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M9 5l7 7-7 7"></path></svg>
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-10 py-20 text-center text-text-tertiary font-bold uppercase tracking-[0.4em] opacity-30 italic">No Active Telemetry Streams.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($sessions->hasPages())
    <div class="px-10 py-6 border-t border-divider bg-surface-light/30">
        <div class="pagination-premium">
            {{ $sessions->links() }}
        </div>
    </div>
    @endif
</div>

<style>
    /* Premium Pagination Shared Styles */
    .pagination-premium nav { display: flex; justify-content: center; }
    .pagination-premium a, .pagination-premium span { 
        background: #1C2733 !important; 
        border: 1px solid #2A3545 !important; 
        color: #B0BEC5 !important; 
        border-radius: 12px !important;
        margin: 0 4px !important;
        font-weight: 800 !important;
        font-size: 10px !important;
        text-transform: uppercase !important;
        letter-spacing: 0.1em !important;
        transition: all 0.3s !important;
    }
    .pagination-premium a:hover { border-color: #00E5FF !important; color: #00E5FF !important; }
    .pagination-premium .active span { background: #00E5FF !important; border-color: #00E5FF !important; color: #0A0F14 !important; }
</style>
@endsection
