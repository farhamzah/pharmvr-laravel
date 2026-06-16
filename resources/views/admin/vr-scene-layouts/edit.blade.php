@extends('layouts.admin')

@section('header', 'Edit VR Scene Layout')

@section('content')
<div class="mx-auto max-w-6xl space-y-8">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
        <div>
            <p class="text-[10px] font-black uppercase tracking-[0.4em] text-primary/70 italic">VR Content Management</p>
            <h1 class="mt-2 text-3xl font-black text-white tracking-tight">Edit VR Scene Layout</h1>
            <p class="mt-2 text-sm text-text-secondary">{{ $layout->scene_slug }} / {{ $layout->template_key ?? 'no template' }} / v{{ $layout->version }}</p>
        </div>

        <div class="flex flex-wrap gap-3">
            <form action="{{ route('admin.vr-scene-layouts.publish', $layout) }}" method="POST" onsubmit="return confirm('Publishing this layout will archive the current published layout for this scene. Continue?');">
                @csrf
                <button type="submit" class="rounded-2xl bg-emerald-500 px-5 py-3 text-xs font-black uppercase tracking-[0.2em] text-white hover:bg-emerald-400">
                    Publish
                </button>
            </form>
            <form action="{{ route('admin.vr-scene-layouts.archive', $layout) }}" method="POST" onsubmit="return confirm('Archive this VR scene layout? It will no longer be returned by the public published layout endpoint.');">
                @csrf
                <button type="submit" class="rounded-2xl border border-red-500/30 bg-red-500/10 px-5 py-3 text-xs font-black uppercase tracking-[0.2em] text-red-300 hover:bg-red-500 hover:text-white">
                    Archive
                </button>
            </form>
            <form action="{{ route('admin.vr-scene-layouts.duplicate', $layout) }}" method="POST">
                @csrf
                <button type="submit" class="rounded-2xl border border-primary/30 px-5 py-3 text-xs font-black uppercase tracking-[0.2em] text-primary hover:bg-primary hover:text-background">
                    Duplicate Draft
                </button>
            </form>
        </div>
    </div>

    <form action="{{ route('admin.vr-scene-layouts.update', $layout) }}" method="POST" class="space-y-8 rounded-3xl border border-divider bg-surface p-6 shadow-premium">
        @csrf
        @method('PUT')
        @include('admin.vr-scene-layouts._form', ['layout' => $layout])
    </form>
</div>
@endsection
