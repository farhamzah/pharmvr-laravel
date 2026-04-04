@extends('layouts.admin')

@section('header', 'News Intelligence Preview')

@section('content')
<div class="max-w-5xl mx-auto space-y-10 animate-in fade-in duration-700">
    <!-- Action Header -->
    <div class="flex justify-between items-center bg-surface/50 backdrop-blur-md p-6 rounded-3xl border border-divider shadow-premium">
        <div class="flex items-center gap-4">
            <a href="{{ route('admin.news.edit', $news->slug) }}" class="p-3 bg-surface-light/50 rounded-2xl border border-divider hover:border-primary/50 text-text-tertiary hover:text-primary transition-all group">
                <svg class="w-5 h-5 group-hover:-translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"></path></svg>
            </a>
            <div>
                <h3 class="text-[10px] font-black text-primary uppercase tracking-[0.4em] italic font-display">Preview Mode</h3>
                <p class="text-xs font-bold text-text-tertiary">Reviewing: {{ $news->title }}</p>
            </div>
        </div>
        <div class="flex gap-3">
            <span class="px-4 py-2 rounded-xl bg-primary/10 border border-primary/20 text-[10px] font-black text-primary uppercase tracking-widest">
                {{ $news->is_active ? 'Status: Live' : 'Status: Draft' }}
            </span>
            <a href="{{ route('admin.news.edit', $news->slug) }}" class="px-6 py-2.5 bg-primary text-background text-[10px] font-black uppercase tracking-widest rounded-xl hover:scale-105 transition-all shadow-lg shadow-primary/20">
                Return to Editor
            </a>
        </div>
    </div>

    <!-- Content Card -->
    <div class="bg-surface rounded-[40px] border border-divider overflow-hidden shadow-2xl">
        <!-- Headline Image -->
        @if($news->image_url)
        <div class="aspect-[21/9] w-full relative overflow-hidden group">
            <img src="{{ asset($news->image_url) }}" alt="Headline" class="w-full h-full object-cover transition-transform duration-1000 group-hover:scale-105">
            <div class="absolute inset-0 bg-gradient-to-t from-background via-transparent to-transparent opacity-60"></div>
            
            <!-- Category Badge -->
            <div class="absolute bottom-8 left-8">
                <span class="px-6 py-2 rounded-full bg-primary/90 backdrop-blur-md text-[11px] font-black text-background uppercase tracking-[0.2em] shadow-xl">
                    {{ $news->category }}
                </span>
            </div>
        </div>
        @endif

        <div class="p-10 md:p-16 space-y-10">
            <!-- Article Header -->
            <div class="space-y-6">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-1 bg-primary/40 rounded-full"></div>
                    <time class="text-xs font-black text-text-tertiary/60 uppercase tracking-[0.3em]">
                        {{ $news->published_at ? $news->published_at->format('F d, Y / H:i') : 'Unpublished' }}
                    </time>
                </div>
                <h1 class="text-4xl md:text-6xl font-black text-white leading-[1.1] tracking-tight font-sans">
                    {{ $news->title }}
                </h1>
            </div>

            <!-- Full Content -->
            <div class="prose prose-invert prose-2xl max-w-none font-sans leading-[1.8] text-white/90 space-y-8">
                {!! nl2br(e($news->content)) !!}
            </div>

            <!-- Footer Stats -->
            <div class="pt-10 border-t border-divider/50 flex flex-wrap gap-8 items-center text-text-tertiary/40">
                <div class="flex items-center gap-3">
                    <span class="text-[10px] font-black uppercase tracking-widest">Article ID:</span>
                    <span class="text-xs font-mono font-bold text-text-tertiary">{{ strtoupper(substr($news->slug, 0, 8)) }}</span>
                </div>
                <div class="w-2 h-2 rounded-full bg-divider/30"></div>
                <div class="flex items-center gap-3">
                    <span class="text-[10px] font-black uppercase tracking-widest">Broadcast Layer:</span>
                    <span class="text-xs font-bold text-primary italic">Global Intelligence</span>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
