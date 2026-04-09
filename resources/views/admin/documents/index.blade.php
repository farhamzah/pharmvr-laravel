@extends('layouts.admin')

@section('header', 'Educational Documents')

@section('content')
<div class="flex flex-col gap-8">
    <!-- Header Section -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 px-2">
        <div>
            <h1 class="text-3xl font-black text-white tracking-tight mb-2">Manage Documents</h1>
            <p class="text-text-tertiary text-sm font-medium italic opacity-60 uppercase tracking-[0.2em]">SOPs, Guides, and Educational Resources</p>
        </div>
        <a href="{{ route('admin.documents.create') }}" class="group relative inline-flex items-center gap-3 bg-primary hover:bg-primary-dark text-background px-8 py-4 rounded-2xl transition-all duration-300 shadow-cyan-glow hover:scale-[1.02]">
            <svg class="w-5 h-5 transition-transform group-hover:rotate-90" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M12 4v16m8-8H4"></path></svg>
            <span class="text-xs font-black uppercase tracking-[0.2em]">Add Document</span>
        </a>
    </div>

    <!-- Stats Overview -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="bg-indigo-900/10 border border-indigo-500/20 rounded-2xl p-6 backdrop-blur-sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-indigo-400 text-xs font-bold uppercase tracking-widest">Total Resources</p>
                    <h3 class="text-3xl font-black text-white mt-1">{{ $contents->count() }}</h3>
                </div>
                <div class="w-12 h-12 bg-indigo-500/10 rounded-xl flex items-center justify-center border border-indigo-500/20 shadow-indigo-glow/20">
                    <svg class="w-6 h-6 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                </div>
            </div>
        </div>
        <div class="bg-emerald-900/10 border border-emerald-500/20 rounded-2xl p-6 backdrop-blur-sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-emerald-400 text-xs font-bold uppercase tracking-widest">Live / Active</p>
                    <h3 class="text-3xl font-black text-white mt-1">{{ $contents->where('is_active', true)->count() }}</h3>
                </div>
                <div class="w-12 h-12 bg-emerald-500/10 rounded-xl flex items-center justify-center border border-emerald-500/20 shadow-emerald-glow/20">
                    <svg class="w-6 h-6 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
            </div>
        </div>
        <div class="bg-amber-900/10 border border-amber-500/20 rounded-2xl p-6 backdrop-blur-sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-amber-400 text-xs font-bold uppercase tracking-widest">Draft / Pending</p>
                    <h3 class="text-3xl font-black text-white mt-1">{{ $contents->where('is_active', false)->count() }}</h3>
                </div>
                <div class="w-12 h-12 bg-amber-500/10 rounded-xl flex items-center justify-center border border-amber-500/20 shadow-amber-glow/20">
                    <svg class="w-6 h-6 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                </div>
            </div>
        </div>
    </div>

    <!-- List Table -->
    <div class="bg-slate-900/40 border border-slate-800 rounded-[2rem] overflow-hidden backdrop-blur-md shadow-2xl">
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead>
                    <tr class="border-b border-slate-800 bg-slate-900/60">
                        <th class="px-8 py-5 text-[10px] font-black text-slate-500 uppercase tracking-[0.2em] italic">Educational Resource</th>
                        <th class="px-8 py-5 text-[10px] font-black text-slate-500 uppercase tracking-[0.2em] italic">Learning Context</th>
                        <th class="px-8 py-5 text-[10px] font-black text-slate-500 uppercase tracking-[0.2em] italic">Associated Module</th>
                        <th class="px-8 py-5 text-[10px] font-black text-slate-500 uppercase tracking-[0.2em] italic">Origin / Source</th>
                        <th class="px-8 py-5 text-[10px] font-black text-slate-500 uppercase tracking-[0.2em] italic text-center">Lifecycle</th>
                        <th class="px-8 py-5 text-[10px] font-black text-slate-500 uppercase tracking-[0.2em] italic text-right">Operations</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/50">
                    @forelse($contents as $doc)
                    <tr class="hover:bg-slate-800/40 transition-all duration-300 group">
                        <!-- Resource (Thumb + Title) -->
                        <td class="px-8 py-5">
                            <div class="flex items-start gap-5">
                                <div class="w-14 h-18 bg-slate-800 rounded-xl overflow-hidden flex-shrink-0 border border-slate-700 shadow-xl group-hover:border-indigo-500/50 transition-all duration-500 group-hover:scale-105">
                                    @if($doc->thumbnail_url)
                                        <img src="{{ $doc->thumbnail_full_url }}" class="w-full h-full object-cover" alt="">
                                    @else
                                        <div class="w-full h-full flex items-center justify-center text-slate-600">
                                            <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                        </div>
                                    @endif
                                </div>
                                <div class="space-y-1.5 flex-1 min-w-0">
                                    <h4 class="text-base font-bold text-white group-hover:text-indigo-400 transition-colors line-clamp-2 leading-snug tracking-tight">
                                        {{ $doc->title }}
                                    </h4>
                                    <div class="flex items-center gap-2.5">
                                        <span class="text-[9px] font-black px-2 py-0.5 rounded-lg bg-slate-800 text-slate-500 tracking-widest uppercase border border-slate-700/50">
                                            {{ $doc->code }}
                                        </span>
                                        <span class="text-[10px] font-black text-indigo-500 uppercase tracking-widest italic opacity-80">
                                            {{ $doc->category }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </td>

                        <!-- Learning Meta -->
                        <td class="px-8 py-5">
                            <div class="flex flex-col gap-2">
                                <div class="flex items-center gap-2">
                                    <span class="text-[10px] font-black text-slate-500 uppercase italic">Difficulty:</span>
                                    <span class="px-2 py-0.5 rounded-full bg-slate-800 text-white text-[10px] font-bold border border-slate-700">
                                        {{ ucfirst($doc->level) }}
                                    </span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <span class="text-[10px] font-black text-slate-500 uppercase italic">Scope:</span>
                                    <span class="text-[11px] font-black text-emerald-400 tracking-wider">{{ $doc->pages_count ?? 0 }} HALAMAN</span>
                                </div>
                                @if($doc->prerequisites)
                                <div class="mt-0.5">
                                    <div class="inline-flex items-center gap-1.5 text-[9px] font-black text-amber-500 bg-amber-500/5 px-2 py-1 rounded-lg border border-amber-500/20 uppercase tracking-widest italic">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                        Strict Prereq.
                                    </div>
                                </div>
                                @endif
                            </div>
                        </td>

                        <!-- Module Link -->
                        <td class="px-8 py-5">
                            @if($doc->trainingModule)
                                <div class="flex flex-col gap-1">
                                    <span class="text-xs font-black text-white uppercase tracking-tight leading-tight group-hover:text-indigo-300 transition-colors">
                                        {{ $doc->trainingModule->title ?? $doc->trainingModule->name }}
                                    </span>
                                    <div class="flex items-center gap-1.5">
                                        <div class="w-1 h-1 rounded-full bg-indigo-500/50"></div>
                                        <span class="text-[9px] text-slate-500 font-bold uppercase tracking-widest italic">Linked Module</span>
                                    </div>
                                </div>
                            @else
                                <div class="inline-flex items-center gap-1.5 text-slate-600 bg-slate-800/10 px-2.5 py-1 rounded-lg border border-slate-800/40 border-dashed">
                                    <span class="text-[10px] font-bold uppercase tracking-widest italic opacity-40">Unlinked</span>
                                </div>
                            @endif
                        </td>

                        <!-- Source Info -->
                        <td class="px-8 py-5">
                            <div class="flex flex-col gap-1.5">
                                @if($doc->source_type === 'external')
                                    <div class="flex items-center gap-2">
                                        <div class="w-1.5 h-1.5 rounded-full bg-amber-500 shadow-amber-glow/40"></div>
                                        <span class="text-[10px] font-black text-amber-500/90 uppercase tracking-[0.1em]">Cloud / Ext</span>
                                    </div>
                                    <div class="text-[9px] text-slate-500 max-w-[130px] truncate italic font-medium">
                                        @php $parsed = parse_url($doc->file_url); @endphp
                                        <span class="text-slate-400">{{ $parsed['host'] ?? 'External Link' }}</span>
                                    </div>
                                @else
                                    <div class="flex items-center gap-2">
                                        <div class="w-1.5 h-1.5 rounded-full bg-indigo-500 shadow-indigo-glow/40"></div>
                                        <span class="text-[10px] font-black text-indigo-500/90 uppercase tracking-[0.1em]">Server / Local</span>
                                    </div>
                                    <div class="text-[9px] text-slate-500 max-w-[130px] truncate italic font-medium">
                                        {{ basename($doc->file_url) }}
                                    </div>
                                @endif
                            </div>
                        </td>

                        <!-- Status -->
                        <td class="px-8 py-5 text-center">
                            @if($doc->is_active)
                                <div class="inline-flex flex-col items-center gap-1">
                                    <span class="px-3 py-1.5 rounded-xl bg-emerald-500/10 text-emerald-500 text-[9px] font-black uppercase tracking-[0.2em] border border-emerald-500/20 shadow-emerald-glow/5">
                                        Published
                                    </span>
                                    <span class="text-[8px] text-emerald-500/40 uppercase font-black italic tracking-tighter">Live On App</span>
                                </div>
                            @else
                                <div class="inline-flex flex-col items-center gap-1">
                                    <span class="px-3 py-1.5 rounded-xl bg-slate-800 text-slate-500 text-[9px] font-black uppercase tracking-[0.2em] border border-slate-700/50">
                                        Draft
                                    </span>
                                    <span class="text-[8px] text-slate-500/40 uppercase font-black italic tracking-tighter">Invisible</span>
                                </div>
                            @endif
                        </td>

                        <!-- Actions -->
                        <td class="px-8 py-5 text-right">
                            <div class="flex items-center justify-end gap-2 translate-x-2 opacity-40 group-hover:opacity-100 group-hover:translate-x-0 transition-all duration-300">
                                <a href="{{ route('admin.documents.edit', $doc) }}" 
                                   class="p-2.5 text-slate-400 bg-slate-800/50 border border-slate-700/50 rounded-xl hover:bg-slate-800 hover:text-white transition-all shadow-lg" 
                                   title="Edit Detail">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                </a>
                                
                                <form action="{{ route('admin.documents.toggle', $doc) }}" method="POST" class="inline">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" 
                                            class="p-2.5 {{ $doc->is_active ? 'text-amber-400 bg-amber-500/5 border-amber-500/10 hover:bg-amber-500' : 'text-emerald-400 bg-emerald-500/5 border-emerald-500/10 hover:bg-emerald-500' }} border rounded-xl hover:text-white transition-all shadow-lg"
                                            title="{{ $doc->is_active ? 'Move to Draft' : 'Publish to App' }}">
                                        @if($doc->is_active)
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg>
                                        @else
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                        @endif
                                    </button>
                                </form>

                                <form action="{{ route('admin.documents.destroy', $doc) }}" method="POST" class="inline" onsubmit="return confirm('Archive resource?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" 
                                            class="p-2.5 text-rose-500/70 bg-rose-500/5 border border-rose-500/10 rounded-xl hover:bg-rose-500 hover:text-white transition-all shadow-lg"
                                            title="Archive Permanent">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-8 py-24 text-center">
                            <div class="flex flex-col items-center justify-center text-slate-500/40">
                                <svg class="w-20 h-20 mb-6 border-2 border-slate-800/50 rounded-[2rem] p-5 border-dashed" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                                <p class="text-sm font-black uppercase tracking-[0.4em] italic">Library Is Empty</p>
                                <p class="text-[10px] mt-2 font-medium">Start contributing educational documents to populate the training base.</p>
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
