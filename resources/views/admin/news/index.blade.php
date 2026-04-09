@extends('layouts.admin')

@section('header')
<div class="flex flex-col gap-1">
    <div class="flex items-center gap-2 text-[10px] font-black uppercase tracking-[0.3em] text-text-tertiary opacity-60 italic">
        <a href="{{ route('admin.dashboard') }}" class="hover:text-primary transition-colors font-sans">Dashboard</a>
        <span class="text-primary/30">/</span>
        <span class="text-white font-sans uppercase">Global News</span>
    </div>
    <h1 class="text-3xl font-black text-white tracking-tight font-display uppercase italic">Intelligence Hub</h1>
</div>
@endsection

@section('content')
<div class="space-y-6 max-w-[1500px] mx-auto pb-10">
    <!-- Compact Management Toolbar -->
    <div class="bg-surface rounded-3xl border border-divider shadow-premium p-4 md:p-5 backdrop-blur-md">
        <form action="{{ route('admin.news.index') }}" method="GET" class="flex flex-col lg:flex-row gap-4 lg:items-center">
            <!-- Search Control (Primary) -->
            <div class="flex-1 min-w-[300px] relative group">
                <input type="text" name="search" value="{{ request('search') }}" 
                    placeholder="Search articles by title or keyword..." 
                    class="w-full bg-background/50 border border-divider rounded-2xl px-5 py-3 text-sm text-white focus:ring-1 focus:ring-primary/40 focus:border-primary/40 transition-all placeholder:text-text-tertiary/20 font-sans">
                <svg class="absolute right-4 top-1/2 -translate-y-1/2 w-4 h-4 text-text-tertiary opacity-40 group-focus-within:text-primary transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
            </div>

            <!-- Horizontal Filters Group -->
            <div class="flex flex-wrap items-center gap-3">
                <!-- Status Filter -->
                <div class="min-w-[140px]">
                    <select name="status" class="w-full bg-background/50 border border-divider rounded-2xl px-4 py-3 text-xs text-text-secondary focus:ring-1 focus:ring-primary/40 transition-all font-sans appearance-none cursor-pointer hover:border-divider-light">
                        <option value="">All Statuses</option>
                        <option value="published" {{ request('status') === 'published' ? 'selected' : '' }}>Published</option>
                        <option value="draft" {{ request('status') === 'draft' ? 'selected' : '' }}>Draft</option>
                        <option value="scheduled" {{ request('status') === 'scheduled' ? 'selected' : '' }}>Scheduled</option>
                    </select>
                </div>

                <!-- Category Filter -->
                <div class="min-w-[160px]">
                    <select name="category" class="w-full bg-background/50 border border-divider rounded-2xl px-4 py-3 text-xs text-text-secondary focus:ring-1 focus:ring-primary/40 transition-all font-sans appearance-none cursor-pointer hover:border-divider-light">
                        <option value="">All Categories</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat }}" {{ request('category') === $cat ? 'selected' : '' }}>{{ $cat }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Sort Control -->
                <div class="min-w-[140px]">
                    <select name="sort" class="w-full bg-background/50 border border-divider rounded-2xl px-4 py-3 text-xs text-text-secondary focus:ring-1 focus:ring-primary/40 transition-all font-sans appearance-none cursor-pointer hover:border-divider-light">
                        <option value="newest" {{ request('sort') === 'newest' ? 'selected' : '' }}>Newest First</option>
                        <option value="oldest" {{ request('sort') === 'oldest' ? 'selected' : '' }}>Oldest First</option>
                        <option value="updated" {{ request('sort') === 'updated' ? 'selected' : '' }}>Last Updated</option>
                        <option value="published" {{ request('sort') === 'published' ? 'selected' : '' }}>Published Date</option>
                    </select>
                </div>

                <!-- Toolbar Actions -->
                <div class="flex items-center gap-2 border-l border-divider/50 pl-3">
                    <button type="submit" class="bg-surface-light border border-divider text-white px-5 py-3 rounded-2xl text-[10px] font-black uppercase tracking-widest hover:bg-divider transition-all active:scale-95 font-sans">
                        Filter
                    </button>
                    @if(request()->anyFilled(['search', 'status', 'category', 'sort']))
                        <a href="{{ route('admin.news.index') }}" class="p-3 bg-red-500/10 text-red-500 hover:bg-red-500 hover:text-white rounded-2xl border border-red-500/20 transition-all" title="Clear All Filters">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"></path></svg>
                        </a>
                    @endif
                </div>
            </div>

            <!-- Global Action -->
            <div class="lg:ml-auto">
                <a href="{{ route('admin.news.create') }}" class="bg-primary hover:bg-primary-dark text-background px-8 py-3 rounded-2xl text-[10px] font-black uppercase tracking-[0.2em] transition-all shadow-cyan-glow flex items-center gap-3 active:scale-95 whitespace-nowrap">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M12 4v16m8-8H4"></path></svg>
                    Write Article
                </a>
            </div>
        </form>
    </div>

    <!-- Article Index Table Card -->
    <div class="bg-surface rounded-[40px] border border-divider shadow-premium overflow-hidden backdrop-blur-sm">
        <div class="p-8 border-b border-divider/50 flex items-center justify-between bg-surface-light/20">
            <div class="flex items-center gap-4">
                <div class="w-2 h-8 bg-primary/40 rounded-full"></div>
                <div>
                    <h3 class="font-black text-white text-xl tracking-tight uppercase italic font-display">Intelligence Streams</h3>
                    <p class="text-[10px] font-bold text-text-tertiary uppercase tracking-[0.2em] opacity-40 font-sans mt-0.5">Managing drafs, scheduled broadcasts, and live intelligence</p>
                </div>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead class="bg-surface-light/40 text-[10px] font-black text-text-tertiary uppercase tracking-[0.4em] font-sans">
                    <tr>
                        <th class="px-10 py-5 border-b border-divider/50">Article Concept & Identity</th>
                        <th class="px-8 py-5 border-b border-divider/50 text-center">Current Status</th>
                        <th class="px-8 py-5 border-b border-divider/50">Classification</th>
                        <th class="px-8 py-5 border-b border-divider/50">Broadcast Date</th>
                        <th class="px-10 py-5 border-b border-divider/50 text-right w-[200px]">Operations</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-divider/20">
                    @forelse($news as $article)
                    <tr class="hover:bg-primary/[0.03] transition-colors duration-500 group border-b border-divider/10">
                        <td class="px-10 py-5">
                            <div class="flex items-center gap-8">
                                <div class="w-20 h-20 flex-shrink-0 rounded-[2rem] bg-surface-light border border-divider flex items-center justify-center text-text-tertiary overflow-hidden transition-all shadow-premium group-hover:scale-105 duration-700">
                                    @if($article->image_url)
                                        <div class="w-full h-full relative">
                                            <img src="{{ $article->image_full_url }}" 
                                                alt="{{ $article->title }}" 
                                                class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-1000"
                                                onerror="this.style.display='none'; this.parentElement.innerHTML = '<div class=\'w-full h-full flex items-center justify-center bg-surface\'><svg class=\'w-6 h-6 opacity-10\' fill=\'none\' stroke=\'currentColor\' viewBox=\'0 0 24 24\'><path stroke-linecap=\'round\' stroke-linejoin=\'round\' stroke-width=\'2\' d=\'M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10l4 4v10a2 2 0 01-2 2z\'></path></svg></div>';">
                                            <div class="absolute inset-0 bg-primary/10 opacity-0 group-hover:opacity-100 transition-opacity"></div>
                                        </div>
                                    @else
                                        <svg class="w-8 h-8 opacity-20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10l4 4v10a2 2 0 01-2 2z"></path></svg>
                                    @endif
                                </div>
                                <div class="min-w-0 space-y-1.5">
                                    <h4 class="text-xl font-bold text-white group-hover:text-primary transition-colors tracking-tight font-sans leading-tight line-clamp-2 max-w-[500px]">{{ $article->title }}</h4>
                                    <div class="flex items-center gap-4">
                                        <span class="text-[10px] text-text-tertiary font-bold uppercase tracking-widest opacity-30 font-sans truncate max-w-[200px]">{{ $article->slug }}</span>
                                        <div class="w-1 h-1 bg-divider rounded-full"></div>
                                        <span class="text-[9px] text-text-tertiary font-black uppercase tracking-[0.2em] font-sans opacity-15">ID: {{ strtoupper(substr($article->slug, 0, 8)) }}</span>
                                    </div>
                                </div>
                            </div>
                        </td>
                        <td class="px-8 py-5 text-center">
                            @php
                                $isScheduled = $article->is_active && $article->published_at && $article->published_at->isFuture();
                                $isPublished = $article->is_active && (!$article->published_at || $article->published_at->isPast());
                            @endphp
                            
                            @if($isScheduled)
                                <span class="px-5 py-2 bg-yellow-500/10 text-yellow-500 border border-yellow-500/20 rounded-2xl text-[10px] font-black uppercase tracking-widest flex items-center justify-center gap-2 w-fit mx-auto shadow-lg shadow-yellow-500/5">
                                    <span class="w-1.5 h-1.5 rounded-full bg-yellow-500 animate-pulse"></span>
                                    Scheduled
                                </span>
                            @elseif($isPublished)
                                <span class="px-5 py-2 bg-primary/10 text-primary border border-primary/20 rounded-2xl text-[10px] font-black uppercase tracking-widest shadow-cyan-glow flex items-center justify-center gap-2 w-fit mx-auto">
                                    <span class="w-1.5 h-1.5 rounded-full bg-primary"></span>
                                    Published
                                </span>
                            @else
                                <span class="px-5 py-2 bg-divider/10 text-text-tertiary border border-divider/30 rounded-2xl text-[10px] font-black uppercase tracking-widest opacity-50 flex items-center justify-center gap-2 w-fit mx-auto">
                                    <span class="w-1.5 h-1.5 rounded-full bg-text-tertiary"></span>
                                    Draft
                                </span>
                            @endif
                        </td>
                        <td class="px-8 py-5">
                            <span class="px-4 py-2 bg-surface-light border border-divider rounded-xl text-[10px] font-black text-text-secondary uppercase tracking-widest font-sans">
                                {{ $article->category }}
                            </span>
                        </td>
                        <td class="px-8 py-5 space-y-1">
                            <div class="text-xs font-bold text-white font-sans uppercase tracking-tight">
                                {{ $article->published_at ? $article->published_at->format('d M Y') : 'Pending' }}
                            </div>
                            <div class="text-[10px] text-text-tertiary/60 font-medium font-sans italic opacity-40">
                                {{ $article->published_at ? $article->published_at->format('H:i') . ' UTC' : 'Transmission Idle' }}
                            </div>
                        </td>
                        <td class="px-10 py-5 text-right">
                            <div class="flex items-center justify-end gap-2.5 opacity-40 group-hover:opacity-100 transition-all duration-300">
                                <a href="{{ route('admin.news.show', $article->slug) }}" target="_blank" class="p-3.5 bg-surface-light border border-divider text-text-tertiary hover:text-primary hover:border-primary/50 rounded-2xl transition-all shadow-sm active:scale-90" title="Public Preview">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                </a>
                                <a href="{{ route('admin.news.edit', $article) }}" class="p-3.5 bg-surface-light border border-divider text-text-tertiary hover:text-white hover:border-white rounded-2xl transition-all shadow-sm active:scale-90" title="Edit Content">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                                </a>
                                <form action="{{ route('admin.news.destroy', $article) }}" method="POST" onsubmit="return confirm('Confirm permanent deletion of this intelligence stream? This action cannot be undone.')" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="p-3.5 bg-surface-light border border-divider text-text-tertiary hover:text-red-500 hover:border-red-500 rounded-2xl transition-all shadow-sm active:scale-90" title="Permanently Delete">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-8 py-32 text-center space-y-6">
                            <div class="flex justify-center flex-col items-center gap-6">
                                <div class="w-20 h-20 rounded-full bg-divider/10 border border-divider/20 flex items-center justify-center opacity-40">
                                    <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10l4 4v10a2 2 0 01-2 2z"></path></svg>
                                </div>
                                <div class="space-y-1">
                                    <h4 class="text-text-tertiary font-black uppercase tracking-[0.4em] italic opacity-30">No Intelligence Streams Detected</h4>
                                    <p class="text-[10px] text-text-tertiary font-bold uppercase tracking-widest opacity-20 font-sans">No data matching your current synchronization criteria was found.</p>
                                </div>
                                @if(request()->anyFilled(['search', 'status', 'category']))
                                    <a href="{{ route('admin.news.index') }}" class="px-6 py-2.5 bg-divider/20 text-white text-[10px] font-black uppercase tracking-widest rounded-xl hover:bg-primary hover:text-background transition-all">Clear All Sync Filters</a>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($news->hasPages())
        <div class="px-10 py-8 border-t border-divider bg-surface-light/10 pagination-premium">
            {{ $news->links() }}
        </div>
        @endif
    </div>
</div>
@endsection

