@extends('layouts.admin')

@section('header', 'VR Scene Layouts')

@section('content')
<div class="space-y-8">
    <div class="flex flex-col gap-5 xl:flex-row xl:items-end xl:justify-between">
        <div>
            <p class="text-[10px] font-black uppercase tracking-[0.4em] text-primary/70 italic">VR Content Management</p>
            <h1 class="mt-2 text-3xl font-black text-white tracking-tight">VR Scene Layouts</h1>
            <p class="mt-2 text-sm text-text-secondary max-w-3xl">Manage JSON-first scene layout drafts and published composer contracts for future WebXR rendering.</p>
        </div>

        <a href="{{ route('admin.vr-scene-layouts.create') }}" class="inline-flex items-center justify-center rounded-2xl bg-primary px-6 py-3 text-xs font-black uppercase tracking-[0.2em] text-background hover:scale-[1.02] transition-all shadow-cyan-glow">
            New Layout
        </a>
    </div>

    <form method="GET" class="grid gap-4 rounded-3xl border border-divider bg-surface p-5 lg:grid-cols-5">
        <div class="lg:col-span-2">
            <label class="mb-2 block text-[9px] font-black uppercase tracking-[0.3em] text-text-tertiary">Search</label>
            <input type="text" name="search" value="{{ $filters['search'] ?? '' }}" placeholder="scene, title, template" class="w-full rounded-2xl border border-divider bg-background px-4 py-3 text-sm font-bold text-white outline-none focus:border-primary">
        </div>

        <div>
            <label class="mb-2 block text-[9px] font-black uppercase tracking-[0.3em] text-text-tertiary">Scene</label>
            <select name="scene_slug" class="w-full rounded-2xl border border-divider bg-background px-4 py-3 text-sm font-bold text-white outline-none focus:border-primary">
                <option value="">All scenes</option>
                @foreach ($sceneSlugs as $sceneSlug)
                    <option value="{{ $sceneSlug }}" @selected(($filters['scene_slug'] ?? '') === $sceneSlug)>{{ $sceneSlug }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="mb-2 block text-[9px] font-black uppercase tracking-[0.3em] text-text-tertiary">Template</label>
            <select name="template_key" class="w-full rounded-2xl border border-divider bg-background px-4 py-3 text-sm font-bold text-white outline-none focus:border-primary">
                <option value="">All templates</option>
                @foreach ($templateKeys as $templateKey)
                    <option value="{{ $templateKey }}" @selected(($filters['template_key'] ?? '') === $templateKey)>{{ $templateKey }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="mb-2 block text-[9px] font-black uppercase tracking-[0.3em] text-text-tertiary">Status</label>
            <select name="status" class="w-full rounded-2xl border border-divider bg-background px-4 py-3 text-sm font-bold text-white outline-none focus:border-primary">
                <option value="">All status</option>
                @foreach (\App\Models\VrSceneLayout::STATUSES as $status)
                    <option value="{{ $status }}" @selected(($filters['status'] ?? '') === $status)>{{ strtoupper($status) }}</option>
                @endforeach
            </select>
        </div>

        <div class="flex items-end gap-3 lg:col-span-5">
            <button type="submit" class="rounded-2xl bg-primary px-5 py-3 text-xs font-black uppercase tracking-[0.2em] text-background">Filter</button>
            <a href="{{ route('admin.vr-scene-layouts.index') }}" class="rounded-2xl border border-divider px-5 py-3 text-xs font-black uppercase tracking-[0.2em] text-text-secondary hover:text-white">Reset</a>
        </div>
    </form>

    <div class="overflow-hidden rounded-3xl border border-divider bg-surface shadow-premium">
        <div class="overflow-x-auto">
            <table class="w-full min-w-[1200px] text-left">
                <thead class="border-b border-divider bg-surface-light/30 text-[10px] font-black uppercase tracking-[0.3em] text-text-tertiary">
                    <tr>
                        <th class="px-6 py-5">ID</th>
                        <th class="px-6 py-5">Scene Slug</th>
                        <th class="px-6 py-5">Title</th>
                        <th class="px-6 py-5">Template Key</th>
                        <th class="px-6 py-5">Version</th>
                        <th class="px-6 py-5">Status</th>
                        <th class="px-6 py-5">Published At</th>
                        <th class="px-6 py-5">Updated At</th>
                        <th class="px-6 py-5 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-divider">
                    @forelse ($layouts as $layout)
                        <tr class="hover:bg-surface-light/20 transition-colors">
                            <td class="px-6 py-5 text-sm font-black text-text-secondary">#{{ $layout->id }}</td>
                            <td class="px-6 py-5 text-sm font-black text-white">{{ $layout->scene_slug }}</td>
                            <td class="px-6 py-5">
                                <div class="text-sm font-bold text-white">{{ $layout->title ?? '-' }}</div>
                                <div class="mt-1 text-xs text-text-tertiary">{{ count($layout->layout_json['components'] ?? []) }} components</div>
                            </td>
                            <td class="px-6 py-5 text-sm font-bold text-primary">{{ $layout->template_key ?? '-' }}</td>
                            <td class="px-6 py-5 text-sm font-bold text-text-secondary">v{{ $layout->version }}</td>
                            <td class="px-6 py-5">
                                <span class="rounded-full px-3 py-1 text-[10px] font-black uppercase tracking-widest {{ $layout->status === 'published' ? 'bg-emerald-500/10 text-emerald-300' : ($layout->status === 'archived' ? 'bg-red-500/10 text-red-300' : 'bg-amber-500/10 text-amber-300') }}">
                                    {{ $layout->status }}
                                </span>
                            </td>
                            <td class="px-6 py-5 text-xs font-bold text-text-secondary">{{ $layout->published_at?->format('Y-m-d H:i') ?? '-' }}</td>
                            <td class="px-6 py-5 text-xs font-bold text-text-secondary">{{ $layout->updated_at?->format('Y-m-d H:i') ?? '-' }}</td>
                            <td class="px-6 py-5">
                                <div class="flex flex-wrap justify-end gap-2">
                                    <a href="{{ route('admin.vr-scene-layouts.show', $layout) }}" class="rounded-xl border border-divider px-3 py-2 text-[10px] font-black uppercase tracking-widest text-text-secondary hover:text-white">Show</a>
                                    <a href="{{ route('admin.vr-scene-layouts.edit', $layout) }}" class="rounded-xl border border-primary/30 px-3 py-2 text-[10px] font-black uppercase tracking-widest text-primary hover:bg-primary hover:text-background">Edit</a>
                                    <form action="{{ route('admin.vr-scene-layouts.publish', $layout) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="rounded-xl border border-emerald-400/30 px-3 py-2 text-[10px] font-black uppercase tracking-widest text-emerald-300 hover:bg-emerald-500 hover:text-white">Publish</button>
                                    </form>
                                    <form action="{{ route('admin.vr-scene-layouts.archive', $layout) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="rounded-xl border border-red-500/30 px-3 py-2 text-[10px] font-black uppercase tracking-widest text-red-300 hover:bg-red-500 hover:text-white">Archive</button>
                                    </form>
                                    <form action="{{ route('admin.vr-scene-layouts.duplicate', $layout) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="rounded-xl border border-divider px-3 py-2 text-[10px] font-black uppercase tracking-widest text-text-secondary hover:text-white">Duplicate</button>
                                    </form>
                                    <a href="{{ url('/api/v1/vr/scenes/'.$layout->scene_slug.'/layout') }}" target="_blank" class="rounded-xl border border-divider px-3 py-2 text-[10px] font-black uppercase tracking-widest text-text-secondary hover:text-white">API</a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="px-6 py-12 text-center text-sm font-bold text-text-tertiary">No VR scene layout records found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{ $layouts->links() }}
</div>
@endsection
