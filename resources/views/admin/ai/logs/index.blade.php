@extends('layouts.admin')

@section('header', 'AI Interaction Logs')

@section('content')
<div class="mb-10 flex flex-wrap items-center justify-between gap-6">
    <div class="flex flex-col gap-2">
        <h2 class="text-[10px] font-black text-primary uppercase tracking-[0.4em] italic">System Audit Trail</h2>
        <p class="text-xs text-text-tertiary font-bold uppercase tracking-widest opacity-80">
            Comprehensive archive of all student-to-AI communications and inference analytics.
        </p>
    </div>
</div>

{{-- Unified Interaction Filters --}}
<div class="bg-surface/40 border border-divider rounded-3xl p-5 mb-10 backdrop-blur-md shadow-inner">
    <form action="{{ route('admin.ai.logs.index') }}" method="GET" class="flex flex-wrap items-end gap-5">
        <div class="flex-grow min-w-[300px]">
            <label class="text-[8px] font-black text-text-tertiary uppercase tracking-[0.3em] block mb-2 opacity-50 px-2">Content Search</label>
            <div class="relative group">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="SEARCH QUERY OR RESPONSE..." 
                    class="bg-background border-divider border rounded-2xl px-12 py-3.5 text-xs font-bold text-white focus:border-primary focus:ring-0 transition-all w-full uppercase tracking-widest placeholder:text-text-tertiary/30">
                <svg class="w-5 h-5 absolute left-4 top-1/2 -translate-y-1/2 text-text-tertiary group-hover:text-primary transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                </svg>
            </div>
        </div>
        
        <div class="w-44">
            <label class="text-[8px] font-black text-text-tertiary uppercase tracking-[0.3em] block mb-2 opacity-50 px-2">Platform</label>
            <select name="platform" class="bg-background border-divider border rounded-2xl px-5 py-3.5 text-xs font-black text-white focus:border-primary focus:ring-0 transition-all w-full uppercase tracking-widest cursor-pointer appearance-none">
                <option value="">ALL PLATFORMS</option>
                <option value="app" {{ request('platform') == 'app' ? 'selected' : '' }}>MOBILE_APP</option>
                <option value="vr" {{ request('platform') == 'vr' ? 'selected' : '' }}>VR_HEADSET</option>
            </select>
        </div>

        <div class="w-48">
            <label class="text-[8px] font-black text-text-tertiary uppercase tracking-[0.3em] block mb-2 opacity-50 px-2">Assistant Mode</label>
            <select name="mode" class="bg-background border-divider border rounded-2xl px-5 py-3.5 text-xs font-black text-white focus:border-primary focus:ring-0 transition-all w-full uppercase tracking-widest cursor-pointer appearance-none">
                <option value="">ALL MODES</option>
                <option value="gmp_expert" {{ request('mode') == 'gmp_expert' ? 'selected' : '' }}>GMP_EXPERT</option>
                <option value="training_support" {{ request('mode') == 'training_support' ? 'selected' : '' }}>TRAINING_SUPPORT</option>
                <option value="lab_procedures" {{ request('mode') == 'lab_procedures' ? 'selected' : '' }}>LAB_PROCEDURES</option>
            </select>
        </div>

        <div class="w-48">
            <label class="text-[8px] font-black text-text-tertiary uppercase tracking-[0.3em] block mb-2 opacity-50 px-2">Response Type</label>
            <select name="response_mode" class="bg-background border-divider border rounded-2xl px-5 py-3.5 text-xs font-black text-white focus:border-primary focus:ring-0 transition-all w-full uppercase tracking-widest cursor-pointer appearance-none">
                <option value="">ALL TYPES</option>
                <option value="grounded" {{ request('response_mode') == 'grounded' ? 'selected' : '' }}>GROUNDED</option>
                <option value="restricted" {{ request('response_mode') == 'restricted' ? 'selected' : '' }}>RESTRICTED</option>
                <option value="neutral" {{ request('response_mode') == 'neutral' ? 'selected' : '' }}>CONTEXT_GAP</option>
            </select>
        </div>

        <div class="flex items-center gap-3">
            <x-admin.ai.action-button type="submit" size="md" class="px-8 py-3.5 h-[50px]">EXECUTE SYNC</x-admin.ai.action-button>
            <a href="{{ route('admin.ai.logs.index') }}" class="p-4 bg-surface-light hover:bg-surface border border-divider rounded-2xl transition-all h-[50px] flex items-center justify-center group" title="Reset All Filters">
                <svg class="w-5 h-5 text-text-tertiary group-hover:text-red-400 group-hover:rotate-180 transition-all duration-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
            </a>
        </div>
    </form>
</div>

