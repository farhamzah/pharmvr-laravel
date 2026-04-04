@extends('layouts.admin')

@section('header')
<div class="flex flex-col gap-1">
    <div class="flex items-center gap-2 text-[10px] font-black uppercase tracking-[0.3em] text-text-tertiary opacity-60 italic">
        <a href="{{ route('admin.dashboard') }}" class="hover:text-primary transition-colors font-sans">Dashboard</a>
        <span class="text-primary/30">/</span>
        <span class="text-white font-sans uppercase">External News</span>
    </div>
    <h1 class="text-3xl font-black text-white tracking-tight font-display uppercase italic">External Sources</h1>
</div>
@endsection

@section('content')
<div class="space-y-6 max-w-[1500px] mx-auto pb-10">
    <div class="bg-surface rounded-[40px] border border-divider shadow-premium overflow-hidden backdrop-blur-sm">
        <div class="p-8 border-b border-divider/50 flex items-center justify-between bg-surface-light/20">
            <div class="flex items-center gap-4">
                <div class="w-2 h-8 bg-primary/40 rounded-full"></div>
                <div>
                    <h3 class="font-black text-white text-xl tracking-tight uppercase italic font-display">News Sources</h3>
                    <p class="text-[10px] font-bold text-text-tertiary uppercase tracking-[0.2em] opacity-40 font-sans mt-0.5">Manage automated external intelligence streams</p>
                </div>
            </div>
            
            <form action="{{ route('admin.news-sources.sync-all') }}" method="POST">
                @csrf
                <button type="submit" class="bg-primary hover:bg-primary-dark text-background px-6 py-3 rounded-2xl text-[10px] font-black uppercase tracking-[0.2em] transition-all shadow-cyan-glow flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                    Sync All Now
                </button>
            </form>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead class="bg-surface-light/40 text-[10px] font-black text-text-tertiary uppercase tracking-[0.4em] font-sans">
                    <tr>
                        <th class="px-10 py-5 border-b border-divider/50">Source Identity</th>
                        <th class="px-8 py-5 border-b border-divider/50 text-center">Status</th>
                        <th class="px-8 py-5 border-b border-divider/50 text-center">Articles</th>
                        <th class="px-8 py-5 border-b border-divider/50">Last Sync</th>
                        <th class="px-10 py-5 border-b border-divider/50 text-right">Operations</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-divider/20">
                    @forelse($sources as $source)
                    <tr class="hover:bg-primary/[0.03] transition-colors duration-500 border-b border-divider/10">
                        <td class="px-10 py-5">
                            <div class="flex flex-col space-y-1">
                                <span class="text-white font-bold">{{ $source->name }}</span>
                                <span class="text-xs text-text-tertiary opacity-50">{{ $source->feed_url }}</span>
                            </div>
                        </td>
                        <td class="px-8 py-5 text-center">
                            @if($source->is_active)
                                <span class="px-3 py-1 bg-primary/20 text-primary border border-primary/30 rounded-full text-xs font-bold uppercase">Active</span>
                            @else
                                <span class="px-3 py-1 bg-red-500/20 text-red-500 border border-red-500/30 rounded-full text-xs font-bold uppercase">Inactive</span>
                            @endif
                        </td>
                        <td class="px-8 py-5 text-center text-white">{{ $source->articles_count }}</td>
                        <td class="px-8 py-5">
                            <div class="text-xs text-white">{{ $source->last_synced_at ? $source->last_synced_at->diffForHumans() : 'Never' }}</div>
                            <div class="text-[10px] text-text-tertiary">Status: {{ $source->last_sync_status ?? '-' }}</div>
                        </td>
                        <td class="px-10 py-5 text-right">
                            <div class="flex items-center justify-end gap-3">
                                <form action="{{ route('admin.news-sources.toggle', $source) }}" method="POST">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="px-3 py-1.5 border border-divider text-white text-xs rounded-xl hover:bg-surface-light transition-colors">
                                        Toggle Focus
                                    </button>
                                </form>
                                <form action="{{ route('admin.news-sources.sync', $source) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="p-2 border border-primary/40 text-primary rounded-xl hover:bg-primary/20 transition-colors" title="Force Sync">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-8 py-10 text-center text-text-tertiary">No sources defined yet. Perform DB Seed.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
