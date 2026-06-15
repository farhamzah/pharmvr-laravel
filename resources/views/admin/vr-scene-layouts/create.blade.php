@extends('layouts.admin')

@section('header', 'Create VR Scene Layout')

@section('content')
<div class="mx-auto max-w-6xl space-y-8">
    <div>
        <p class="text-[10px] font-black uppercase tracking-[0.4em] text-primary/70 italic">VR Content Management</p>
        <h1 class="mt-2 text-3xl font-black text-white tracking-tight">Create VR Scene Layout</h1>
        <p class="mt-2 text-sm text-text-secondary">Create a JSON-first layout draft for future WebXR scene composition.</p>
    </div>

    <form action="{{ route('admin.vr-scene-layouts.store') }}" method="POST" class="space-y-8 rounded-3xl border border-divider bg-surface p-6 shadow-premium">
        @csrf
        @include('admin.vr-scene-layouts._form', ['layout' => $layout])
    </form>
</div>
@endsection
