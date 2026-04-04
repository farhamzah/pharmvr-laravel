@extends('layouts.admin')

@section('header', 'AI Avatar Network')

@section('content')
<div class="mb-10 flex flex-wrap items-center justify-between gap-6">
    <div class="flex flex-col gap-2">
        <h2 class="text-[10px] font-black text-primary uppercase tracking-[0.4em] italic">Personnel Protocol Matrix</h2>
        <p class="text-xs text-text-tertiary font-bold uppercase tracking-widest opacity-80">
            Define and manage the AI personas that serve as guides and specialists within the VR ecosystem.
        </p>
    </div>
    
    <a href="{{ route('admin.ai.avatars.create') }}">
        <x-admin.ai.action-button color="primary" size="lg">Create New Avatar</x-admin.ai.action-button>
    </a>
</div>

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-10">
    @forelse($avatars as $avatar)
    <div class="bg-surface rounded-4xl border border-divider shadow-premium hover:border-primary/40 transition-all group overflow-hidden flex flex-col relative backdrop-blur-sm">
        <!-- Avatar Profile Header -->
        <div class="h-44 bg-surface-light/30 relative overflow-hidden group-hover:bg-primary/5 transition-colors border-b border-divider">
            <div class="absolute inset-x-0 bottom-0 p-10 bg-gradient-to-t from-surface via-surface/80 to-transparent pt-20 z-10">
                <div class="flex items-end justify-between">
                    <div>
                        <h3 class="text-2xl font-black text-white tracking-tighter uppercase font-display italic group-hover:text-primary transition-colors leading-none truncate max-w-[200px]">{{ $avatar->name }}</h3>
                        <p class="text-[10px] font-black text-primary uppercase tracking-[0.2em] mt-3 italic opacity-80">{{ $avatar->role_title }}</p>
                    </div>
                    <div class="pb-1">
                        <x-admin.ai.glow-badge :color="$avatar->is_active ? 'emerald' : 'surface'">
                            {{ $avatar->is_active ? 'ACTIVE_STATUS' : 'OFFLINE_NODE' }}
                        </x-admin.ai.glow-badge>
                    </div>
                </div>
            </div>
            
            <!-- Structural branding -->
            <div class="absolute top-0 right-0 p-8 opacity-5">
                <svg class="w-32 h-32 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                     <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                </svg>
            </div>
        </div>

        <!-- Operational Parameters -->
        <div class="p-10 flex-1 space-y-8">
            <div>
                <span class="text-[9px] font-black text-text-tertiary uppercase tracking-[0.3em] opacity-50 px-1">Persona Definition</span>
                <p class="text-[11px] text-text-tertiary font-bold uppercase tracking-widest line-clamp-3 leading-loose mt-3 italic opacity-80">{{ $avatar->persona_text }}</p>
            </div>

            <div>
                <span class="text-[9px] font-black text-text-tertiary uppercase tracking-[0.3em] opacity-50 px-1">Knowledge Domains</span>
                <div class="flex flex-wrap gap-2 mt-4">
                    @forelse($avatar->allowed_topics_json ?? [] as $topic)
                        <span class="px-3 py-1.5 bg-background border border-divider rounded-xl text-[8px] font-black text-white uppercase tracking-widest group-hover:border-primary/30 transition-colors">{{ $topic }}</span>
                    @empty
                        <span class="text-[8px] text-text-tertiary font-black italic opacity-40 uppercase tracking-widest pl-1">Universal System Access</span>
                    @endforelse
                </div>
            </div>

            <div class="pt-8 border-t border-divider grid grid-cols-2 gap-8">
                <div class="space-y-2">
                    <p class="text-[8px] font-black text-text-tertiary uppercase tracking-[0.3em] opacity-40 italic">Integration Rules</p>
                    <p class="text-xl font-black text-white italic tracking-tighter">{{ $avatar->scene_prompts_count }} DIRECTIVES</p>
                </div>
                <div class="space-y-2">
                    <p class="text-[8px] font-black text-text-tertiary uppercase tracking-[0.3em] opacity-40 italic">Voice Matrix</p>
                    <p class="text-xl font-black text-primary italic tracking-tighter uppercase">{{ $avatar->voice_style ?? 'NEURAL_HD' }}</p>
                </div>
            </div>
        </div>

        <!-- Control Actions -->
        <div class="p-10 pt-0 flex gap-4 mt-auto">
            <a href="{{ route('admin.ai.avatars.edit', $avatar) }}" class="flex-1">
                <x-admin.ai.action-button color="surface" size="md" class="w-full h-12">Edit Configuration</x-admin.ai.action-button>
            </a>
            <form action="{{ route('admin.ai.avatars.toggle', $avatar) }}" method="POST" class="inline">
                @csrf
                @method('PATCH')
                <button type="submit" class="h-12 w-14 bg-{{ $avatar->is_active ? 'red-500/5' : 'emerald-500/5' }} border-2 border-{{ $avatar->is_active ? 'red-500/20' : 'emerald-500/20' }} rounded-2xl flex items-center justify-center hover:bg-{{ $avatar->is_active ? 'red-500/20' : 'emerald-500/20' }} transition-all group/btn" title="{{ $avatar->is_active ? 'Suspend Persona' : 'Activate Persona' }}">
                    <svg class="w-5 h-5 {{ $avatar->is_active ? 'text-red-500 shadow-red-glow' : 'text-emerald-500 shadow-emerald-glow' }} group-hover/btn:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                </button>
            </form>
        </div>
    </div>
    @empty
    <div class="lg:col-span-3">
        <div class="py-32 flex flex-col items-center justify-center text-center bg-surface border border-divider rounded-4xl">
            <div class="w-32 h-32 rounded-[2.5rem] bg-surface-light border border-divider flex items-center justify-center mb-10 relative group">
                <div class="absolute inset-0 bg-primary/20 rounded-[2.5rem] blur-2xl animate-pulse"></div>
                <svg class="w-16 h-16 text-primary/40 relative z-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
            </div>
            <h3 class="text-3xl font-black text-white tracking-[0.3em] uppercase italic mb-6">Persona Subnet Offline</h3>
            <p class="text-xs text-text-tertiary font-bold uppercase tracking-widest max-w-2xl mb-12 opacity-60 leading-loose">No active AI avatars detected in the system network. Initialize your first specialized persona to provide grounded guidance within the VR pharmaceutical environment.</p>
            <a href="{{ route('admin.ai.avatars.create') }}">
                <x-admin.ai.action-button color="primary" size="lg" class="px-12 py-4 shadow-cyan-glow">Initialize First Avatar</x-admin.ai.action-button>
            </a>
        </div>
    </div>
    @endforelse
</div>
@endsection
