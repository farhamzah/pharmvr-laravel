@extends('layouts.admin')

@section('header', 'Module Telemetry')

@section('content')
<div class="max-w-6xl mx-auto">
    <div class="mb-10 flex flex-col md:flex-row md:items-center justify-between gap-6">
        <div>
            <div class="flex items-center gap-2 text-[10px] font-black uppercase tracking-[0.3em] text-text-tertiary opacity-60 italic mb-2">
                <a href="{{ route('admin.education.index') }}" class="hover:text-primary transition-colors">Education</a>
                <span class="text-primary/30">/</span>
                <span class="text-white">Module Details</span>
            </div>
            <h2 class="text-3xl font-black text-white tracking-tight font-display uppercase italic">{{ $module->title }}</h2>
        </div>
        <div class="flex items-center gap-4">
            <a href="{{ route('admin.education.edit', $module) }}" class="px-8 py-3 bg-surface-light border border-divider text-white rounded-2xl text-[10px] font-black uppercase tracking-widest hover:border-primary transition-all">Edit Module</a>
            <a href="{{ route('admin.education.add-content', $module) }}" class="px-8 py-3 bg-primary text-background rounded-2xl text-[10px] font-black uppercase tracking-[0.2em] hover:bg-primary-dark transition-all shadow-cyan-glow flex items-center gap-3">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M12 4v16m8-8H4"></path></svg>
                Add Content
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <div class="space-y-8">
            <div class="bg-surface rounded-4xl border border-divider p-8 shadow-premium">
                <h4 class="text-[10px] font-black text-text-tertiary uppercase tracking-[0.3em] mb-6 opacity-60 italic">Module Overview</h4>
                <div class="space-y-6">
                    <div>
                        <p class="text-[9px] font-black text-text-tertiary uppercase tracking-widest mb-2 opacity-40">Difficulty</p>
                        <span class="px-4 py-1.5 rounded-full text-[9px] font-black uppercase tracking-[0.2em] border {{ $module->difficulty === 'Beginner' ? 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20' : ($module->difficulty === 'Intermediate' ? 'bg-amber-500/10 text-amber-400 border-amber-500/20' : 'bg-rose-500/10 text-rose-400 border-rose-500/20') }}">
                            {{ $module->difficulty }}
                        </span>
                    </div>
                    <div>
                        <p class="text-[9px] font-black text-text-tertiary uppercase tracking-widest mb-2 opacity-40">Status</p>
                        @if($module->is_active)
                            <span class="px-4 py-1.5 bg-primary/10 text-primary border border-primary/20 rounded-full text-[9px] font-black uppercase tracking-[0.2em] shadow-cyan-glow">
                                Active
                            </span>
                        @else
                            <span class="px-4 py-1.5 bg-surface-light text-text-tertiary border border-divider rounded-full text-[9px] font-black uppercase tracking-[0.2em]">
                                Draft
                            </span>
                        @endif
                    </div>
                    <div>
                        <p class="text-[9px] font-black text-text-tertiary uppercase tracking-widest mb-2 opacity-40">Estimated Duration</p>
                        <p class="text-sm font-bold text-white uppercase italic tracking-tight font-display">{{ $module->estimated_duration ?? 'Not set' }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-primary rounded-4xl p-8 shadow-cyan-glow text-background relative overflow-hidden">
                <div class="absolute -right-10 -top-10 w-32 h-32 bg-white/20 rounded-full blur-2xl"></div>
                <h4 class="text-[10px] font-black text-background/60 uppercase tracking-[0.3em] mb-4 italic">Student Activity</h4>
                <p class="text-4xl font-black mb-1 font-display italic">0</p>
                <p class="text-[9px] font-bold text-background/70 uppercase tracking-widest leading-tight">Students tracking this module</p>
                <div class="mt-8 pt-8 border-t border-background/20 flex items-center justify-between">
                    <div class="text-center">
                        <p class="text-2xl font-black font-display">0%</p>
                        <p class="text-[8px] text-background/60 font-black uppercase tracking-widest">Avg Progress</p>
                    </div>
                    <div class="text-center">
                        <p class="text-2xl font-black font-display">0</p>
                        <p class="text-[8px] text-background/60 font-black uppercase tracking-widest">Assessments</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="lg:col-span-2 space-y-8">
            <div class="bg-surface rounded-4xl border border-divider p-8 shadow-premium">
                <h3 class="text-[10px] font-black text-text-tertiary uppercase tracking-[0.3em] mb-6 opacity-60 italic">Description</h3>
                <div class="text-text-secondary leading-relaxed font-bold">
                    {{ $module->description }}
                </div>
            </div>

            <div class="bg-surface rounded-4xl border border-divider shadow-premium overflow-hidden">
                <div class="p-8 border-b border-divider flex items-center justify-between bg-surface-light/30">
                    <h3 class="font-black text-white text-xl tracking-tight uppercase italic font-display">Linked Education Contents</h3>
                    <span class="text-[10px] font-black text-primary uppercase tracking-[0.2em] bg-primary/5 px-4 py-1.5 rounded-full border border-primary/20">{{ count($contents) }} items</span>
                </div>
                
                <div class="divide-y divide-divider/50">
                    @forelse($contents as $item)
                    <div class="p-8 hover:bg-primary/5 transition-all duration-300 flex items-center justify-between group">
                        <div class="flex items-center gap-6">
                            <div class="w-16 h-16 bg-surface-light border border-divider rounded-2xl overflow-hidden flex-shrink-0 group-hover:border-primary transition-all">
                                @if($item->thumbnail_url)
                                    <img src="{{ $item->thumbnail_url }}" alt="" class="w-full h-full object-cover">
                                @else
                                    <div class="w-full h-full flex items-center justify-center text-text-tertiary">
                                        @if($item->type === 'Video')
                                            <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                        @elseif($item->type === 'Document')
                                            <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                        @else
                                            <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a2 2 0 00-1.96 1.414l-.722 2.166a2 2 0 01-2.489 1.254l-1.274-.425a2 2 0 01-1.357-1.357l-.425-1.274a2 2 0 011.254-2.489l2.166-.722a2 2 0 001.414-1.96l-.477-2.387a2 2 0 00-.547-1.022L15.428 2.572a2 2 0 012.489-1.254l1.274.425a2 2 0 011.357 1.357l.425 1.274a2 2 0 01-1.254 2.489l-2.166.722a2 2 0 00-1.414 1.96l.477 2.387a2 2 0 00.547 1.022l1.572 1.572z"></path></svg>
                                        @endif
                                    </div>
                                @endif
                            </div>
                            <div>
                                <h4 class="text-sm font-bold text-white group-hover:text-primary transition-colors">{{ $item->title }}</h4>
                                <div class="flex items-center gap-3 mt-2">
                                    <span class="text-[9px] font-black text-primary bg-primary/10 border border-primary/20 px-3 py-1 rounded-full uppercase tracking-widest">{{ strtoupper($item->type) }}</span>
                                    <span class="text-[10px] text-text-tertiary font-mono opacity-50">{{ $item->code }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="flex items-center gap-3 opacity-0 group-hover:opacity-100 transition-all transform translate-x-4 group-hover:translate-x-0">
                            <a href="#" class="p-3 text-text-tertiary hover:text-primary bg-surface-light border border-divider rounded-xl transition-all" title="Edit Content">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                            </a>
                        </div>
                    </div>
                    @empty
                    <div class="p-20 text-center text-text-tertiary font-bold uppercase tracking-[0.4em] opacity-30 italic">
                        No telemetry streams detected.
                    </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
