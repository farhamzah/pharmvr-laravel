@extends('layouts.admin')

@section('header')
<div class="flex flex-col gap-1">
    <div class="flex items-center gap-2 text-[10px] font-black uppercase tracking-[0.3em] text-text-tertiary opacity-60 italic">
        <a href="{{ route('admin.dashboard') }}" class="hover:text-primary transition-colors">Intelligence</a>
        <span class="text-primary/30">/</span>
        <span class="text-white">Education</span>
    </div>
    <h1 class="text-2xl font-black text-white tracking-tight font-display uppercase italic">Courses & Modules</h1>
</div>
@endsection

@section('content')
<div class="bg-surface rounded-4xl border border-divider shadow-premium overflow-hidden">
    <div class="p-8 border-b border-divider bg-surface-light/30 flex items-center justify-between">
        <h3 class="font-black text-white text-xl tracking-tight uppercase italic font-display">Training Modules</h3>
        <a href="{{ route('admin.education.create') }}" class="bg-primary hover:bg-primary-dark text-background px-8 py-3 rounded-2xl text-[10px] font-black uppercase tracking-[0.2em] transition-all shadow-cyan-glow flex items-center gap-3">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M12 4v16m8-8H4"></path></svg>
            Create Module
        </a>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead class="bg-surface-light/50 text-[10px] font-black text-text-tertiary uppercase tracking-[0.3em] border-b border-divider">
                <tr>
                    <th class="px-10 py-5">Title & Slug</th>
                    <th class="px-10 py-5">Difficulty</th>
                    <th class="px-10 py-5">Duration</th>
                    <th class="px-10 py-5">Assessments</th>
                    <th class="px-10 py-5">Status</th>
                    <th class="px-10 py-5 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-divider/50">
                @forelse($modules as $module)
                <tr class="hover:bg-primary/5 transition-all duration-300 group">
                    <td class="px-10 py-6">
                        <div class="flex items-center gap-4">
                            <div class="w-12 h-12 rounded-lg border border-divider bg-surface-light/50 overflow-hidden flex-shrink-0">
                                @if($module->cover_image_url)
                                    <img src="{{ $module->cover_image_url }}" alt="" class="w-full h-full object-cover">
                                @else
                                    <div class="w-full h-full flex items-center justify-center text-text-tertiary/20">
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                    </div>
                                @endif
                            </div>
                            <div>
                                <div class="text-sm font-bold text-white group-hover:text-primary transition-colors">{{ $module->title }}</div>
                                <div class="text-[10px] text-text-tertiary font-mono opacity-50">{{ $module->slug }}</div>
                            </div>
                        </div>
                    </td>
                    <td class="px-10 py-6">
                        <span class="px-3 py-1.5 rounded-full text-[9px] font-black uppercase tracking-[0.2em] border {{ $module->difficulty === 'Beginner' ? 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20' : ($module->difficulty === 'Intermediate' ? 'bg-amber-500/10 text-amber-400 border-amber-500/20' : 'bg-rose-500/10 text-rose-400 border-rose-500/20') }}">
                            {{ $module->difficulty }}
                        </span>
                    </td>
                    <td class="px-10 py-6 text-[10px] font-bold text-text-tertiary uppercase tracking-widest opacity-60">
                        {{ $module->estimated_duration ?? '-' }}
                    </td>
                    <td class="px-10 py-6 text-[10px] font-black text-primary uppercase tracking-widest">
                        {{ $module->assessments_count }} items
                    </td>
                    <td class="px-10 py-6">
                        @if($module->is_active)
                            <span class="px-4 py-1.5 bg-primary/10 text-primary border border-primary/20 rounded-full text-[9px] font-black uppercase tracking-[0.2em] shadow-cyan-glow">
                                Active
                            </span>
                        @else
                            <span class="px-4 py-1.5 bg-surface-light text-text-tertiary border border-divider rounded-full text-[9px] font-black uppercase tracking-[0.2em]">
                                Draft
                            </span>
                        @endif
                    </td>
                    <td class="px-10 py-6 text-right">
                        <div class="flex items-center justify-end gap-3">
                            <a href="{{ route('admin.education.show', $module) }}" class="p-3 text-text-tertiary hover:text-primary bg-surface-light/50 hover:bg-surface-light border border-divider rounded-xl transition-all" title="View Contents">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                            </a>
                            <a href="{{ route('admin.education.edit', $module) }}" class="p-3 text-text-tertiary hover:text-primary bg-surface-light/50 hover:bg-surface-light border border-divider rounded-xl transition-all" title="Edit Module">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                            </a>
                            <form action="{{ route('admin.education.destroy', $module) }}" method="POST" onsubmit="return confirm('Are you sure? This will fail if contents are linked.')" class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="p-3 text-red-500/40 hover:text-red-500 bg-red-500/5 hover:bg-red-500/10 border border-red-500/10 rounded-xl transition-all" title="Delete Module">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-6 py-12 text-center text-gray-500 italic">No modules found. Create your first one.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($modules->hasPages())
    <div class="px-10 py-6 border-t border-divider bg-surface-light/30">
        <div class="pagination-premium">
            {{ $modules->links() }}
        </div>
    </div>
    @endif
</div>

<style>
    /* Pagination Overrides for Premium Theme */
    .pagination-premium nav { display: flex; justify-content: center; }
    .pagination-premium a, .pagination-premium span { 
        background: #1C2733 !important; 
        border: 1px solid #2A3545 !important; 
        color: #B0BEC5 !important; 
        border-radius: 12px !important;
        margin: 0 4px !important;
        font-weight: 800 !important;
        font-size: 10px !important;
        text-transform: uppercase !important;
        letter-spacing: 0.1em !important;
        transition: all 0.3s !important;
    }
    .pagination-premium a:hover { 
        border-color: #00E5FF !important; 
        color: #00E5FF !important; 
        box-shadow: 0 0 10px rgba(0, 229, 255, 0.2);
    }
    .pagination-premium .active span {
        background: #00E5FF !important;
        border-color: #00E5FF !important;
        color: #0A0F14 !important;
        box-shadow: 0 0 15px rgba(0, 229, 255, 0.3);
    }
</style>
@endsection
