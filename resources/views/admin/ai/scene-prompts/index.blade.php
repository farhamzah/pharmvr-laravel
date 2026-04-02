@extends('layouts.admin')

@section('header', 'AI Scene Directives')

@section('content')
<div class="mb-10 flex flex-wrap items-center justify-between gap-6">
    <div class="flex flex-col gap-2">
        <h2 class="text-[10px] font-black text-primary uppercase tracking-[0.4em] italic">Context Control Matrix</h2>
        <p class="text-xs text-text-tertiary font-bold uppercase tracking-widest opacity-80">
            Define context-aware directives that override general avatar behavior for specific VR environments and objects.
        </p>
    </div>
    
    <x-admin.ai.action-button color="primary" size="lg" onclick="document.getElementById('create_prompt_modal').classList.remove('hidden')">Create New Directive</x-admin.ai.action-button>
</div>

<!-- Implementation Reference IDs -->
<div class="mb-10 grid grid-cols-1 md:grid-cols-4 gap-6">
    @foreach([
        ['key' => 'gowning_room', 'title' => 'Gowning Protocol', 'icon' => 'check_circle'],
        ['key' => 'cleanroom_entry', 'title' => 'Airshower Steps', 'icon' => 'air'],
        ['key' => 'tablet_press_area', 'title' => 'Machine Safety', 'icon' => 'settings'],
        ['key' => 'qc_checkpoint', 'title' => 'Sampling Logic', 'icon' => 'science'],
    ] as $example)
    <div class="p-6 rounded-3xl bg-surface/50 border border-divider hover:border-primary/30 transition-all cursor-default group backdrop-blur-sm shadow-inner">
        <p class="text-[9px] font-black text-primary uppercase tracking-[0.3em] mb-2">{{ $example['title'] }}</p>
        <p class="text-[10px] font-mono text-text-tertiary uppercase tracking-tighter opacity-50 group-hover:opacity-100 transition-opacity italic">SCENE_ID: {{ $example['key'] }}</p>
    </div>
    @endforeach
</div>

<div class="bg-surface border border-divider rounded-4xl overflow-hidden shadow-premium backdrop-blur-sm">
    <div class="px-10 py-6 border-b border-divider bg-surface-light/20 flex items-center justify-between">
        <h3 class="text-lg font-black text-white italic tracking-tight uppercase font-display">Active Context Directives</h3>
        <span class="text-[9px] font-black text-text-tertiary uppercase tracking-[0.2em] opacity-50">{{ count($prompts) }} RULES DEPLOYED</span>
    </div>

    <table class="w-full text-left border-collapse">
        <thead class="bg-surface-light/30 text-[9px] font-black text-text-tertiary uppercase tracking-[0.2em] border-b border-divider">
            <tr>
                <th class="px-10 py-5">Assigned Avatar</th>
                <th class="px-8 py-5">Location / Context</th>
                <th class="px-8 py-5">Directive Title</th>
                <th class="px-8 py-5 text-center">Lifecycle</th>
                <th class="px-10 py-5 text-right">Row Actions</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-divider/30 text-xs">
            @forelse($prompts as $prompt)
            <tr class="hover:bg-primary/5 transition-all duration-300 group">
                <td class="px-10 py-6">
                    <div class="font-black text-white group-hover:text-primary transition-colors cursor-default uppercase tracking-widest truncate max-w-[150px]">{{ $prompt->avatar->name }}</div>
                    <div class="text-[9px] text-text-tertiary font-bold uppercase tracking-widest opacity-40 mt-1 italic italic">{{ $prompt->avatar->role_title }}</div>
                </td>
                <td class="px-8 py-6">
                    <div class="flex flex-col gap-1.5">
                        <span class="text-[10px] font-black text-white uppercase tracking-widest flex items-center gap-2 italic">
                            <span class="w-1.5 h-1.5 rounded-full bg-primary opacity-50 group-hover:animate-pulse"></span>
                            ID: {{ $prompt->scene_key }}
                        </span>
                        <span class="text-[8px] font-black text-text-tertiary uppercase tracking-[0.2em] opacity-50 pl-4 italic">OBJECT: {{ $prompt->object_key ?? 'GLOBAL_SCENE' }}</span>
                    </div>
                </td>
                <td class="px-8 py-6">
                    <div class="text-[10px] font-black text-white uppercase tracking-widest group-hover:text-primary transition-colors">{{ $prompt->prompt_title }}</div>
                    <div class="text-[9px] text-text-tertiary font-bold line-clamp-1 mt-2 italic opacity-50 italic">{{ Str::limit($prompt->prompt_text, 50) }}</div>
                </td>
                <td class="px-8 py-6">
                    <div class="flex justify-center">
                        <x-admin.ai.glow-badge :color="$prompt->is_active ? 'emerald' : 'surface'">
                            {{ $prompt->is_active ? 'OPERATIONAL' : 'DORMANT' }}
                        </x-admin.ai.glow-badge>
                    </div>
                </td>
                <td class="px-10 py-6 text-right">
                    <div class="flex items-center justify-end gap-3">
                        <button class="p-2.5 bg-background hover:bg-surface-light border border-divider rounded-xl transition-all group/btn" title="Edit Directive">
                             <svg class="w-4 h-4 text-text-tertiary group-hover/btn:text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                        </button>
                        
                        <form action="{{ route('admin.ai.scene-prompts.destroy', $prompt) }}" method="POST" class="inline" onsubmit="return confirm('EXTERMINATE THIS DIRECTIVE?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="p-2.5 bg-background hover:bg-red-500/10 border border-divider rounded-xl transition-all group/btn" title="Delete Directive">
                                <svg class="w-4 h-4 text-text-tertiary group-hover/btn:text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-4v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                            </button>
                        </form>
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="5" class="py-24 text-center px-10">
                    <p class="text-[10px] font-black text-text-tertiary uppercase tracking-[0.4em] opacity-30 italic leading-loose">No active context directives detected. All inhabitants currently rely on general intelligence protocols without location-specific overrides.</p>
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

