@extends('layouts.admin')

@section('header', 'Edit VR Scene Content')

@section('content')
<div class="mx-auto max-w-6xl space-y-8">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
        <div>
            <p class="text-[10px] font-black uppercase tracking-[0.4em] text-primary/70 italic">VR Content Management</p>
            <h1 class="mt-2 text-3xl font-black text-white tracking-tight">Edit VR Scene Content</h1>
            <p class="mt-2 text-sm text-text-secondary">{{ $content->scene_slug }} / {{ $content->content_key }} / {{ $content->locale }}</p>
        </div>

        <form action="{{ route('admin.vr-scene-contents.destroy', $content) }}" method="POST" onsubmit="return confirm('Archive this VR scene content record?');">
            @csrf
            @method('DELETE')
            <button type="submit" class="rounded-2xl border border-red-500/30 bg-red-500/10 px-5 py-3 text-xs font-black uppercase tracking-[0.2em] text-red-300 hover:bg-red-500 hover:text-white">
                Archive
            </button>
        </form>
    </div>

    <form action="{{ route('admin.vr-scene-contents.update', $content) }}" method="POST" class="space-y-8 rounded-3xl border border-divider bg-surface p-6 shadow-premium">
        @csrf
        @method('PUT')
        @include('admin.vr-scene-contents._form', ['content' => $content])
    </form>
</div>
@endsection