{{-- High Density Log Table --}}
<div class="bg-surface border border-divider rounded-4xl overflow-hidden shadow-premium backdrop-blur-sm">
    <div class="px-10 py-6 border-b border-divider bg-surface-light/20 flex items-center justify-between">
        <h3 class="text-lg font-black text-white italic tracking-tight uppercase font-display">Communication Archive</h3>
        <span class="text-[9px] font-black text-text-tertiary uppercase tracking-[0.2em] opacity-50">{{ $logs->total() }} INTERACTIONS LOGGED</span>
    </div>

    <table class="w-full text-left border-collapse">
        <thead class="bg-surface-light/30 text-[9px] font-black text-text-tertiary uppercase tracking-[0.2em] border-b border-divider">
            <tr>
                <th class="px-10 py-5">User / Platform</th>
                <th class="px-8 py-5">AI Strategy</th>
                <th class="px-8 py-5">Interaction Content (Q&A)</th>
                <th class="px-8 py-5">Inference Stats</th>
                <th class="px-10 py-5 text-right">Timestamp</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-divider/30 text-xs text-text-tertiary">
            @forelse($logs as $log)
            <tr class="hover:bg-primary/5 transition-all duration-300 group">
                <td class="px-10 py-6">
                    <div class="font-black text-white group-hover:text-primary transition-colors cursor-default uppercase tracking-widest truncate max-w-[150px]">{{ $log->session->user->name ?? 'ANONYMOUS_STUDENT' }}</div>
                    <div class="flex items-center gap-2 mt-1 italic">
                        <span class="text-[8px] text-primary font-black uppercase tracking-widest">{{ $log->session->platform->value ?? 'MOBILE_APP' }}</span>
                        <span class="w-1 h-1 rounded-full bg-divider/50"></span>
                        <span class="text-[8px] text-text-tertiary font-bold uppercase tracking-widest opacity-40">M: {{ strtoupper($log->session->assistant_mode ?? 'GENERAL') }}</span>
                    </div>
                </td>
                <td class="px-8 py-6">
                    <div class="flex flex-col gap-2">
                        @php
                            $modeColor = match($log->response_mode) {
                                'grounded' => 'emerald',
                                'restricted' => 'red',
                                'neutral' => 'amber',
                                default => 'surface'
                            };
                        @endphp
                        <x-admin.ai.glow-badge :color="$modeColor">
                            {{ strtoupper($log->response_mode ?? 'GROUNDED') }}
                        </x-admin.ai.glow-badge>
                        
                        <div class="flex items-center gap-1.5 pl-1.5 opacity-50">
                            <span class="text-[7px] font-black uppercase tracking-widest">CITATIONS:</span>
                            <span class="text-[9px] font-black text-white italic leading-none">{{ count($log->cited_sources_json ?? []) }}</span>
                        </div>
                    </div>
                </td>
                <td class="px-8 py-6">
                    <div class="space-y-3 max-w-md">
                        <div class="flex items-start gap-3 group/q">
                            <span class="text-[9px] font-black text-text-tertiary mt-0.5 opacity-40">Q:</span>
                            <p class="text-[11px] font-bold text-white group-hover:text-primary transition-colors line-clamp-1 group-hover:line-clamp-none transition-all leading-relaxed">{{ $log->session->messages->where('sender', 'user')->where('created_at', '<=', $log->created_at)->last()->message_text ?? 'N/A' }}</p>
                        </div>
                        <div class="flex items-start gap-3 group/a">
                            <span class="text-[9px] font-black text-primary mt-0.5 italic opacity-60">A:</span>
                            <p class="text-[11px] font-bold text-text-tertiary group-hover:text-white transition-colors line-clamp-2 group-hover:line-clamp-none transition-all leading-relaxed italic">{{ $log->message_text }}</p>
                        </div>
                    </div>
                </td>
                <td class="px-8 py-6">
                    <div class="flex flex-col gap-1.5">
                        <div class="flex items-baseline gap-2">
                            <span class="text-sm font-black text-white italic">{{ number_format($log->confidence_score ?? 0.85, 4) }}</span>
                            <span class="text-[7px] font-black text-text-tertiary uppercase tracking-widest opacity-40">CONF</span>
                        </div>
                        <div class="text-[9px] font-black text-blue-400/60 uppercase tracking-tighter whitespace-nowrap">{{ $log->response_time_ms }}MS LATENCY</div>
                    </div>
                </td>
                <td class="px-10 py-6 text-right">
                    <div class="text-[10px] font-bold text-white italic tracking-[0.1em] uppercase leading-none">{{ $log->created_at->format('H:i:s') }}</div>
                    <div class="text-[8px] font-black text-text-tertiary uppercase tracking-widest opacity-40 italic mt-2">{{ $log->created_at->format('M d, Y') }}</div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="5" class="py-24 text-center">
                    <p class="text-[10px] font-black text-text-tertiary uppercase tracking-[0.4em] opacity-30 italic">No communication signals archived in current selection.</p>
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>

    @if($logs->hasPages())
    <div class="px-10 py-8 bg-surface-light/10 border-t border-divider">
        {{ $logs->links() }}
    </div>
    @endif
</div>
@endsection
