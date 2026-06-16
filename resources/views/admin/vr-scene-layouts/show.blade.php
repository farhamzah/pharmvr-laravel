@extends('layouts.admin')

@section('header', 'VR Scene Layout Detail')

@section('content')
@php
    $statusClass = match ($layout->status) {
        \App\Models\VrSceneLayout::STATUS_PUBLISHED => 'border-emerald-400/40 bg-emerald-500/10 text-emerald-300',
        \App\Models\VrSceneLayout::STATUS_DRAFT => 'border-amber-400/40 bg-amber-500/10 text-amber-300',
        \App\Models\VrSceneLayout::STATUS_ARCHIVED => 'border-slate-400/30 bg-slate-500/10 text-slate-300',
        default => 'border-divider bg-background text-text-secondary',
    };
    $warnings = $layout->validation_warnings_json ?? [];
    $publicApiUrl = url('/api/v1/vr/scenes/'.$layout->scene_slug.'/layout');
@endphp
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
            <p class="mt-2 inline-flex rounded-full border px-3 py-1 text-xs font-black uppercase tracking-[0.18em] {{ $statusClass }}">{{ $layout->status }}</p>
        </div>
        <div class="rounded-3xl border border-divider bg-surface p-5">
            <p class="text-[9px] font-black uppercase tracking-[0.3em] text-text-tertiary">Published At</p>
            <p class="mt-2 text-sm font-bold text-white">{{ $layout->published_at?->format('Y-m-d H:i') ?? '-' }}</p>
            <p class="mt-1 text-xs text-text-tertiary">By: {{ $layout->publisher?->name ?? '-' }}</p>
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
        <h2 class="text-lg font-black text-white">Public API Preview</h2>
        <a href="{{ $publicApiUrl }}" target="_blank" class="mt-3 block break-all rounded-2xl border border-emerald-400/20 bg-emerald-500/5 p-4 font-mono text-xs text-emerald-300 hover:bg-emerald-500 hover:text-white">
            GET {{ $publicApiUrl }}
        </a>
    </div>

    <div class="rounded-3xl border border-divider bg-surface p-6 shadow-premium">
        <h2 class="text-lg font-black text-white">Validation Warnings</h2>
        @if (!empty($warnings))
            <ul class="mt-4 list-disc space-y-2 pl-5 text-sm text-amber-200">
                @foreach ($warnings as $warning)
                    <li>{{ $warning }}</li>
                @endforeach
            </ul>
        @else
            <p class="mt-3 text-sm text-text-secondary">No validation warnings recorded.</p>
        @endif
    </div>

    <div class="rounded-3xl border border-divider bg-surface p-6 shadow-premium">
        <h2 class="text-lg font-black text-white">Layout JSON</h2>
        <pre class="mt-4 overflow-x-auto rounded-2xl border border-divider bg-background p-5 text-xs leading-6 text-text-secondary">{{ json_encode($layout->layout_json ?? [], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) }}</pre>
    </div>
</div>
@endsection
