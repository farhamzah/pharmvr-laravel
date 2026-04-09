@extends('layouts.admin')

@section('header', 'Educational Videos')

@section('content')
<div class="max-w-6xl mx-auto">
    <div class="mb-10 flex flex-col md:flex-row md:items-center justify-between gap-6">
        <div>
            <h2 class="text-3xl font-black text-white tracking-tight font-display uppercase italic">Educational Videos</h2>
            <p class="text-[10px] font-black text-text-tertiary uppercase tracking-[0.3em] mt-2 opacity-60 italic">Manage your training video content</p>
        </div>
        <a href="{{ route('admin.videos.create') }}" class="px-8 py-4 bg-primary text-background rounded-2xl text-[10px] font-black uppercase tracking-[0.2em] hover:bg-primary-dark transition-all shadow-cyan-glow flex items-center gap-3">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M12 4v16m8-8H4"></path></svg>
            Add Training Video
        </a>
    </div>

    <div class="bg-surface rounded-4xl border border-divider shadow-premium overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-surface-light/30 border-b border-divider">
                        <th class="px-8 py-6 text-[10px] font-black text-text-tertiary uppercase tracking-[0.2em]">Video Content</th>
                        <th class="px-8 py-6 text-[10px] font-black text-text-tertiary uppercase tracking-[0.2em]">Video Info</th>
                        <th class="px-8 py-6 text-[10px] font-black text-text-tertiary uppercase tracking-[0.2em]">Linked Module</th>
                        <th class="px-8 py-6 text-[10px] font-black text-text-tertiary uppercase tracking-[0.2em]">Status</th>
                        <th class="px-8 py-6 text-[10px] font-black text-text-tertiary uppercase tracking-[0.2em] text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-divider/50">
                    @forelse($videos as $video)
                    <tr class="hover:bg-primary/5 transition-all duration-300 group {{ $video->is_active ? '' : 'opacity-60' }}">
                        <td class="px-8 py-6">
                            <div class="flex items-center gap-6">
                                <div class="relative w-32 h-20 rounded-xl overflow-hidden border border-divider group-hover:border-primary/50 transition-all bg-background">
                                    <img src="{{ $video->thumbnail_full_url ?: 'https://img.youtube.com/vi/'.$video->video_id.'/hqdefault.jpg' }}" 
                                         class="w-full h-full object-cover"
                                         onerror="this.src='https://img.youtube.com/vi/{{ $video->video_id }}/mqdefault.jpg'">
                                    <div class="absolute inset-0 bg-background/40 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-all">
                                        <svg class="w-8 h-8 text-primary" fill="currentColor" viewBox="0 0 20 20"><path d="M6.3 2.841A1.5 1.5 0 004 4.11V15.89a1.5 1.5 0 002.3 1.269l9.333-5.89a1.5 1.5 0 000-2.538L6.3 2.841z"></path></svg>
                                    </div>
                                </div>
                                <div class="min-w-0">
                                    <p class="text-[9px] font-black text-primary uppercase tracking-widest mb-1">{{ $video->category }}</p>
                                    <h4 class="text-sm font-bold text-white truncate max-w-[200px]">{{ $video->title }}</h4>
                                </div>
                            </div>
                        </td>
                        <td class="px-8 py-6">
                            <div class="space-y-1">
                                <div class="flex items-center gap-2">
                                    <span class="text-[9px] font-black text-text-tertiary uppercase tracking-widest opacity-60">ID:</span>
                                    <span class="text-[10px] font-mono text-white">{{ $video->video_id }}</span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <span class="text-[9px] font-black text-text-tertiary uppercase tracking-widest opacity-60">Duration:</span>
                                    <span class="text-[10px] font-bold text-white italic uppercase">{{ $video->duration_minutes }} MIN</span>
                                </div>
                            </div>
                        </td>
                        <td class="px-8 py-6">
                            <div class="space-y-2">
                                <p class="text-[9px] font-black text-text-tertiary uppercase tracking-widest mb-1 opacity-60">Training Module</p>
                                <span class="px-3 py-1 bg-surface-light border border-divider rounded-full text-[9px] font-black uppercase tracking-widest text-text-secondary italic">
                                    {{ $video->trainingModule->title ?? 'None' }}
                                </span>
                            </div>
                        </td>
                        <td class="px-8 py-6">
                            <form action="{{ route('admin.videos.toggle', $video) }}" method="POST">
                                @csrf @method('PATCH')
                                <button type="submit" class="relative inline-flex items-center cursor-pointer group/toggle">
                                    <div class="w-10 h-5 bg-surface-light border border-divider rounded-full transition-all peer-checked:bg-primary {{ $video->is_active ? 'bg-primary' : '' }}">
                                        <div class="absolute top-[4px] left-[4px] bg-text-tertiary rounded-full h-3 after:h-3 w-3 transition-all transform {{ $video->is_active ? 'translate-x-5 bg-background shadow-cyan-glow' : 'bg-text-tertiary opacity-40' }}"></div>
                                    </div>
                                    <span class="ml-3 text-[9px] font-black uppercase tracking-widest {{ $video->is_active ? 'text-primary' : 'text-text-tertiary' }}">
                                        {{ $video->is_active ? 'Published' : 'Unpublished' }}
                                    </span>
                                </button>
                            </form>
                        </td>
                        <td class="px-8 py-6 text-right">
                            <div class="flex items-center justify-end gap-3">
                                <a href="{{ route('admin.videos.edit', $video) }}" class="p-3 bg-surface-light border border-divider text-text-tertiary hover:text-primary hover:border-primary rounded-xl transition-all">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                                </a>
                                <form action="{{ route('admin.videos.destroy', $video) }}" method="POST" onsubmit="return confirm('Delete this video content permanently?')">
                                    @csrf @method('DELETE')
                                    <button class="p-3 bg-surface-light border border-divider text-text-tertiary hover:text-red-500 hover:border-red-500/50 rounded-xl transition-all">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="px-8 py-20 text-center text-text-tertiary font-bold uppercase tracking-[0.4em] opacity-30 italic">
                            No video content found.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($videos->hasPages())
        <div class="px-8 py-6 bg-surface-light/30 border-t border-divider">
            {{ $videos->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
