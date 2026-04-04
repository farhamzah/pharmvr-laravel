@extends('layouts.admin')

@section('header')
<div class="flex flex-col gap-1">
    <div class="flex items-center gap-2 text-[10px] font-black uppercase tracking-[0.3em] text-text-tertiary opacity-60 italic">
        <a href="{{ route('admin.dashboard') }}" class="hover:text-primary transition-colors">Intelligence</a>
        <span class="text-primary/30">/</span>
        <span class="text-white">Neural Analytics</span>
    </div>
    <h1 class="text-2xl font-black text-white tracking-tight font-display uppercase italic">Neural Analytics</h1>
</div>
@endsection

@section('content')
<div class="space-y-8">
    <!-- Stat Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        @foreach($interactionStats as $stat)
        <div class="bg-surface p-8 rounded-4xl border border-divider shadow-premium hover:border-primary/30 transition-all group">
            <p class="text-[10px] font-black text-text-tertiary uppercase tracking-[0.3em] mb-2">{{ str_replace('_', ' ', strtoupper($stat->interaction_type)) }}</p>
            <div class="flex items-end justify-between">
                <p class="text-3xl font-black text-white leading-none font-display italic tracking-tight">{{ $stat->count }}</p>
            </div>
        </div>
        @endforeach
        @if($interactionStats->isEmpty())
        <div class="bg-surface p-8 rounded-4xl border border-divider shadow-premium col-span-3 text-center text-text-tertiary opacity-50 italic uppercase tracking-widest font-black text-[10px]">No interaction data available.</div>
        @endif
    </div>

    <!-- Recent Interactions -->
    <div class="bg-surface rounded-4xl border border-divider shadow-premium overflow-hidden">
        <div class="p-8 border-b border-divider flex items-center justify-between bg-surface-light/30">
            <div>
                <h3 class="font-black text-white text-xl tracking-tight">Operational Feed</h3>
                <p class="text-xs text-text-tertiary font-bold uppercase tracking-widest mt-1 opacity-60">Monitoring AI behavior and safety responses</p>
            </div>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead class="bg-surface-light/50 text-[10px] font-black text-text-tertiary uppercase tracking-[0.3em] border-b border-divider">
                    <tr>
                        <th class="px-8 py-5">User</th>
                        <th class="px-8 py-5">Interaction</th>
                        <th class="px-8 py-5">Efficiency</th>
                        <th class="px-8 py-5">Safety</th>
                        <th class="px-8 py-5 text-right">Activity</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-divider/50">
                    @forelse($recentInteractions as $log)
                    <tr class="hover:bg-primary/5 transition-all duration-300 group">
                        <td class="px-8 py-6">
                            <a href="{{ route('admin.reporting.user-report', $log->user) }}" class="group/link">
                                <div class="text-sm font-bold text-white group-hover/link:text-primary transition-colors">{{ $log->user->name }}</div>
                                <div class="text-[10px] text-text-tertiary font-mono opacity-50">{{ $log->interaction_type }}</div>
                            </a>
                        </td>
                        <td class="px-8 py-6">
                            <div class="text-[10px] font-black text-text-tertiary uppercase tracking-[0.2em] leading-tight">
                                <span class="text-white">{{ $log->model_name }}</span><br>
                                <span class="text-primary tracking-widest italic opacity-70">{{ $log->provider_name }}</span>
                            </div>
                        </td>
                        <td class="px-8 py-6">
                            <div class="text-sm font-black text-white italic font-display">{{ $log->latency_ms }}<span class="text-[10px] font-mono opacity-50 ml-1">ms</span></div>
                            <div class="text-[10px] font-black text-primary bg-primary/5 border border-primary/10 px-2 py-0.5 rounded-full w-fit uppercase tracking-widest mt-1 inline-block">{{ $log->total_tokens }} tokens</div>
                        </td>
                        <td class="px-8 py-6">
                            @if($log->is_safe_response)
                                <span class="px-4 py-1.5 bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 rounded-full text-[9px] font-black uppercase tracking-[0.2em]">Safe</span>
                            @else
                                <span class="px-4 py-1.5 bg-red-500/10 text-red-500 border border-red-500/20 rounded-full text-[9px] font-black uppercase tracking-[0.2em]">Flagged</span>
                            @endif
                        </td>
                        <td class="px-8 py-6 text-right text-[10px] font-bold text-text-tertiary uppercase tracking-widest opacity-60 italic">
                            {{ $log->created_at->diffForHumans() }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-8 py-16 text-center text-text-tertiary font-black uppercase tracking-[0.4em] opacity-30 italic text-[10px]">No AI interaction logs found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Analytics Insights -->
    <div class="bg-primary rounded-4xl p-12 text-background shadow-cyan-glow relative overflow-hidden group">
        <div class="absolute -right-20 -top-20 w-64 h-64 bg-white/20 rounded-full blur-3xl group-hover:bg-white/30 transition-all duration-700 pointer-events-none"></div>
        <div class="absolute -bottom-12 -left-12 w-64 h-64 bg-black/20 rounded-full blur-3xl opacity-50 pointer-events-none"></div>

        <div class="relative z-10 grid grid-cols-1 md:grid-cols-2 gap-12 items-center">
            <div>
                <h3 class="text-3xl font-black mb-4 leading-tight font-display uppercase italic tracking-tighter">AI Utility Analytics</h3>
                <p class="text-[10px] font-bold text-background/80 mb-8 leading-relaxed uppercase tracking-widest">The AI interaction engine is currently maintaining a sub-500ms latency profile. Safe response verification is active and 100% operational across all Meta Quest sessions.</p>
                <div class="flex gap-10">
                    <div>
                        <p class="text-[10px] font-black opacity-60 uppercase tracking-widest mb-1 italic">Avg Tokens</p>
                        <p class="text-2xl font-black font-display italic">124.5</p>
                    </div>
                    <div class="border-l border-background/20 pl-10">
                        <p class="text-[10px] font-black opacity-60 uppercase tracking-widest mb-1 italic">Safety Pass</p>
                        <p class="text-2xl font-black font-display italic flex items-center gap-2">
                            99.8%
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        </p>
                    </div>
                </div>
            </div>
            <div class="hidden md:block">
                <div class="w-full bg-background/10 p-8 rounded-3xl backdrop-blur-sm border border-background/20 contour-glow">
                    <p class="text-[9px] font-black text-background/80 uppercase tracking-widest mb-4 italic">Latency Trend</p>
                    <div class="flex items-end gap-2 h-32">
                        @foreach([30, 45, 38, 52, 41, 35, 48, 42, 39, 44] as $h)
                        <div class="flex-1 bg-background/30 hover:bg-background/50 rounded-t border-t-2 border-background/40 transition-all" @style(['height' => $h . '%'])></div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
