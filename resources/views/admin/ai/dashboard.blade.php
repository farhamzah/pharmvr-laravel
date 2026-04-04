@extends('layouts.admin')

@section('header', 'AI Operations Dashboard')

@section('content')
{{-- Operational Readiness Workflow --}}
<div class="mb-10 bg-surface rounded-4xl border border-divider shadow-premium overflow-hidden relative group">
    <div class="absolute top-0 right-0 p-8 opacity-5">
        <svg class="w-32 h-32 text-primary" fill="currentColor" viewBox="0 0 24 24"><path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z"></path></svg>
    </div>
    <div class="p-8 border-b border-divider bg-surface-light/30">
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 rounded-2xl bg-primary/10 border border-primary/20 flex items-center justify-center text-primary">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path></svg>
            </div>
            <div>
                <h3 class="font-black text-white text-xl tracking-tight uppercase font-display italic">Operational Readiness</h3>
                <p class="text-xs text-text-tertiary font-bold uppercase tracking-widest mt-1 opacity-60">System initialization and knowledge ingestion status</p>
            </div>
        </div>
    </div>
    <div class="p-8">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            @php
                $steps = [
                    ['title' => 'Ingest Knowledge', 'desc' => 'Add trusted datasets', 'link' => route('admin.ai.sources.index'), 'done' => $totalSources > 0],
                    ['title' => 'Process Index', 'desc' => 'Generate vector chunks', 'link' => route('admin.ai.sources.index'), 'done' => $totalChunks > 0],
                    ['title' => 'Ready Signals', 'desc' => 'Validate retrieval nodes', 'link' => route('admin.ai.sources.index'), 'done' => $activeSources > 0],
                    ['title' => 'Deploy Avatars', 'desc' => 'Configure RAG personas', 'link' => route('admin.ai.avatars.index'), 'done' => $avatarCount > 0],
                ];
            @endphp
            
            @foreach($steps as $index => $step)
            <a href="{{ $step['link'] }}" class="flex items-start gap-4 p-5 rounded-3xl border {{ $step['done'] ? 'bg-emerald-500/5 border-emerald-500/20 shadow-premium' : 'bg-surface-light/30 border-divider' }} group/step transition-all hover:scale-[1.02]">
                <div class="w-9 h-9 rounded-xl {{ $step['done'] ? 'bg-emerald-500 text-background' : 'bg-surface-light text-text-tertiary' }} flex flex-shrink-0 items-center justify-center text-[11px] font-black italic">
                    @if($step['done'])
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
                    @else
                        {{ $index + 1 }}
                    @endif
                </div>
                <div>
                    <h4 class="text-[11px] font-black {{ $step['done'] ? 'text-emerald-400' : 'text-white' }} uppercase tracking-widest">{{ $step['title'] }}</h4>
                    <p class="text-[9px] text-text-tertiary font-bold uppercase tracking-widest opacity-60 group-hover/step:opacity-100 transition-opacity mt-1 italic">{{ $step['desc'] }}</p>
                </div>
            </a>
            @endforeach
        </div>
    </div>
</div>

