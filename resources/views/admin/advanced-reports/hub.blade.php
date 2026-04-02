@extends('layouts.admin')

@section('header')
<div class="flex flex-col gap-1">
    <div class="flex items-center gap-2 text-[10px] font-black uppercase tracking-[0.3em] text-text-tertiary opacity-60 italic">
        <a href="{{ route('admin.dashboard') }}" class="hover:text-primary transition-colors">Intelligence</a>
        <span class="text-primary/30">/</span>
        <span class="text-white">Analytics Hub</span>
    </div>
    <h1 class="text-2xl font-black text-white tracking-tight font-display uppercase italic">Advanced Data Suite</h1>
</div>
@endsection

@section('content')
<div class="space-y-10">
    <div class="bg-surface rounded-4xl border border-divider shadow-premium p-10 relative overflow-hidden group">
        <div class="absolute -right-20 -top-20 w-64 h-64 bg-primary/10 rounded-full blur-[100px] group-hover:bg-primary/20 transition-all duration-700 pointer-events-none"></div>
        <div class="relative z-10">
            <h2 class="text-3xl font-black text-white font-display italic tracking-tight mb-4">PharmVR Analytics Core</h2>
            <p class="text-text-secondary text-sm leading-relaxed max-w-3xl">This suite processes continuous telemetry feeds, VR operational metrics, and neural assistant utilization to provide granular insight into educational efficacy and system health.</p>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
        <!-- Pre-test vs Post-test -->
        <a href="{{ route('admin.advanced-reports.pretest-posttest') }}" class="bg-surface rounded-4xl border border-divider shadow-premium p-8 hover:border-primary/50 transition-all duration-300 group flex flex-col h-full">
            <div class="w-12 h-12 rounded-2xl bg-primary/10 text-primary border border-primary/20 flex items-center justify-center mb-6 group-hover:scale-110 transition-transform">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
            </div>
            <h3 class="text-lg font-black text-white uppercase italic tracking-wider mb-2">Knowledge Gain</h3>
            <p class="text-xs text-text-tertiary font-medium mb-6 flex-1">Compare Pre-Test and Post-Test scores across modules to measure actual learning progression.</p>
            <div class="flex items-center text-[10px] font-black text-primary uppercase tracking-[0.2em]">
                View Report
                <svg class="w-3 h-3 ml-2 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
            </div>
        </a>

        <!-- Question Analysis -->
        <a href="{{ route('admin.advanced-reports.question-analysis') }}" class="bg-surface rounded-4xl border border-divider shadow-premium p-8 hover:border-primary/50 transition-all duration-300 group flex flex-col h-full">
            <div class="w-12 h-12 rounded-2xl bg-indigo-500/10 text-indigo-400 border border-indigo-500/20 flex items-center justify-center mb-6 group-hover:scale-110 transition-transform">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9l3 3-3 3m5 0h3M5 20h14a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
            </div>
            <h3 class="text-lg font-black text-white uppercase italic tracking-wider mb-2">Item Analysis</h3>
            <p class="text-xs text-text-tertiary font-medium mb-6 flex-1">Evaluate the difficulty and efficacy of individual questions in the assessment bank.</p>
            <div class="flex items-center text-[10px] font-black text-indigo-400 uppercase tracking-[0.2em]">
                View Report
                <svg class="w-3 h-3 ml-2 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
            </div>
        </a>

        <!-- Completion Funnel -->
        <a href="{{ route('admin.advanced-reports.completion-funnel') }}" class="bg-surface rounded-4xl border border-divider shadow-premium p-8 hover:border-primary/50 transition-all duration-300 group flex flex-col h-full">
            <div class="w-12 h-12 rounded-2xl bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 flex items-center justify-center mb-6 group-hover:scale-110 transition-transform">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path></svg>
            </div>
            <h3 class="text-lg font-black text-white uppercase italic tracking-wider mb-2">Journey Funnel</h3>
            <p class="text-xs text-text-tertiary font-medium mb-6 flex-1">Track drop-off rates across the entire training pipeline, from Pre-Test to Certification.</p>
            <div class="flex items-center text-[10px] font-black text-emerald-400 uppercase tracking-[0.2em]">
                View Report
                <svg class="w-3 h-3 ml-2 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
            </div>
        </a>

        <!-- VR Performance -->
        <a href="{{ route('admin.advanced-reports.vr-performance') }}" class="bg-surface rounded-4xl border border-divider shadow-premium p-8 hover:border-primary/50 transition-all duration-300 group flex flex-col h-full">
            <div class="w-12 h-12 rounded-2xl bg-cyan-500/10 text-cyan-400 border border-cyan-500/20 flex items-center justify-center mb-6 group-hover:scale-110 transition-transform">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
            </div>
            <h3 class="text-lg font-black text-white uppercase italic tracking-wider mb-2">VR Operations</h3>
            <p class="text-xs text-text-tertiary font-medium mb-6 flex-1">Analyze speed, accuracy, and compliance breaches within Meta Quest 3 environments.</p>
            <div class="flex items-center text-[10px] font-black text-cyan-400 uppercase tracking-[0.2em]">
                View Report
                <svg class="w-3 h-3 ml-2 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
            </div>
        </a>

        <!-- Neural AI Usage -->
        <a href="{{ route('admin.advanced-reports.ai-usage') }}" class="bg-surface rounded-4xl border border-divider shadow-premium p-8 hover:border-primary/50 transition-all duration-300 group flex flex-col h-full">
            <div class="w-12 h-12 rounded-2xl bg-purple-500/10 text-purple-400 border border-purple-500/20 flex items-center justify-center mb-6 group-hover:scale-110 transition-transform">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
            </div>
            <h3 class="text-lg font-black text-white uppercase italic tracking-wider mb-2">Neural Metrics</h3>
            <p class="text-xs text-text-tertiary font-medium mb-6 flex-1">Monitor AI model token consumption, latency, and safety verification pass rates.</p>
            <div class="flex items-center text-[10px] font-black text-purple-400 uppercase tracking-[0.2em]">
                View Report
                <svg class="w-3 h-3 ml-2 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
            </div>
        </a>

        <!-- Time Trends -->
        <a href="{{ route('admin.advanced-reports.trends') }}" class="bg-surface rounded-4xl border border-divider shadow-premium p-8 hover:border-primary/50 transition-all duration-300 group flex flex-col h-full">
            <div class="w-12 h-12 rounded-2xl bg-orange-500/10 text-orange-400 border border-orange-500/20 flex items-center justify-center mb-6 group-hover:scale-110 transition-transform">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z"></path></svg>
            </div>
            <h3 class="text-lg font-black text-white uppercase italic tracking-wider mb-2">Activity Trends</h3>
            <p class="text-xs text-text-tertiary font-medium mb-6 flex-1">Time-series analysis of system utilization, mapping VR sessions over 30 to 90 days.</p>
            <div class="flex items-center text-[10px] font-black text-orange-400 uppercase tracking-[0.2em]">
                View Report
                <svg class="w-3 h-3 ml-2 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
            </div>
        </a>
    </div>
</div>
@endsection