<!-- Creation Modal -->
<div id="create_prompt_modal" class="hidden fixed inset-0 z-50 flex items-center justify-center p-6 bg-background/80 backdrop-blur-xl">
    <div class="bg-surface w-full max-w-2xl rounded-4xl border border-divider shadow-premium overflow-hidden">
        <div class="p-10 border-b border-divider bg-surface-light/30 flex items-center justify-between">
            <div>
                <h3 class="font-black text-white text-xl tracking-tight uppercase font-display italic leading-none">Initialize Context Rule</h3>
                <p class="text-[9px] text-text-tertiary font-bold uppercase tracking-widest mt-2 opacity-60">Overriding general persona logic for specific VR triggers</p>
            </div>
            <button onclick="document.getElementById('create_prompt_modal').classList.add('hidden')" class="p-3 bg-surface-light hover:bg-surface border border-divider rounded-xl text-text-tertiary hover:text-white transition-all">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        </div>
        <form action="{{ route('admin.ai.scene-prompts.store') }}" method="POST" class="p-10 space-y-8">
            @csrf
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <div class="space-y-3">
                    <label class="text-[10px] font-black text-text-tertiary uppercase tracking-widest pl-1">Target Avatar</label>
                    <select name="ai_avatar_profile_id" required class="w-full bg-background border-divider border rounded-2xl px-6 py-4 text-sm font-black text-white focus:border-primary focus:ring-0 transition-all uppercase tracking-widest cursor-pointer appearance-none">
                        @foreach($avatars as $avatar)
                            <option value="{{ $avatar->id }}">{{ strtoupper($avatar->name) }} ({{ strtoupper($avatar->role_title) }})</option>
                        @endforeach
                    </select>
                </div>
                <x-admin.input-group label="Directive Identification" name="prompt_title" required placeholder="E.G. CLEANROOM_ENTRY_CAUTION" />
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <x-admin.input-group label="Scene Index Key" name="scene_key" required placeholder="E.G. airshower_tunnel" />
                <x-admin.input-group label="Object Focus (Optional)" name="object_key" placeholder="E.G. door_interlock_panel" />
            </div>

            <x-admin.input-group label="Contextual Prompt Intelligence" type="textarea" name="prompt_text" required placeholder="Specific instructions for the AI when interacting with this scene/object..." rows="6" />
            
            <div class="flex items-center gap-6 pt-4">
                <button type="button" onclick="document.getElementById('create_prompt_modal').classList.add('hidden')" class="flex-1 py-4 bg-surface-light border border-divider rounded-2xl text-[10px] font-black text-text-tertiary uppercase tracking-widest hover:text-white transition-all">DISCARD</button>
                <x-admin.ai.action-button type="submit" color="primary" class="flex-1 py-4 shadow-cyan-glow">COMMIT DIRECTIVE</x-admin.ai.action-button>
            </div>
        </form>
    </div>
</div>
@endsection
