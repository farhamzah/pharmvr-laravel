@extends('layouts.admin')

@section('header', 'Create VR Scene Content')

@section('content')
<div class="mx-auto max-w-6xl space-y-8">
    <div>
        <p class="text-[10px] font-black uppercase tracking-[0.4em] text-primary/70 italic">VR Content Management</p>
        <h1 class="mt-2 text-3xl font-black text-white tracking-tight">Create VR Scene Content</h1>
        <p class="mt-2 text-sm text-text-secondary">Create dynamic CMS content for WebXR educational panels.</p>
    </div>

    <form action="{{ route('admin.vr-scene-contents.store') }}" method="POST" class="space-y-8 rounded-3xl border border-divider bg-surface p-6 shadow-premium">
        @csrf
        @include('admin.vr-scene-contents._form', ['content' => $content])
    </form>
</div>
@endsection
