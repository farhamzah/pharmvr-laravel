@extends('layouts.admin')

@section('header')
<div class="flex flex-col gap-1">
    <div class="flex items-center gap-2 text-[10px] font-black uppercase tracking-[0.3em] text-text-tertiary opacity-60 italic">
        <a href="{{ route('admin.assessments.index') }}" class="hover:text-primary transition-colors hover:tracking-[0.4em]">Neural Evaluations</a>
        <span class="text-primary/30">/</span>
        <span class="text-white font-display">{{ $module->title }}</span>
    </div>
    <h1 class="text-2xl font-black text-white tracking-tight font-display uppercase italic">Node Configuration</h1>
</div>
@endsection

@section('content')
<div x-data="{ activeTab: 'question-bank' }" class="space-y-10">
    <!-- Subject Node Hero -->
    <div class="bg-surface/30 backdrop-blur-xl rounded-[40px] border border-divider p-10 relative overflow-hidden group shadow-2xl">
        <div class="absolute top-0 right-0 w-[500px] h-[500px] bg-primary/5 rounded-full blur-[120px] -mr-64 -mt-64 group-hover:bg-primary/10 transition-all duration-1000"></div>
        <div class="flex flex-col md:flex-row items-center gap-10 relative z-10">
            <div class="w-48 h-48 rounded-[32px] border-2 border-divider p-2 bg-background/50 shrink-0 group-hover:border-primary/30 transition-all duration-500 shadow-cyan-glow/5">
                <div class="w-full h-full rounded-[24px] overflow-hidden">
                    <img src="{{ $module->cover_image_url }}" alt="" class="w-full h-full object-cover opacity-80 group-hover:scale-110 group-hover:opacity-100 transition-all duration-700">
                </div>
            </div>
            <div class="flex-1 text-center md:text-left">
                <div class="inline-flex items-center gap-2 px-4 py-1.5 bg-primary/10 border border-primary/20 rounded-full mb-6">
                    <div class="w-1.5 h-1.5 rounded-full bg-primary animate-pulse"></div>
                    <span class="text-[10px] font-black text-primary uppercase tracking-[0.3em] italic">Intelligence Subject</span>
                </div>
                <h2 class="text-5xl font-black text-white font-display italic tracking-tighter uppercase mb-4">{{ $module->title }}</h2>
                <p class="text-text-tertiary text-sm font-medium opacity-60 max-w-2xl leading-relaxed">{{ $module->description }}</p>
            </div>
        </div>
    </div>

    <!-- Macro Telemetry Cards -->
    <div class="grid grid-cols-1 md:grid-cols-5 gap-6">
        <div class="bg-surface/40 backdrop-blur-md rounded-3xl border border-divider p-6 group">
            <p class="text-[9px] font-black text-text-tertiary uppercase tracking-[0.2em] mb-2 opacity-50">Total Assets</p>
            <h4 class="text-3xl font-black text-white font-display italic">{{ $module->questionBankItems->count() }}</h4>
        </div>
        <div class="bg-surface/40 backdrop-blur-md rounded-3xl border border-divider p-6 group">
            <p class="text-[9px] font-black text-emerald-400 uppercase tracking-[0.2em] mb-2 opacity-50">Pre-Eligible</p>
            <h4 class="text-3xl font-black text-white font-display italic">{{ $module->pretest_question_count }}</h4>
        </div>
        <div class="bg-surface/40 backdrop-blur-md rounded-3xl border border-divider p-6 group">
            <p class="text-[9px] font-black text-amber-400 uppercase tracking-[0.2em] mb-2 opacity-50">Post-Eligible</p>
            <h4 class="text-3xl font-black text-white font-display italic">{{ $module->posttest_question_count }}</h4>
        </div>
        <div class="bg-surface/40 backdrop-blur-md rounded-3xl border border-divider p-6 group">
            <p class="text-[9px] font-black text-text-tertiary uppercase tracking-[0.2em] mb-2 opacity-50">Pre-Status</p>
            @if($pretest && $pretest->status === \App\Enums\AssessmentStatus::ACTIVE)
                <span class="text-xl font-black text-emerald-400 font-display italic uppercase tracking-tight">Active</span>
            @else
                <span class="text-xl font-black text-rose-500/50 font-display italic uppercase tracking-tight">Offline</span>
            @endif
        </div>
        <div class="bg-surface/40 backdrop-blur-md rounded-3xl border border-divider p-6 group">
            <p class="text-[9px] font-black text-text-tertiary uppercase tracking-[0.2em] mb-2 opacity-50">Post-Status</p>
            @if($posttest && $posttest->status === \App\Enums\AssessmentStatus::ACTIVE)
                <span class="text-xl font-black text-amber-400 font-display italic uppercase tracking-tight">Active</span>
            @else
                <span class="text-xl font-black text-rose-500/50 font-display italic uppercase tracking-tight">Offline</span>
            @endif
        </div>
    </div>

    <!-- Algorithm Calibration Alerts -->
    @php
        $preWarn = $pretest && $module->pretest_question_count < $pretest->number_of_questions_to_take;
        $postWarn = $posttest && $module->posttest_question_count < $posttest->number_of_questions_to_take;
    @endphp

    @if($preWarn || $postWarn)
        <div class="p-6 bg-rose-500/10 border border-rose-500/20 rounded-[32px] flex items-center gap-6 animate-pulse">
            <div class="w-12 h-12 rounded-2xl bg-rose-500 text-background flex items-center justify-center shrink-0 shadow-lg shadow-rose-500/20">
                <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
            </div>
            <div class="flex flex-col">
                <span class="text-[10px] font-black text-rose-500 uppercase tracking-[0.3em]">Critical Integrity Warning</span>
                <p class="text-sm font-bold text-white/80 mt-1">
                    @if($preWarn)
                        Insufficient assets for Pre-Test protocol ({{ $module->pretest_question_count }}/{{ $pretest->number_of_questions_to_take }} available).
                    @endif
                    @if($postWarn)
                        {{ $preWarn ? ' | ' : '' }}
                        Insufficient assets for Post-Test protocol ({{ $module->posttest_question_count }}/{{ $posttest->number_of_questions_to_take }} available).
                    @endif
                </p>
            </div>
        </div>
    @endif

    <!-- Interface Navigation Tabs -->
    <div class="flex items-center gap-3 p-1.5 bg-surface/30 backdrop-blur-md rounded-2xl border border-divider w-fit shadow-lg">
        <button @click="activeTab = 'question-bank'" 
                :class="activeTab === 'question-bank' ? 'bg-primary text-background shadow-cyan-glow scale-105' : 'text-text-tertiary hover:text-white hover:bg-surface-light/50'" 
                class="px-10 py-4 rounded-xl text-[11px] font-black uppercase tracking-[0.25em] transition-all duration-300 transform">
            1. Neural Repository
        </button>
        <button @click="activeTab = 'settings'" 
                :class="activeTab === 'settings' ? 'bg-primary text-background shadow-cyan-glow scale-105' : 'text-text-tertiary hover:text-white hover:bg-surface-light/50'" 
                class="px-10 py-4 rounded-xl text-[11px] font-black uppercase tracking-[0.25em] transition-all duration-300 transform">
            2. Assessment Matrix
        </button>
    </div>

    <!-- Content Sections -->
    <div class="relative min-h-[600px]">
        <!-- Question Bank Tab -->
        <div x-show="activeTab === 'question-bank'" 
             x-transition:enter="transition ease-out duration-500" 
             x-transition:enter-start="opacity-0 scale-[0.98] translate-y-4" 
             x-transition:enter-end="opacity-100 scale-100 translate-y-0" 
             class="space-y-8">
            
            <div class="bg-surface/20 backdrop-blur-xl rounded-[40px] border border-divider shadow-premium overflow-hidden">
                <div class="p-10 border-b border-divider bg-surface-light/10 flex flex-col md:flex-row items-center justify-between gap-6">
                    <div>
                        <h3 class="font-black text-white text-2xl tracking-tight uppercase italic font-display tracking-widest">Encoded Assets</h3>
                        <p class="text-[9px] text-text-tertiary font-black uppercase tracking-[0.3em] opacity-40 mt-1 italic">Knowledge pool for this intelligence node</p>
                    </div>
                    <div class="flex flex-wrap items-center gap-4 w-full md:w-auto">
                        <!-- Filters -->
                        <div class="flex items-center p-1 bg-background/50 border border-divider rounded-2xl">
                            <button class="px-4 py-2 rounded-xl text-[9px] font-black uppercase tracking-widest bg-primary text-background shadow-cyan-glow transition-all">All</button>
                            <button class="px-4 py-2 rounded-xl text-[9px] font-black uppercase tracking-widest text-text-tertiary hover:text-white transition-all">Pre</button>
                            <button class="px-4 py-2 rounded-xl text-[9px] font-black uppercase tracking-widest text-text-tertiary hover:text-white transition-all">Post</button>
                        </div>
                        
                        <div class="relative flex-1 md:w-64">
                            <input type="text" placeholder="Search Matrix..." class="w-full bg-background/50 border border-divider rounded-2xl px-6 py-3 text-xs text-white font-bold focus:border-primary transition-all pr-12">
                            <svg class="w-4 h-4 absolute right-5 top-1/2 -translate-y-1/2 text-text-tertiary opacity-40" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                        </div>
                        
                        <button @click="$dispatch('open-modal', 'create-question')" class="bg-primary hover:bg-primary-dark text-background px-8 py-3 rounded-2xl text-[10px] font-black uppercase tracking-[0.25em] transition-all shadow-cyan-glow flex items-center gap-3 shrink-0">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="4" d="M12 4v16m8-8H4"></path></svg>
                            New Entry
                        </button>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead class="bg-surface-light/30 text-[9px] font-black text-text-tertiary uppercase tracking-[0.35em] border-b border-divider">
                            <tr>
                                <th class="px-10 py-6 w-[45%]">Neural Inquiry</th>
                                <th class="px-8 py-6 text-center">Protocol Scope</th>
                                <th class="px-8 py-6 text-center">Options</th>
                                <th class="px-8 py-6 text-center">Correct Index</th>
                                <th class="px-8 py-6 text-center">Status</th>
                                <th class="px-10 py-6 text-right">Operations</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-divider/30">
                            @forelse($module->questionBankItems as $question)
                            <tr class="hover:bg-primary/[0.03] transition-all duration-300 group">
                                <td class="px-10 py-8">
                                    <div class="flex flex-col gap-2">
                                        <p class="text-base font-bold text-white/90 leading-snug tracking-tight">{{ $question->question_text }}</p>
                                        @if($question->difficulty)
                                            <div class="flex items-center gap-2">
                                                <span class="text-[8px] font-black px-2 py-0.5 border border-primary/30 rounded text-primary uppercase tracking-widest italic">{{ $question->difficulty }} Node</span>
                                            </div>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-8 py-8 text-center">
                                    @if($question->usage_scope === \App\Enums\QuestionUsageScope::PRETEST)
                                        <span class="px-4 py-1.5 bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 rounded-xl text-[9px] font-black uppercase tracking-[0.2em]">Pre-Test</span>
                                    @elseif($question->usage_scope === \App\Enums\QuestionUsageScope::POSTTEST)
                                        <span class="px-4 py-1.5 bg-amber-500/10 text-amber-400 border border-amber-500/20 rounded-xl text-[9px] font-black uppercase tracking-[0.2em]">Post-Test</span>
                                    @else
                                        <span class="px-4 py-1.5 bg-primary/10 text-primary border border-primary/20 rounded-xl text-[9px] font-black uppercase tracking-[0.2em]">Universal</span>
                                    @endif
                                </td>
                                <td class="px-8 py-8 text-center text-sm font-black text-text-tertiary italic opacity-60">
                                    {{ $question->options->count() }}
                                </td>
                                <td class="px-8 py-8 text-center">
                                    <span class="w-8 h-8 rounded-full border border-divider flex items-center justify-center text-xs font-black text-white group-hover:border-primary/50 transition-all font-mono">
                                        {{ chr(65 + $question->options->search(fn($o) => $o->is_correct)) }}
                                    </span>
                                </td>
                                <td class="px-8 py-8 text-center">
                                    @if($question->is_active)
                                        <div class="w-3 h-3 rounded-full bg-emerald-500 shadow-cyan-glow mx-auto ring-4 ring-emerald-500/10"></div>
                                    @else
                                        <div class="w-3 h-3 rounded-full bg-divider/30 mx-auto"></div>
                                    @endif
                                </td>
                                <td class="px-10 py-8 text-right">
                                    <div class="flex items-center justify-end gap-3">
                                        <button @click="$dispatch('open-modal', 'edit-question-{{ $question->id }}')" class="p-3 text-text-tertiary hover:text-primary bg-background/50 border border-divider rounded-2xl transition-all hover:scale-110">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                                        </button>
                                        <form action="{{ route('admin.assessments.questions.destroy', [$module, $question]) }}" method="POST" onsubmit="return confirm('Purge this neural asset?')" class="inline">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="p-3 text-rose-500/40 hover:text-rose-500 bg-rose-500/5 border border-rose-500/10 rounded-2xl transition-all hover:scale-110">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="px-10 py-24 text-center text-text-tertiary italic uppercase tracking-[0.1em] text-sm opacity-30">Repository is synchronized but empty. Initiate encoding protocol.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Assessment Settings Tab -->
        <div x-show="activeTab === 'settings'" 
             x-transition:enter="transition ease-out duration-500" 
             x-transition:enter-start="opacity-0 scale-[0.98] translate-y-4" 
             x-transition:enter-end="opacity-100 scale-100 translate-y-0">
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-10">
                <!-- Pre-Test Config -->
                <div>
                    @include('admin.assessments.partials.assessment_form', [
                        'assessment' => $pretest, 
                        'type' => \App\Enums\AssessmentType::PRETEST,
                        'title' => 'Pre-Test Protocol Matrix'
                    ])
                </div>
                
                <!-- Post-Test Config -->
                <div>
                    @include('admin.assessments.partials.assessment_form', [
                        'assessment' => $posttest, 
                        'type' => \App\Enums\AssessmentType::POSTTEST,
                        'title' => 'Post-Test Protocol Matrix'
                    ])
                </div>
            </div>
        </div>
    </div>
</div>

@foreach($module->questionBankItems as $question)
    @include('admin.assessments.partials.question_modal', ['question' => $question, 'isEdit' => true])
@endforeach

@include('admin.assessments.partials.question_modal', ['question' => null, 'isEdit' => false])
@endsection
