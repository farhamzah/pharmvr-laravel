@extends('layouts.admin')

@section('header')
<div class="flex flex-col gap-1">
    <div class="flex items-center gap-2 text-[10px] font-black uppercase tracking-[0.3em] text-text-tertiary opacity-60 italic">
        <a href="{{ route('admin.dashboard') }}" class="hover:text-primary transition-colors">Intelligence</a>
        <span class="text-primary/30">/</span>
        <a href="{{ route('admin.monitoring.vr') }}" class="hover:text-primary transition-colors">Field Operations</a>
        <span class="text-primary/30">/</span>
        <span class="text-white">Signal Analysis</span>
    </div>
    <h1 class="text-2xl font-black text-white tracking-tight font-display uppercase italic">Neural Link Detail</h1>
</div>
@endsection

@section('content')
<div class="max-w-6xl mx-auto space-y-10">
    <!-- Session Status Header Card -->
    <div class="bg-surface rounded-4xl border border-divider shadow-premium overflow-hidden">
        <div class="relative overflow-hidden">
            <div class="absolute inset-0 bg-primary/5 pointer-events-none"></div>
            <div class="relative p-10 flex flex-col md:flex-row justify-between items-start md:items-center gap-10">
                <div class="space-y-6">
                    <div class="flex items-center gap-4">
                        <span class="px-4 py-1.5 bg-primary/10 text-primary border border-primary/20 rounded-full text-[9px] font-black uppercase tracking-[0.2em] shadow-cyan-glow italic">VR SIGNAL ACTIVE</span>
                        <span class="text-text-tertiary font-mono text-[10px] tracking-[0.2em] opacity-50">NODE_ID: {{ strtoupper(substr($session->id, 0, 8)) }}</span>
                    </div>
                    <div>
                        <h2 class="text-5xl font-black text-white tracking-tight font-display uppercase italic">{{ $session->user->name }}</h2>
                        <div class="flex items-center gap-6 mt-4 text-[10px] font-black text-text-tertiary uppercase tracking-[0.2em] italic opacity-60">
                            <span class="flex items-center gap-2">
                                <svg class="w-3.5 h-3.5 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                {{ $session->created_at->format('d M / Y') }}
                            </span>
                            <span class="flex items-center gap-2 text-primary font-bold">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                Sync Initiated: {{ $session->started_at ? $session->started_at->format('H:i:s') : 'N/A' }}
                            </span>
                        </div>
                    </div>
                </div>

                <div class="flex flex-col items-end gap-3 bg-surface-light border border-divider p-8 rounded-3xl min-w-[200px] shadow-premium">
                    <div class="text-6xl font-black text-primary font-display italic tracking-tighter">{{ $session->progress_percentage }}<span class="text-2xl opacity-40 italic mt-1 font-mono">%</span></div>
                    <p class="text-[9px] font-black text-text-tertiary uppercase tracking-[0.3em] opacity-40">Sync Completion</p>
                    <div class="mt-4 w-full">
                        @php
                            $statusStyle = match($session->session_status) {
                                'active', 'starting', 'playing' => 'bg-primary/10 text-primary border-primary/20 shadow-cyan-glow',
                                'completed' => 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20',
                                'failed', 'interrupted' => 'bg-red-500/10 text-red-500 border-red-500/20',
                                default => 'bg-surface-light text-text-tertiary border-divider'
                            };
                        @endphp
                        <span class="block text-center px-4 py-2 border rounded-xl text-[9px] font-black uppercase tracking-[0.3em] italic {{ $statusStyle }}">
                            {{ $session->session_status }}
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Data Analytics Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-10">
        <!-- Neural Logs -->
        <div class="lg:col-span-2 bg-surface rounded-4xl border border-divider shadow-premium overflow-hidden flex flex-col">
            <div class="p-8 border-b border-divider flex items-center justify-between bg-surface-light/30">
                <h3 class="font-black text-white text-lg tracking-tight uppercase italic font-display underline decoration-primary/20 underline-offset-8">Neural Assistant Logs</h3>
                <span class="text-[10px] font-black text-primary uppercase tracking-[0.2em] bg-primary/10 px-3 py-1 rounded-full border border-primary/20">{{ count($session->aiInteractions) }} Data Packets</span>
            </div>
            <div class="flex-1 overflow-y-auto max-h-[600px] p-10 space-y-10 custom-scrollbar">
                @forelse($session->aiInteractions as $interaction)
                    <div class="space-y-6">
                        <div class="flex items-start gap-4">
                            <div class="w-8 h-8 rounded-xl bg-surface-light border border-divider text-text-tertiary flex items-center justify-center text-[10px] font-black flex-shrink-0 mt-1 shadow-sm uppercase">SUB</div>
                            <div class="bg-surface-light border border-divider/50 rounded-2xl rounded-tl-none px-6 py-4 text-sm text-text-secondary leading-relaxed italic">
                                {{ $interaction->query }}
                                <div class="text-[9px] text-text-tertiary font-mono opacity-30 mt-2 uppercase">{{ $interaction->created_at->format('H:i:s') }}</div>
                            </div>
                        </div>
                        <div class="flex items-start gap-4 flex-row-reverse text-right">
                            <div class="w-8 h-8 rounded-xl bg-primary/10 border border-primary/20 text-primary flex items-center justify-center text-[10px] font-black flex-shrink-0 mt-1 shadow-cyan-glow uppercase">AI</div>
                            <div class="bg-primary/5 border border-primary/20 rounded-2xl rounded-tr-none px-6 py-4 text-sm text-primary font-bold leading-relaxed">
                                {{ $interaction->response }}
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="h-full flex flex-col items-center justify-center text-text-tertiary font-black uppercase tracking-[0.4em] opacity-30 italic py-20">
                        <svg class="w-16 h-16 mb-4 opacity-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"></path></svg>
                        No AI interactions detected.
                    </div>
                @endforelse
            </div>
        </div>

        <!-- Technical Telemetry -->
        <div class="space-y-10">
            <div class="bg-surface rounded-4xl border border-divider shadow-premium p-8 space-y-8">
                <h3 class="font-black text-white text-lg tracking-tight uppercase italic font-display border-b border-divider pb-4 underline decoration-primary/20 underline-offset-8">Core Telemetry</h3>
                
                <div class="space-y-6">
                    <div class="flex flex-col gap-2">
                        <label class="text-[9px] font-black text-text-tertiary uppercase tracking-[0.3em] opacity-50">Hardware Signature</label>
                        <div class="text-text-primary font-bold text-sm bg-surface-light p-3 rounded-xl border border-divider overflow-hidden text-ellipsis">{{ $session->device ? $session->device->device_name : 'Unknown Device' }}</div>
                    </div>
                    
                    <div class="grid grid-cols-2 gap-6">
                        <div class="flex flex-col gap-2">
                            <label class="text-[9px] font-black text-text-tertiary uppercase tracking-[0.3em] opacity-50">Current Phase</label>
                            <div class="text-sm font-bold text-white uppercase italic tracking-tight">{{ $session->current_step ?? 'INITIALIZING' }}</div>
                        </div>
                        
                        <div class="flex flex-col gap-2">
                            <label class="text-[9px] font-black text-text-tertiary uppercase tracking-[0.3em] opacity-50">Module Cluster</label>
                            <div class="text-sm font-bold text-white tracking-tight">{{ $session->trainingModule ? $session->trainingModule->title : 'MOD_' . $session->training_module_id }}</div>
                        </div>
                    </div>

                    <div class="flex flex-col gap-2">
                        <label class="text-[9px] font-black text-text-tertiary uppercase tracking-[0.3em] opacity-50">Pairing Reference</label>
                        <div class="text-[10px] font-bold text-primary italic">PR-{{ str_pad($session->pairing_id, 4, '0', STR_PAD_LEFT) }}</div>
                    </div>

                    <div class="flex flex-col gap-2">
                        <label class="text-[9px] font-black text-text-tertiary uppercase tracking-[0.3em] opacity-50">Termination Signal</label>
                        <div class="text-sm font-bold text-text-tertiary font-mono italic">{{ $session->completed_at ? $session->completed_at->format('H:i:s') : 'LINK_ACTIVE' }}</div>
                    </div>
                </div>
            </div>

            @if($session->summary_json)
                <div class="bg-surface rounded-4xl border border-divider shadow-premium p-8 overflow-hidden">
                    <h4 class="text-[9px] font-black text-text-tertiary uppercase tracking-[0.3em] mb-6 opacity-60">Raw Data Packet</h4>
                    <pre class="bg-black/40 border border-divider text-primary/70 p-6 rounded-2xl text-[10px] font-mono overflow-x-auto max-h-[300px] custom-scrollbar leading-relaxed">{{ json_encode($session->summary_json, JSON_PRETTY_PRINT) }}</pre>
                </div>
            @endif
        </div>
    </div>
</div>

<style>
    .custom-scrollbar::-webkit-scrollbar { width: 4px; }
    .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
    .custom-scrollbar::-webkit-scrollbar-thumb { background: rgba(0, 229, 255, 0.1); border-radius: 10px; }
    .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: rgba(0, 229, 255, 0.3); }
</style>
@endsection
