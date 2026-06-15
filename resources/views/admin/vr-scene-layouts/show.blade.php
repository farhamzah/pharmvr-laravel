@extends('layouts.admin')

@section('header', 'VR Scene Layout Detail')

@section('content')
<div class="mx-auto max-w-6xl space-y-8">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
        <div>
            <p class="text-[10px] font-black uppercase tracking-[0.4em] text-primary/70 italic">VR Content Management</p>
            <h1 class="mt-2 text-3xl font-black text-white tracking-tight">{{ $layout->title ?? $layout->scene_slug }}</h1>
            <p class="mt-2 text-sm text-text-secondary">{{ $layout->scene_slug }} / {{ $layout->template_key ?? 'no template' }} / v{{ $layout->version }}</p>
        </div>

        <div class="flex flex-wrap gap-3">
            <a href="{{ route('admin.vr-scene-layouts.edit', $layout) }}" class="rounded-2xl bg-primary px-5 py-3 text-xs font-black uppercase tracking-[0.2em] text-background">Edit</a>
            <a href="{{ url('/api/v1/vr/scenes/'.$layout->scene_slug.'/layout') }}" target="_blank" class="rounded-2xl border border-emerald-400/30 px-5 py-3 text-xs font-black uppercase tracking-[0.2em] text-emerald-300 hover:bg-emerald-500 hover:text-white">View Public API</a>
        </div>
    </div>

    <div class="grid gap-4 md:grid-cols-4">
        <div class="rounded-3xl border border-divider bg-surface p-5">
            <p class="text-[9px] font-black uppercase tracking-[0.3em] text-text-tertiary">Status</p>
            <p class="mt-2 text-xl font-black uppercase text-white">{{ $layout->status }}</p>
        </div>
        <div class="rounded-3xl border border-divider bg-surface p-5">
            <p class="text-[9px] font-black uppercase tracking-[0.3em] text-text-tertiary">Published At</p>
            <p class="mt-2 text-sm font-bold text-white">{{ $layout->published_at?->format('Y-m-d H:i') ?? '-' }}</p>
        </div>
        <div class="rounded-3xl border border-divider bg-surface p-5">
            <p class="text-[9px] font-black uppercase tracking-[0.3em] text-text-tertiary">Updated At</p>
            <p class="mt-2 text-sm font-bold text-white">{{ $layout->updated_at?->format('Y-m-d H:i') ?? '-' }}</p>
        </div>
        <div class="rounded-3xl border border-divider bg-surface p-5">
            <p class="text-[9px] font-black uppercase tracking-[0.3em] text-text-tertiary">Components</p>
            <p class="mt-2 text-xl font-black text-white">{{ count($layout->layout_json['components'] ?? []) }}</p>
        </div>
    </div>

    <div class="rounded-3xl border border-divider bg-surface p-6 shadow-premium">
        <h2 class="text-lg font-black text-white">Layout JSON</h2>
        <pre class="mt-4 overflow-x-auto rounded-2xl border border-divider bg-background p-5 text-xs leading-6 text-text-secondary">{{ json_encode($layout->layout_json ?? [], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) }}</pre>
    </div>
</div>
@endsection
