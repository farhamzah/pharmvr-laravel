@extends('layouts.admin')

@section('header', 'Knowledge Node Lifecycle')

@section('content')
<div class="mb-10 flex items-center justify-between">
    <a href="{{ route('admin.ai.sources.index') }}" class="flex items-center gap-2 text-[10px] font-black text-text-tertiary hover:text-primary transition-all uppercase tracking-[0.2em]">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"></path></svg>
        Return to Matrix
    </a>
    
    <div class="flex items-center gap-4">
        <a href="{{ route('admin.ai.sources.edit', $source) }}">
            <x-admin.ai.action-button color="surface">Edit Metadata</x-admin.ai.action-button>
        </a>
        <form action="{{ route('admin.ai.sources.reprocess', $source) }}" method="POST">
            @csrf
            <x-admin.ai.action-button color="amber">Full Reprocess</x-admin.ai.action-button>
        </form>
        <form action="{{ route('admin.ai.sources.reindex', $source) }}" method="POST">
            @csrf
            <x-admin.ai.action-button color="primary">Reindex Only</x-admin.ai.action-button>
        </form>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-10">
    <div class="lg:col-span-2 space-y-10">
        {{-- Lifecycle Roadmap --}}
        <x-admin.card title="Neural Ingestion Roadmap" helper="Track the progression of this knowledge node through the AI pipeline.">
            <div class="relative py-10 px-4">
                {{-- Status Line Background --}}
                <div class="absolute top-[3.75rem] left-12 right-12 h-1 bg-surface-light rounded-full z-0"></div>
                
                @php
                    $steps = [
                        ['id' => 'draft', 'label' => 'DRAFT', 'icon' => 'pencil'],
                        ['id' => 'uploaded', 'label' => 'UPLOADED', 'icon' => 'cloud-upload'],
                        ['id' => 'processing', 'label' => 'PROCESSING', 'icon' => 'refresh'],
                        ['id' => 'indexed', 'label' => 'INDEXED', 'icon' => 'database'],
                        ['id' => 'ready', 'label' => 'READY', 'icon' => 'check-circle'],
                        ['id' => 'active', 'label' => 'ACTIVE', 'icon' => 'zap'],
                    ];
                    
                    $currentStatus = $source->status->value ?? 'draft';
                    $failed = $currentStatus === 'failed';
                    $reachedCurrent = false;
                @endphp

                <div class="flex justify-between items-start relative z-10">
                    @foreach($steps as $index => $step)
                        @php
                            $isActive = $currentStatus === $step['id'];
                            $isCompleted = !$reachedCurrent && !$isActive && !$failed;
                            if ($isActive) $reachedCurrent = true;
                            
                            $color = $isCompleted ? 'emerald' : ($isActive ? 'primary' : 'surface');
                            if ($failed && $isActive) $color = 'red';
                        @endphp
                        
                        <div class="flex flex-col items-center gap-4 w-20 group">
                            <div class="w-12 h-12 rounded-2xl bg-{{ $color === 'emerald' ? 'emerald-500/20' : ($color === 'primary' ? 'primary/20' : 'background') }} border-2 {{ $color === 'emerald' ? 'border-emerald-500' : ($color === 'primary' ? 'border-primary ring-4 ring-primary/10' : 'border-divider') }} flex items-center justify-center transition-all">
                                <span class="text-{{ $color === 'emerald' ? 'emerald-400' : ($color === 'primary' ? 'primary' : 'text-tertiary opacity-40') }}">
                                    @if($step['icon'] === 'zap')
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                                    @elseif($step['icon'] === 'cloud-upload')
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path></svg>
                                    @else
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                    @endif
                                </span>
                            </div>
                            <div class="text-center">
                                <p class="text-[9px] font-black {{ $isActive ? 'text-white' : 'text-text-tertiary opacity-60' }} uppercase tracking-tighter">{{ $step['label'] }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            @if($failed)
                <div class="mt-4 p-6 bg-red-500/10 border border-red-500/20 rounded-2xl flex items-center gap-4">
                    <div class="w-10 h-10 rounded-xl bg-red-500/20 text-red-500 flex items-center justify-center shrink-0">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                    </div>
                    <div>
                        <p class="text-[10px] font-black text-red-500 uppercase tracking-widest">Initialization Failure Detected</p>
                        <p class="text-[9px] text-text-tertiary uppercase mt-0.5 opacity-60 italic">Error: Neural parsing of payload was interrupted. Please review format or reprocess.</p>
                    </div>
                </div>
            @endif
        </x-admin.card>

        <x-admin.card title="Knowledge Signature" :helper="'ID: ' . strtoupper($source->id)">
            <div class="space-y-10">
                <div>
                    <h1 class="text-4xl font-black text-white tracking-tighter italic uppercase font-display mb-4">{{ $source->title }}</h1>
                    <p class="text-sm text-text-tertiary leading-relaxed mb-8 max-w-2xl italic font-medium opacity-80">{{ $source->description ?? 'No metadata abstract provided.' }}</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-8 bg-background/30 p-8 rounded-3xl border border-divider/50 border-dashed">
                    <div class="space-y-6">
                        <div>
                            <span class="text-[9px] font-black text-primary uppercase tracking-[0.3em] italic">Origin Subject</span>
                            <p class="text-lg font-bold text-white uppercase mt-1">{{ $source->author ?? 'ANONYMOUS_INTEL' }}</p>
                        </div>
                        <div>
                            <span class="text-[9px] font-black text-primary uppercase tracking-[0.3em] italic">Cluster Focus</span>
                            <p class="text-lg font-bold text-white uppercase mt-1">{{ $source->topic }}</p>
                        </div>
                    </div>
                    <div class="space-y-6">
                        <div>
                            <span class="text-[9px] font-black text-primary uppercase tracking-[0.3em] italic">Transmission Sync</span>
                            <p class="text-lg font-bold text-white uppercase mt-1">{{ $source->publication_year ?? 'N/A' }} // {{ $source->publisher ?? 'DIRECT_LINK' }}</p>
                        </div>
                        <div>
                            <span class="text-[9px] font-black text-primary uppercase tracking-[0.3em] italic">Language Protocol</span>
                            <p class="text-lg font-bold text-white uppercase mt-1">{{ strtoupper($source->language ?? 'id') }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </x-admin.card>
    </div>

    <!-- Right Sidebar: Status & Integrity -->
    <div class="space-y-10">
        <div class="bg-surface rounded-4xl border border-divider shadow-premium overflow-hidden">
            <div class="p-8 border-b border-divider bg-surface-light/30">
                <h3 class="font-black text-white text-xl tracking-tight uppercase font-display italic text-center">Operational State</h3>
            </div>
            <div class="p-8 space-y-8">
                <div class="flex flex-col items-center gap-4 pb-4">
                    @php
                        $statusColor = match($currentStatus) {
                            'active' => 'emerald',
                            'ready', 'indexed' => 'primary',
                            'processing', 'uploaded' => 'amber',
                            'failed' => 'red',
                            default => 'surface'
                        };
                    @endphp
                    <div class="w-24 h-24 rounded-full border-4 border-{{ $statusColor }}-500/20 flex items-center justify-center relative">
                        <div class="absolute inset-0 bg-{{ $statusColor }}-500/5 rounded-full animate-ping"></div>
                        <div class="w-16 h-16 rounded-full bg-{{ $statusColor }}-500/10 border border-{{ $statusColor }}-500/30 flex items-center justify-center text-{{ $statusColor }}-400">
                             <span class="text-2xl font-black uppercase italic">{{ substr($currentStatus, 0, 1) }}</span>
                        </div>
                    </div>
                    <div class="text-center">
                        <p class="text-xs font-black text-white uppercase tracking-[0.2em] italic">{{ str_replace('_', ' ', strtoupper($currentStatus)) }}</p>
                        <p class="text-[9px] text-text-tertiary uppercase mt-1 font-bold opacity-60">Neural Node Availability: {{ $source->is_active ? 'ENABLED' : 'DISABLED' }}</p>
                    </div>
                </div>

                <div class="space-y-6 pt-4 border-t border-divider">
                    <div class="flex items-center justify-between">
                        <span class="text-[10px] font-black text-text-tertiary uppercase tracking-widest italic opacity-60">Parsing Stage</span>
                        <x-admin.ai.glow-badge :color="$source->parsing_status->value === 'completed' ? 'emerald' : ($source->parsing_status->value === 'failed' ? 'red' : 'amber')">
                            {{ strtoupper($source->parsing_status->value) }}
                        </x-admin.ai.glow-badge>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-[10px] font-black text-text-tertiary uppercase tracking-widest italic opacity-60">Indexing Stage</span>
                        <x-admin.ai.glow-badge :color="$source->indexing_status->value === 'completed' ? 'emerald' : ($source->indexing_status->value === 'failed' ? 'red' : 'amber')">
                            {{ strtoupper($source->indexing_status->value) }}
                        </x-admin.ai.glow-badge>
                    </div>
                    <div class="pt-4 space-y-4">
                        <div class="flex items-center justify-between text-[10px] font-black uppercase tracking-widest">
                            <span class="opacity-60 italic">Synthesized Chunks</span>
                            <span class="text-white">{{ number_format($source->chunks_count ?: $source->total_chunks) }} Chunks</span>
                        </div>
                        <div class="w-full bg-background h-1.5 rounded-full overflow-hidden border border-divider">
                            <div class="bg-primary h-full rounded-full shadow-cyan-glow transition-all duration-1000" style="width: {{ min(100, (($source->chunks_count ?: $source->total_chunks) > 0 ? 100 : 0)) }}%"></div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="px-8 pb-8 space-y-3">
                <form action="{{ route('admin.ai.sources.toggle', $source) }}" method="POST">
                    @csrf
                    @method('PATCH')
                    <button type="submit" class="w-full text-center py-5 {{ $source->is_active ? 'bg-red-500/10 text-red-500 hover:bg-red-500' : 'bg-emerald-500/10 text-emerald-500 hover:bg-emerald-500' }} hover:text-background border border-{{ $source->is_active ? 'red-500/30' : 'emerald-500/30' }} rounded-2xl text-[9px] font-black uppercase tracking-[0.3em] transition-all group">
                        {{ $source->is_active ? 'DEACTIVATE_NODE' : 'ACTIVATE_SIGNAL' }}
                    </button>
                </form>
            </div>
        </div>

        {{-- Payload Details --}}
        <div class="bg-surface-light/20 border border-divider rounded-4xl p-8 space-y-6">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 rounded-2xl bg-background border border-divider flex items-center justify-center text-primary group-hover:scale-110 transition-transform">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                </div>
                <div class="min-w-0">
                    <p class="text-[11px] font-black text-white uppercase tracking-widest truncate">{{ basename($source->file_path) ?: 'MANUAL_CONTENT' }}</p>
                    <p class="text-[9px] text-text-tertiary font-bold uppercase tracking-widest italic opacity-50 mt-0.5">Payload Identity</p>
                </div>
            </div>
            
            <div class="space-y-3 pt-6 border-t border-divider/50">
                <div class="flex justify-between items-center text-[9px] font-mono text-text-tertiary">
                    <span class="opacity-40">TYPE:</span>
                    <span class="uppercase italic">{{ $source->source_type->value }}</span>
                </div>
                <div class="flex justify-between items-center text-[9px] font-mono text-text-tertiary">
                    <span class="opacity-40">SIZE:</span>
                    <span class="uppercase italic">0.0 MB</span>
                </div>
                <div class="flex justify-between items-center text-[9px] font-mono text-text-tertiary">
                    <span class="opacity-40">CRC_SIGNATURE:</span>
                    <span class="uppercase italic">#{{ strtoupper(substr(md5($source->id), 0, 8)) }}</span>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
