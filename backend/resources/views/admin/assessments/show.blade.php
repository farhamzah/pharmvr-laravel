@extends('layouts.admin')

@section('header', 'Telemetry Data')

@section('content')
<div class="max-w-6xl mx-auto">
    <div class="mb-10 flex flex-col md:flex-row md:items-center justify-between gap-6">
        <div>
            <div class="flex items-center gap-2 text-[10px] font-black uppercase tracking-[0.3em] text-text-tertiary opacity-60 italic mb-2">
                <a href="{{ route('admin.assessments.index') }}" class="hover:text-primary transition-colors">Assessments</a>
                <span class="text-primary/30">/</span>
                <span class="text-white">Assessment Details</span>
            </div>
            <h2 class="text-3xl font-black text-white tracking-tight font-display uppercase italic">{{ $assessment->title }}</h2>
        </div>
        <div class="flex items-center gap-4">
            <a href="{{ route('admin.assessments.edit', $assessment) }}" class="px-8 py-3 bg-surface-light border border-divider text-white rounded-2xl text-[10px] font-black uppercase tracking-widest hover:border-primary transition-all">Edit Settings</a>
            <button class="px-8 py-3 bg-primary text-background rounded-2xl text-[10px] font-black uppercase tracking-[0.2em] hover:bg-primary-dark transition-all shadow-cyan-glow flex items-center gap-3">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M12 4v16m8-8H4"></path></svg>
                Add Question
            </button>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Stats -->
        <div class="space-y-6">
            <div class="bg-surface rounded-4xl border border-divider p-8 shadow-premium">
                <h4 class="text-[10px] font-black text-text-tertiary uppercase tracking-[0.3em] mb-6 opacity-60 italic">Configuration</h4>
                <div class="space-y-6 text-sm">
                    <div class="flex justify-between items-center group">
                        <span class="text-[9px] font-black text-text-tertiary uppercase tracking-widest opacity-40">Min Pass Score</span>
                        <span class="font-black text-primary italic font-display group-hover:scale-110 transition-transform">80%</span>
                    </div>
                    <div class="flex justify-between items-center group">
                        <span class="text-[9px] font-black text-text-tertiary uppercase tracking-widest opacity-40">Time Limit</span>
                        <span class="font-bold text-white uppercase italic tracking-tight font-display">30 mins</span>
                    </div>
                    <div class="flex justify-between items-center group">
                        <span class="text-[9px] font-black text-text-tertiary uppercase tracking-widest opacity-40">Type</span>
                        <span class="px-4 py-1.5 bg-primary/10 text-primary border border-primary/20 rounded-full text-[9px] font-black uppercase tracking-[0.2em]">{{ $assessment->type }}</span>
                    </div>
                </div>
            </div>
            
            <div class="bg-primary rounded-4xl p-8 shadow-cyan-glow text-background relative overflow-hidden">
                <div class="absolute -right-10 -top-10 w-32 h-32 bg-white/20 rounded-full blur-2xl"></div>
                <h4 class="text-[10px] font-black text-background/60 uppercase tracking-[0.3em] mb-4 italic">Total Questions</h4>
                <p class="text-4xl font-black mb-1 font-display italic">{{ count($assessment->questions) }}</p>
                <p class="text-[9px] font-bold text-background/70 uppercase tracking-widest leading-tight">Items in question bank</p>
            </div>
        </div>

        <!-- Question List -->
        <div class="lg:col-span-2">
            <div class="bg-surface rounded-4xl border border-divider shadow-premium overflow-hidden">
                <div class="p-8 border-b border-divider flex items-center justify-between bg-surface-light/30">
                    <h3 class="font-black text-white text-xl tracking-tight uppercase italic font-display">Question Bank</h3>
                    <span class="text-[10px] font-black text-primary uppercase tracking-[0.2em] bg-primary/5 px-4 py-1.5 rounded-full border border-primary/20">{{ count($assessment->questions) }} items</span>
                </div>
                <div class="divide-y divide-divider/50">
                    @forelse($assessment->questions as $question)
                    <div class="p-8 hover:bg-primary/5 transition-all duration-300 group">
                        <div class="flex items-start justify-between gap-6">
                            <div class="flex items-start gap-6">
                                <div class="w-12 h-12 bg-surface-light border border-divider rounded-2xl flex-shrink-0 flex items-center justify-center text-primary group-hover:scale-110 transition-transform shadow-cyan-glow/20">
                                    <span class="text-xs font-black font-display italic">{{ $question->order }}</span>
                                </div>
                                <div class="pt-1">
                                    <p class="text-sm font-bold text-white leading-relaxed group-hover:text-primary transition-colors tracking-tight">{{ $question->question_text }}</p>
                                    @if($question->explanation)
                                        <div class="mt-4 flex items-start gap-2 text-[10px] text-text-tertiary font-bold uppercase tracking-widest opacity-60">
                                            <svg class="w-4 h-4 text-primary/50" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                            <span class="italic">{{ $question->explanation }}</span>
                                        </div>
                                    @endif
                                </div>
                            </div>
                            <div class="opacity-0 group-hover:opacity-100 transition-all transform translate-x-4 group-hover:translate-x-0 flex items-center gap-3">
                                <button class="p-3 bg-surface-light border border-divider text-text-tertiary hover:text-primary hover:border-primary rounded-xl transition-all">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                                </button>
                            </div>
                        </div>
                    </div>
                    @empty
                    <div class="p-20 text-center text-text-tertiary font-black uppercase tracking-[0.4em] opacity-30 italic">No telemetry data detected.</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
