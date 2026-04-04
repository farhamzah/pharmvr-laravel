@extends('layouts.admin')

@section('header')
<div class="flex flex-col gap-1">
    <div class="flex items-center gap-2 text-[10px] font-black uppercase tracking-[0.3em] text-text-tertiary opacity-60 italic">
        <a href="{{ route('admin.dashboard') }}" class="hover:text-primary transition-colors">Intelligence</a>
        <span class="text-primary/30">/</span>
        <span class="text-white font-display">Assessments</span>
    </div>
    <div class="flex items-center justify-between">
        <h1 class="text-3xl font-black text-white tracking-tighter font-display uppercase italic">Neural Evaluations</h1>
        <form action="{{ route('admin.assessments.initialize') }}" method="POST">
            @csrf
            <button type="submit" class="bg-surface-light hover:bg-primary text-text-tertiary hover:text-background border border-divider hover:border-primary px-8 py-3 rounded-2xl text-[10px] font-black uppercase tracking-[0.25em] transition-all duration-500 hover:shadow-cyan-glow flex items-center gap-3">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                Deploy All Protocol Nodes
            </button>
        </form>
    </div>
</div>
@endsection

@section('content')
<div class="grid grid-cols-1 gap-10">
    <!-- Macro Metrics -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
        <div class="bg-surface/40 backdrop-blur-md rounded-3xl border border-divider p-6 relative overflow-hidden group hover:border-primary/30 transition-all duration-500">
            <div class="absolute top-0 right-0 w-32 h-32 bg-primary/5 rounded-full blur-3xl -mr-16 -mt-16 group-hover:bg-primary/10 transition-all"></div>
            <p class="text-[9px] font-black text-text-tertiary uppercase tracking-[0.25em] mb-2 opacity-60">Module Nodes</p>
            <h4 class="text-4xl font-black text-white font-display italic tracking-tighter">{{ $modules->count() }}</h4>
        </div>
        <div class="bg-surface/40 backdrop-blur-md rounded-3xl border border-divider p-6 relative overflow-hidden group hover:border-emerald-500/30 transition-all duration-500">
            <div class="absolute top-0 right-0 w-32 h-32 bg-emerald-500/5 rounded-full blur-3xl -mr-16 -mt-16 group-hover:bg-emerald-500/10 transition-all"></div>
            <p class="text-[9px] font-black text-text-tertiary uppercase tracking-[0.25em] mb-2 opacity-60">Active Pre-Tests</p>
            <h4 class="text-4xl font-black text-emerald-400 font-display italic tracking-tighter">
                {{ $modules->flatMap->assessments->where('type', \App\Enums\AssessmentType::PRETEST)->where('status', \App\Enums\AssessmentStatus::ACTIVE)->count() }}
            </h4>
        </div>
        <div class="bg-surface/40 backdrop-blur-md rounded-3xl border border-divider p-6 relative overflow-hidden group hover:border-amber-500/30 transition-all duration-500">
            <div class="absolute top-0 right-0 w-32 h-32 bg-amber-500/5 rounded-full blur-3xl -mr-16 -mt-16 group-hover:bg-amber-500/10 transition-all"></div>
            <p class="text-[9px] font-black text-text-tertiary uppercase tracking-[0.25em] mb-2 opacity-60">Active Post-Tests</p>
            <h4 class="text-4xl font-black text-amber-400 font-display italic tracking-tighter">
                {{ $modules->flatMap->assessments->where('type', \App\Enums\AssessmentType::POSTTEST)->where('status', \App\Enums\AssessmentStatus::ACTIVE)->count() }}
            </h4>
        </div>
        <div class="bg-surface/40 backdrop-blur-md rounded-3xl border border-divider p-6 relative overflow-hidden group hover:border-primary/50 transition-all duration-500">
            <div class="absolute top-0 right-0 w-32 h-32 bg-primary/10 rounded-full blur-3xl -mr-16 -mt-16 group-hover:bg-primary/20 transition-all"></div>
            <p class="text-[9px] font-black text-text-tertiary uppercase tracking-[0.25em] mb-2 opacity-60">Total Neural Assets</p>
            <h4 class="text-4xl font-black text-white font-display italic tracking-tighter">{{ \App\Models\QuestionBankItem::count() }}</h4>
        </div>
    </div>

    <!-- Repository Interface -->
    <div class="bg-surface/20 backdrop-blur-xl rounded-[40px] border border-divider shadow-2xl overflow-hidden relative">
        <div class="absolute top-0 left-0 w-full h-1 bg-gradient-to-r from-transparent via-primary/30 to-transparent"></div>
        
        <div class="p-10 border-b border-divider bg-surface-light/10 flex items-center justify-between">
            <div>
                <h3 class="font-black text-white text-2xl tracking-tight uppercase italic font-display">Repository Index</h3>
                <p class="text-[10px] text-text-tertiary font-black uppercase tracking-[0.3em] opacity-40 mt-1 italic">Module-based evaluation matrix management</p>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead class="bg-surface-light/30 text-[9px] font-black text-text-tertiary uppercase tracking-[0.4em] border-b border-divider">
                    <tr>
                        <th class="px-10 py-6">Intelligence Node</th>
                        <th class="px-8 py-6 text-center">Total Assets</th>
                        <th class="px-8 py-6 text-center">Pre-Eligible</th>
                        <th class="px-8 py-6 text-center">Post-Eligible</th>
                        <th class="px-8 py-6 text-center">Pre-Protocol</th>
                        <th class="px-8 py-6 text-center">Post-Protocol</th>
                        <th class="px-10 py-6 text-right">Synchronization</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-divider/30">
                    @forelse($modules as $module)
                    @php
                        $pretest = $module->assessments->where('type', \App\Enums\AssessmentType::PRETEST)->first();
                        $posttest = $module->assessments->where('type', \App\Enums\AssessmentType::POSTTEST)->first();
                    @endphp
                    <tr class="group hover:bg-primary/[0.03] transition-all duration-500 border-none">
                        <td class="px-10 py-8">
                            <div class="flex items-center gap-4">
                                <div class="w-12 h-12 rounded-2xl bg-background/50 border border-divider flex items-center justify-center group-hover:border-primary/50 transition-all overflow-hidden shrink-0">
                                    <img src="{{ $module->cover_image_url }}" alt="" class="w-full h-full object-cover opacity-60 group-hover:scale-110 transition-transform duration-700">
                                </div>
                                <div class="flex flex-col">
                                    <span class="text-base font-black text-white group-hover:text-primary transition-colors tracking-tight italic uppercase font-display">{{ $module->title }}</span>
                                    <span class="text-[9px] text-text-tertiary font-mono opacity-50 uppercase tracking-widest font-black">{{ $module->slug }}</span>
                                </div>
                            </div>
                        </td>
                        <td class="px-8 py-8 text-center">
                            <div class="flex flex-col">
                                <span class="text-xl font-black text-white opacity-80 group-hover:opacity-100 transition-opacity">{{ $module->question_bank_items_count ?? $module->questionBankItems()->count() }}</span>
                                <span class="text-[8px] font-black text-text-tertiary uppercase tracking-widest opacity-30 italic">Encoded</span>
                            </div>
                        </td>
                        <td class="px-8 py-8 text-center">
                            <span class="text-sm font-black text-emerald-400/80">{{ $module->pretest_question_count }}</span>
                        </td>
                        <td class="px-8 py-8 text-center">
                            <span class="text-sm font-black text-amber-400/80">{{ $module->posttest_question_count }}</span>
                        </td>
                        <td class="px-8 py-8">
                            <div class="flex items-center justify-center">
                                @if($pretest && $pretest->status === \App\Enums\AssessmentStatus::ACTIVE)
                                    <div class="flex items-center gap-2 px-3 py-1 bg-emerald-500/10 border border-emerald-500/20 rounded-full">
                                        <div class="w-1.5 h-1.5 rounded-full bg-emerald-500 shadow-[0_0_8px_rgba(16,185,129,0.8)] animate-pulse"></div>
                                        <span class="text-[9px] font-black text-emerald-400 uppercase tracking-widest italic">Online</span>
                                    </div>
                                @elseif($pretest)
                                    <div class="flex items-center gap-2 px-3 py-1 bg-surface-light border border-divider rounded-full opacity-40">
                                        <div class="w-1.5 h-1.5 rounded-full bg-text-tertiary"></div>
                                        <span class="text-[9px] font-black text-text-tertiary uppercase tracking-widest italic">Offline</span>
                                    </div>
                                @else
                                    <span class="text-[8px] font-black text-text-tertiary/20 uppercase tracking-widest italic">Undeployed</span>
                                @endif
                            </div>
                        </td>
                        <td class="px-8 py-8">
                            <div class="flex items-center justify-center">
                                @if($posttest && $posttest->status === \App\Enums\AssessmentStatus::ACTIVE)
                                    <div class="flex items-center gap-2 px-3 py-1 bg-amber-500/10 border border-amber-500/20 rounded-full">
                                        <div class="w-1.5 h-1.5 rounded-full bg-amber-500 shadow-[0_0_8px_rgba(245,158,11,0.8)] animate-pulse"></div>
                                        <span class="text-[9px] font-black text-amber-400 uppercase tracking-widest italic">Online</span>
                                    </div>
                                @elseif($posttest)
                                    <div class="flex items-center gap-2 px-3 py-1 bg-surface-light border border-divider rounded-full opacity-40">
                                        <div class="w-1.5 h-1.5 rounded-full bg-text-tertiary"></div>
                                        <span class="text-[9px] font-black text-text-tertiary uppercase tracking-widest italic">Offline</span>
                                    </div>
                                @else
                                    <span class="text-[8px] font-black text-text-tertiary/20 uppercase tracking-widest italic">Undeployed</span>
                                @endif
                            </div>
                        </td>
                        <td class="px-10 py-8 text-right">
                            <a href="{{ route('admin.assessments.show', $module) }}" class="inline-flex items-center gap-3 bg-surface-light hover:bg-primary text-text-tertiary hover:text-background border border-divider hover:border-primary px-8 py-3 rounded-2xl text-[10px] font-black uppercase tracking-[0.25em] transition-all duration-500 hover:shadow-cyan-glow group/btn">
                                <span>Configure</span>
                                <svg class="w-4 h-4 transform group-hover/btn:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-10 py-24 text-center">
                            <div class="flex flex-col items-center gap-4 opacity-30">
                                <svg class="w-16 h-16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z"></path></svg>
                                <span class="text-sm font-black uppercase tracking-widest italic">Zero intelligence nodes detected. System integrity check required.</span>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
