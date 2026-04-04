@extends('layouts.admin')

@section('header', 'Knowledge Matrix')

@section('content')
<div class="max-w-[1600px] mx-auto px-6 lg:px-10 py-8 font-sans antialiased text-zinc-400">
    
    {{-- Header: Professional, Minimal, High-Contrast --}}
    <div class="flex flex-col md:flex-row md:items-end justify-between mb-12 gap-6 border-b border-white/5 pb-8">
        <div>
            <div class="flex items-center gap-3 mb-2">
                <span class="px-2 py-0.5 rounded bg-primary/10 text-primary text-[10px] font-bold tracking-widest uppercase">Knowledge Matrix</span>
                <span class="text-[10px] text-zinc-600 font-mono tracking-tighter italic">V_2.5_STABLE</span>
            </div>
            <h1 class="text-3xl font-bold text-white tracking-tight leading-none mb-3">Enterprise Knowledge Corpus</h1>
            <p class="text-sm text-zinc-500 font-medium">Manage and monitor trusted GMP/CPOB datasets for PharmVR AI retrieval.</p>
        </div>
        
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.ai.sources.create') }}">
                <button class="bg-primary hover:bg-primary-hover text-zinc-950 px-8 py-3 rounded-lg font-bold text-xs transition-all shadow-lg active:scale-95 flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"></path></svg>
                    Upload Source
                </button>
            </a>
        </div>
    </div>

    {{-- Linear-Inspired Stats Bar: Minimal, Horizontal, Pro --}}
    <div class="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-5 gap-4 mb-10">
        @php
            $linearStats = [
                ['label' => 'Total Corpus', 'value' => $stats['total'] ?? 0, 'color' => 'primary', 'tag' => 'RAW'],
                ['label' => 'Active Sync', 'value' => $stats['active'] ?? 0, 'color' => 'emerald', 'tag' => 'LIVE'],
                ['label' => 'Processing', 'value' => $stats['processing'] ?? 0, 'color' => 'amber', 'tag' => 'QUEUE'],
                ['label' => 'RAG Ready', 'value' => $stats['ready'] ?? 0, 'color' => 'blue', 'tag' => 'IDLE'],
                ['label' => 'Fault Detect', 'value' => $stats['failed'] ?? 0, 'color' => 'red', 'tag' => 'ERR'],
            ];
        @endphp

        @foreach($linearStats as $stat)
        <div class="bg-zinc-900/40 border border-white/5 rounded-xl p-5 hover:bg-zinc-900/60 transition-colors group">
            <div class="flex flex-col gap-1">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-[10px] font-bold text-zinc-500 uppercase tracking-widest">{{ $stat['label'] }}</span>
                    <div class="w-1.5 h-1.5 rounded-full bg-{{ $stat['color'] }}-500"></div>
                </div>
                <div class="flex items-baseline gap-2">
                    <span class="text-2xl font-bold text-white leading-none">{{ number_format($stat['value']) }}</span>
                    <span class="text-[9px] font-medium text-zinc-600 font-mono tracking-tighter">{{ $stat['tag'] }}</span>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    {{-- Unified Linear Command Bar: One-Row Table Controller --}}
    <div class="bg-zinc-950/50 border border-white/5 rounded-xl p-2 mb-10">
        <form action="{{ route('admin.ai.sources.index') }}" method="GET" class="flex flex-col lg:flex-row items-stretch lg:items-center gap-2">
            {{-- Search Pill --}}
            <div class="relative flex-grow min-w-[300px]">
                <div class="absolute inset-y-0 left-4 flex items-center pointer-events-none text-zinc-500">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                </div>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search identifier..." 
                    class="bg-zinc-900 border-white/5 border rounded-lg pl-11 pr-4 h-11 text-xs font-medium text-white focus:border-primary/50 focus:ring-0 transition-all w-full placeholder:text-zinc-600">
            </div>
            
            {{-- Select Pills --}}
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-2 flex-shrink-0">
                @foreach(['module_id' => 'Module', 'status' => 'Status', 'trust_level' => 'Trust'] as $name => $label)
                <select name="{{ $name }}" class="bg-zinc-900 border-white/5 border rounded-lg px-4 pr-10 h-11 text-xs font-medium text-white focus:border-primary/50 focus:ring-0 transition-all appearance-none cursor-pointer">
                    <option value="">{{ $label }}</option>
                    @if($name === 'module_id') @foreach($modules as $module) <option value="{{ $module->id }}" {{ request('module_id') == $module->id ? 'selected' : '' }}>{{ $module->title }}</option> @endforeach
                    @elseif($name === 'status') @foreach(\App\Enums\AiSourceStatus::cases() as $st) <option value="{{ $st->value }}" {{ request('status') === $st->value ? 'selected' : '' }}>{{ $st->name }}</option> @endforeach
                    @else @foreach(\App\Enums\TrustLevel::cases() as $tl) <option value="{{ $tl->value }}" {{ request('trust_level') === $tl->value ? 'selected' : '' }}>{{ $tl->name }}</option> @endforeach @endif
                </select>
                @endforeach
            </div>

            {{-- Actions --}}
            <div class="flex items-center gap-2 flex-shrink-0">
                <button type="submit" class="bg-primary/10 hover:bg-primary/20 text-primary h-11 px-6 rounded-lg text-xs font-bold transition-all flex items-center gap-2 group">
                    <svg class="w-4 h-4 group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                    Apply Filter
                </button>
                <a href="{{ route('admin.ai.sources.index') }}" class="w-11 h-11 bg-zinc-900 border border-white/5 rounded-lg flex items-center justify-center hover:bg-zinc-800 transition-all text-zinc-500" title="Reset Filters">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"></path></svg>
                </a>
            </div>
        </form>
    </div>

    {{-- Main Workspace Card: Simplified and Robust --}}
    <div class="bg-zinc-950/20 border border-white/5 rounded-2xl overflow-hidden min-h-[500px] flex flex-col">
        <div class="px-8 py-6 border-b border-white/5 flex items-center justify-between">
            <h3 class="text-sm font-bold text-white tracking-tight uppercase">Corpus Index</h3>
            <span class="text-[10px] font-bold text-zinc-600 bg-zinc-900 px-3 py-1 rounded-full">{{ $sources->total() }} Records Matched</span>
        </div>

        <div class="flex-grow overflow-x-auto">
            @if($sources->isNotEmpty())
                <table class="w-full text-left border-collapse">
                    <thead class="bg-zinc-900/50 text-[10px] font-bold text-zinc-500 uppercase tracking-widest border-b border-white/5">
                        <tr>
                            <th class="px-8 py-4">Title / Identifier</th>
                            <th class="px-6 py-4">Module Integration</th>
                            <th class="px-6 py-4">Trust Level</th>
                            <th class="px-6 py-4">Sync Status</th>
                            <th class="px-6 py-4">Chunks</th>
                            <th class="px-8 py-4 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/5 text-xs font-medium">
                        @foreach($sources as $source)
                        <tr class="hover:bg-white/[0.02] transition-colors group">
                            <td class="px-8 py-6">
                                <div class="flex flex-col gap-0.5">
                                    <span class="text-white font-bold tracking-tight group-hover:text-primary transition-colors text-sm">{{ $source->title }}</span>
                                    <span class="text-[10px] text-zinc-600 font-mono tracking-tighter">{{ strtoupper($source->source_type->value) }} PAYLOAD</span>
                                </div>
                            </td>
                            <td class="px-6 py-6 text-zinc-400">
                                {{ $source->module->title ?? 'N/A' }}
                            </td>
                            <td class="px-6 py-6">
                                @php
                                    $trustStatus = match($source->trust_level->value ?? 'GENERAL') {
                                        'VERIFIED' => ['text-emerald-400', 'bg-emerald-500/10'],
                                        'INTERNAL' => ['text-blue-400', 'bg-blue-500/10'],
                                        default => ['text-zinc-500', 'bg-zinc-500/10']
                                    };
                                @endphp
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold {{ $trustStatus[0] }} {{ $trustStatus[1] }}">
                                    {{ $source->trust_level->value ?? 'GENERAL' }}
                                </span>
                            </td>
                            <td class="px-6 py-6">
                                @php
                                    $st = match($source->status->value ?? 'draft') {
                                        'active' => ['text-emerald-400', 'bg-emerald-400'],
                                        'ready', 'indexed' => ['text-primary', 'bg-primary'],
                                        'processing', 'uploaded' => ['text-amber-400', 'bg-amber-400'],
                                        'failed' => ['text-red-400', 'bg-red-400'],
                                        default => ['text-zinc-500', 'bg-zinc-500']
                                    };
                                @endphp
                                <div class="flex items-center gap-2">
                                    <div class="w-1.5 h-1.5 rounded-full {{ $st[1] }} {{ in_array($source->status->value, ['processing', 'uploaded']) ? 'animate-pulse' : '' }}"></div>
                                    <span class="text-[10px] font-bold uppercase {{ $st[0] }}">{{ $source->status->value ?? 'DRAFT' }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-6 font-mono text-zinc-500">
                                {{ number_format($source->chunks_count ?: $source->total_chunks) }}
                            </td>
                             <td class="px-8 py-6 text-right">
                                <div class="flex items-center justify-end gap-2 transition-all duration-300">
                                    {{-- View Detail --}}
                                    <a href="{{ route('admin.ai.sources.show', $source) }}" class="p-2 border border-white/5 rounded-lg hover:bg-zinc-900 transition-colors text-zinc-500 hover:text-white" title="View Detail">
                                         <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                    </a>

                                    {{-- View File Link --}}
                                    @if($source->file_path)
                                        <a href="{{ asset('storage/' . $source->file_path) }}" target="_blank" class="p-2 border border-white/5 rounded-lg hover:bg-zinc-900 transition-colors text-blue-400 hover:text-blue-300" title="View/Download File">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 16v1a2 2 0 002 2h12a2 2 0 002-2v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                                        </a>
                                    @endif

                                    {{-- Edit --}}
                                    <a href="{{ route('admin.ai.sources.edit', $source) }}" class="p-2 border border-white/5 rounded-lg hover:bg-zinc-900 transition-colors text-amber-500 hover:text-amber-400" title="Edit Metadata">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                    </a>

                                    {{-- Reprocess --}}
                                    <form action="{{ route('admin.ai.sources.reprocess', $source) }}" method="POST" class="inline">
                                        @csrf
                                        <button type="submit" class="p-2 border border-white/5 rounded-lg hover:bg-zinc-900 transition-colors text-primary hover:text-primary-hover" title="Reprocess Source">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m13 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                                        </button>
                                    </form>

                                    {{-- Delete --}}
                                    <form action="{{ route('admin.ai.sources.destroy', $source) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to PERMANENTLY delete this knowledge source and ALL its chunks?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="p-2 border border-white/5 rounded-lg hover:bg-zinc-900 transition-colors text-red-500 hover:text-red-400" title="Delete Source">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                {{-- Clean Linear Empty State --}}
                <div class="flex flex-col items-center justify-center text-center py-20 px-8 flex-grow">
                    <div class="w-16 h-16 bg-zinc-900 border border-white/5 rounded-2xl flex items-center justify-center mb-6 text-zinc-600">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path></svg>
                    </div>
                    <h3 class="text-xl font-bold text-white mb-3">No Knowledge Sources Found</h3>
                    <p class="text-sm text-zinc-500 max-w-sm mb-10 leading-relaxed font-medium">Initialize the knowledge base by uploading SOPs or training materials to the enterprise registry.</p>
                    <div class="flex items-center gap-4">
                        <a href="{{ route('admin.ai.sources.create') }}" class="bg-primary hover:bg-primary-hover text-zinc-950 px-8 py-3 rounded-lg font-bold text-xs transition-all flex items-center gap-2">
                             Upload First Source
                        </a>
                        <button class="bg-zinc-900 hover:bg-zinc-800 text-zinc-400 px-8 py-3 rounded-lg font-bold text-xs transition-all border border-white/5">
                            Documentation
                        </button>
                    </div>
                </div>
            @endif
        </div>

        @if($sources->isNotEmpty() && $sources->hasPages())
        <div class="px-8 py-6 border-t border-white/5 bg-zinc-950/20">
            {{ $sources->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
