@extends('layouts.admin')

@section('header')
<div class="flex flex-col gap-1">
    <div class="flex items-center gap-2 text-[10px] font-black uppercase tracking-[0.3em] text-text-tertiary opacity-60 italic">
        <a href="{{ route('admin.dashboard') }}" class="hover:text-primary transition-colors">Intelligence</a>
        <span class="text-primary/30">/</span>
        <span class="text-white">AI Analytics</span>
    </div>
    <h1 class="text-2xl font-black text-white tracking-tight font-display uppercase italic">Interaction Analytics</h1>
</div>
@endsection

@section('content')
<div class="space-y-10">
    <!-- VR AI Interactions -->
    <div class="bg-surface rounded-4xl border border-divider shadow-premium overflow-hidden transition-all hover:border-primary/10">
        <div class="p-8 border-b border-divider flex items-center justify-between bg-surface-light/30">
            <div>
                <h3 class="font-black text-white text-xl tracking-tight uppercase italic font-display">Neural Assistant Logs</h3>
                <p class="text-[10px] text-text-tertiary font-black uppercase tracking-[0.2em] mt-2 opacity-60">Real-time Guide interactions from VR Cluster</p>
            </div>
            <div class="flex items-center gap-3">
                <span class="w-2 h-2 rounded-full bg-primary animate-pulse shadow-cyan-glow"></span>
                <span class="text-[10px] font-black text-primary uppercase tracking-[0.3em]">{{ count($interactions) }} Active Records</span>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead class="bg-surface-light/50 text-[10px] font-black text-text-tertiary uppercase tracking-[0.3em] italic border-b border-divider">
                    <tr>
                        <th class="px-8 py-5">Signal Identity</th>
                        <th class="px-8 py-5">Subject</th>
                        <th class="px-8 py-5">Query Input</th>
                        <th class="px-8 py-5">Neural Response</th>
                        <th class="px-8 py-5 text-right">Protocol</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-divider/30">
                    @forelse($interactions as $ai)
                    <tr class="hover:bg-primary/5 transition-all duration-300 group">
                        <td class="px-8 py-6 text-[10px] text-text-tertiary font-mono uppercase tracking-tighter opacity-60">
                            {{ $ai->created_at->format('d M / H:i:s') }}
                        </td>
                        <td class="px-8 py-6">
                            <div class="text-sm font-bold text-white group-hover:text-primary transition-colors tracking-tight uppercase italic">{{ $ai->user->name }}</div>
                        </td>
                        <td class="px-8 py-6 max-w-xs">
                            <p class="text-xs text-text-secondary line-clamp-2 leading-relaxed italic opacity-80">{{ $ai->query }}</p>
                        </td>
                        <td class="px-8 py-6 max-w-xs">
                            <p class="text-xs text-primary font-bold line-clamp-2 leading-relaxed">{{ $ai->response }}</p>
                        </td>
                        <td class="px-8 py-6 text-right">
                            @if($ai->vrSession)
                                <a href="{{ route('admin.monitoring.vr.detail', $ai->vrSession) }}" class="inline-flex items-center gap-2 px-4 py-2 bg-surface-light border border-divider hover:border-primary/50 text-text-secondary hover:text-primary rounded-xl text-[9px] font-black uppercase tracking-[0.2em] transition-all">
                                    View Session
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M14 5l7 7-7 7"></path></svg>
                                </a>
                            @else
                                <span class="text-[9px] font-black text-text-tertiary uppercase tracking-widest opacity-30 italic">No Reference</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-8 py-20 text-center text-text-tertiary font-black uppercase tracking-[0.4em] opacity-30 italic">No VR AI interactions detected.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- PharmAI Conversations (Mobile App) -->
    <div class="bg-surface rounded-4xl border border-divider shadow-premium overflow-hidden transition-all hover:border-blue-500/10">
        <div class="p-8 border-b border-divider flex items-center justify-between bg-surface-light/30">
            <div>
                <h3 class="font-black text-white text-xl tracking-tight uppercase italic font-display">Mobile Interface Hub</h3>
                <p class="text-[10px] text-text-tertiary font-black uppercase tracking-[0.2em] mt-2 opacity-60">App-based conversations and direct queries</p>
            </div>
            <div class="flex items-center gap-3">
                <span class="w-2 h-2 rounded-full bg-blue-500 shadow-blue-500/50 opacity-80"></span>
                <span class="text-[10px] font-black text-blue-400 uppercase tracking-[0.3em]">{{ count($conversations) }} Conversations</span>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead class="bg-surface-light/50 text-[10px] font-black text-text-tertiary uppercase tracking-[0.3em] italic border-b border-divider">
                    <tr>
                        <th class="px-8 py-5">Latest Broadcast</th>
                        <th class="px-8 py-5">Subject Identity</th>
                        <th class="px-8 py-5">Interface Topic</th>
                        <th class="px-8 py-5">Density</th>
                        <th class="px-8 py-5 text-right">Operations</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-divider/30">
                    @forelse($conversations as $chat)
                    <tr class="hover:bg-blue-500/5 transition-all duration-300 group">
                        <td class="px-8 py-6 text-[10px] text-text-tertiary font-mono uppercase tracking-tighter opacity-60">
                            {{ strtoupper($chat->updated_at->diffForHumans()) }}
                        </td>
                        <td class="px-8 py-6">
                            <div class="text-sm font-bold text-white group-hover:text-blue-400 transition-colors tracking-tight uppercase italic">{{ $chat->user->name }}</div>
                        </td>
                        <td class="px-8 py-6">
                            <div class="text-sm font-bold text-text-secondary uppercase italic tracking-tight">{{ $chat->title ?? 'Untitled Stream' }}</div>
                        </td>
                        <td class="px-8 py-6">
                            <span class="px-3 py-1 bg-surface-light text-text-tertiary border border-divider rounded-lg text-[9px] font-black uppercase tracking-[0.1em]">
                                {{ $chat->messages_count ?? 0 }} Packets
                            </span>
                        </td>
                        <td class="px-8 py-6 text-right">
                            <button class="px-5 py-2.5 bg-surface-light border border-divider hover:border-blue-500/50 text-text-secondary hover:text-blue-400 rounded-xl text-[9px] font-black uppercase tracking-[0.3em] transition-all">
                                Transcript
                            </button>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-8 py-20 text-center text-text-tertiary font-black uppercase tracking-[0.4em] opacity-30 italic">No mobile chat logs detected.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
