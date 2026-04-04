@extends('layouts.admin')

@section('header', 'Content Engine')

@section('content')
<div class="max-w-[1440px] mx-auto px-4 sm:px-6 lg:px-8">
    <div class="mb-10 flex flex-col md:flex-row md:items-end justify-between gap-6 border-b border-divider/30 pb-10">
        <div class="space-y-3">
            <div class="flex items-center gap-3 text-[10px] font-black uppercase tracking-[0.4em] text-text-tertiary/60 italic">
                <a href="{{ route('admin.news.index') }}" class="hover:text-primary transition-all">Archive Feed</a>
                <span class="text-primary/30">/</span>
                <span class="text-white">Edit Article</span>
            </div>
            <h1 class="text-4xl font-black text-white tracking-tighter font-display uppercase italic leading-none">Edit News Article</h1>
            <div class="flex items-center gap-4">
                <p class="text-xs font-bold text-text-tertiary uppercase tracking-widest opacity-40">Article Slug: {{ $news->slug }}</p>
                <div class="w-1 h-1 rounded-full bg-divider"></div>
                <span class="text-[10px] font-black {{ $news->is_active ? 'text-primary' : 'text-text-tertiary' }} uppercase tracking-[0.3em] opacity-60">Status: {{ $news->is_active ? 'Published' : 'Draft' }}</span>
            </div>
        </div>
        
        <div class="flex items-center gap-2 bg-divider/10 p-1.5 rounded-2xl border border-divider/30">
            <div class="px-5 py-2.5 rounded-xl bg-surface-light/50 border border-divider shadow-inner">
                <span class="text-[10px] font-black text-primary uppercase tracking-[0.3em] inline-flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-primary animate-pulse"></span>
                    Editor Active
                </span>
            </div>
        </div>
    </div>

    <form action="{{ route('admin.news.update', $news) }}" method="POST" enctype="multipart/form-data" x-ref="mainForm">
        @csrf
        @method('PUT')
        @include('admin.news.partials._form')
    </form>
</div>
@endsection