{{-- Global System Stats --}}
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-10">
    <x-admin.ai.stat-card 
        title="Knowledge Repository" 
        :value="$totalSources" 
        sub="ACTIVE_NODES"
        icon='<svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg>'
        color="primary"
    />
    
    <x-admin.ai.stat-card 
        title="Grounded Accuracy" 
        :value="$groundedRate . '%'" 
        sub="RAG_CONFIDENCE"
        icon='<svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>'
        color="emerald"
        trend="Synced"
    />

    <x-admin.ai.stat-card 
        title="Restricted Access" 
        :value="$restrictedQueryCount" 
        sub="POLICY_ENFORCED"
        icon='<svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>'
        color="red"
    />

    <x-admin.ai.stat-card 
        title="Active Sessions" 
        :value="number_format($totalSessions)" 
        sub="USER_INTERACTIONS"
        icon='<svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M8 10h.01M12 10h.01M16 10h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path></svg>'
        color="amber"
    />
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-10">
    <div class="lg:col-span-2 space-y-10">
        <div class="bg-surface rounded-4xl border border-divider shadow-premium overflow-hidden">
            <div class="px-10 py-6 border-b border-divider bg-surface-light/20 flex items-center justify-between">
                <div>
                    <h3 class="text-lg font-black text-white italic tracking-tight uppercase font-display">Recent AI Inquiries</h3>
                    <p class="text-[10px] text-text-tertiary font-bold uppercase tracking-widest mt-1 opacity-60">Live feed of student-to-AI communications</p>
                </div>
                <a href="{{ route('admin.ai.logs.index') }}" class="text-[9px] font-black text-primary hover:text-white uppercase tracking-widest transition-colors border-b border-primary/20 pb-0.5">VIEW ALL LOGS</a>
            </div>

            <table class="w-full text-left border-collapse">
                <thead class="bg-surface-light/30 text-[9px] font-black text-text-tertiary uppercase tracking-[0.2em] border-b border-divider">
                    <tr>
                        <th class="px-10 py-5">User Identity</th>
                        <th class="px-8 py-5">Integration Context</th>
                        <th class="px-8 py-5">Last Transmission</th>
                        <th class="px-10 py-5 text-right">Row Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-divider/30 text-xs">
                    @forelse($recentSessions as $session)
                    <tr class="hover:bg-primary/5 transition-all duration-300 group">
                        <td class="px-10 py-6">
                            <div class="font-black text-white group-hover:text-primary transition-colors uppercase tracking-widest">{{ $session->user->name ?? 'ANONYMOUS_STUDENT' }}</div>
                            <div class="text-[9px] text-text-tertiary font-mono opacity-50 uppercase tracking-tighter mt-1 italic italic">ID: {{ strtoupper(substr($session->id, 0, 8)) }}</div>
                        </td>
                        <td class="px-8 py-6">
                            <x-admin.ai.glow-badge color="blue">
                                {{ strtoupper($session->module->title ?? 'GENERAL_HUB') }}
                            </x-admin.ai.glow-badge>
                        </td>
                        <td class="px-8 py-6 text-[10px] font-bold text-text-tertiary uppercase tracking-widest opacity-60 italic">
                            {{ $session->updated_at->diffForHumans() }}
                        </td>
                        <td class="px-10 py-6 text-right">
                            <a href="{{ route('admin.ai.logs.index', ['session_id' => $session->id]) }}">
                                <x-admin.ai.action-button size="sm">INSPECT LOG</x-admin.ai.action-button>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="py-20 text-center">
                            <p class="text-[10px] font-black text-text-tertiary uppercase tracking-[0.4em] opacity-30 italic">No communication signals detected.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="space-y-10">
        <!-- Signal Integrity Analysis -->
        <div class="bg-surface rounded-4xl border border-divider shadow-premium overflow-hidden">
            <div class="p-8 border-b border-divider bg-surface-light/30 text-center">
                <h3 class="font-black text-white text-xl tracking-tight uppercase font-display italic">Signal Stability</h3>
                <p class="text-[9px] text-text-tertiary font-bold uppercase tracking-[0.2em] mt-2 opacity-60">AI Response Quality Analytics</p>
            </div>
            <div class="p-8 space-y-10">
                <div>
                    <div class="flex items-center justify-between mb-3 px-1">
                        <span class="text-[10px] font-black text-white uppercase tracking-widest italic opacity-70">Context Gaps</span>
                        <span class="text-[11px] font-black text-amber-500 italic">{{ $insufficientContextCount }} ERRORS</span>
                    </div>
                    <div class="w-full bg-background h-2 rounded-full overflow-hidden border border-divider shadow-inner">
                        <div class="bg-amber-500 h-full rounded-full shadow-[0_0_15px_rgba(245,158,11,0.4)]" style="width: {{ $totalSessions > 0 ? min(100, ($insufficientContextCount / $totalSessions) * 100) : 0 }}%"></div>
                    </div>
                </div>
                
                <div class="pt-8 border-t border-divider grid grid-cols-2 gap-6">
                    <div class="p-5 rounded-3xl bg-surface-light border border-divider flex flex-col items-center group hover:border-emerald-500/30 transition-all">
                        <p class="text-[8px] font-black text-text-tertiary uppercase tracking-[0.3em] mb-2 opacity-50">Active Nodes</p>
                        <p class="text-2xl font-black text-emerald-400 italic leading-none group-hover:scale-110 transition-transform">{{ $activeSources }}</p>
                    </div>
                    <div class="p-5 rounded-3xl bg-surface-light border border-divider flex flex-col items-center group hover:border-primary/30 transition-all">
                        <p class="text-[8px] font-black text-text-tertiary uppercase tracking-[0.3em] mb-2 opacity-50">Personas</p>
                        <p class="text-2xl font-black text-primary italic leading-none group-hover:scale-110 transition-transform">{{ $avatarCount }}</p>
                    </div>
                </div>

                <div class="bg-primary/5 border border-primary/20 rounded-3xl p-6 text-center">
                    <p class="text-[9px] font-black text-primary uppercase tracking-[0.4em] mb-3">Total Knowledge Vectors</p>
                    <p class="text-3xl font-black text-white italic tracking-tighter">{{ number_format($totalChunks) }} <span class="text-xs opacity-40">CHUNKS</span></p>
                </div>
            </div>
        </div>

        <!-- Topics Analysis -->
        <div class="bg-surface rounded-4xl border border-divider shadow-premium overflow-hidden">
            <div class="p-8 border-b border-divider bg-surface-light/30">
                <h3 class="font-black text-white text-xl tracking-tight uppercase font-display italic">Topic Analytics</h3>
                <p class="text-xs text-text-tertiary font-bold uppercase tracking-widest mt-1 opacity-60">High-Frequency Knowledge Requisitions</p>
            </div>
            <div class="p-8 space-y-8">
                @forelse($topTopics as $topic)
                <div class="group">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-[11px] font-black text-white uppercase tracking-widest group-hover:text-primary transition-colors">{{ $topic->topic }}</span>
                        <span class="text-[10px] font-black text-primary italic">{{ $topic->total }} INTERACTIONS</span>
                    </div>
                    <div class="w-full bg-background h-1.5 rounded-full overflow-hidden border border-divider">
                        <div class="bg-primary h-full rounded-full shadow-cyan-glow group-hover:brightness-125 transition-all" style="width: {{ min(100, ($topic->total / max(1, $topTopics->first()->total ?? 1)) * 100) }}%"></div>
                    </div>
                </div>
                @empty
                <p class="text-center text-text-tertiary font-bold uppercase tracking-[0.4em] opacity-30 italic py-10">No data clusters detected.</p>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection
