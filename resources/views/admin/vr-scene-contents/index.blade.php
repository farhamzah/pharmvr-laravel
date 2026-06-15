@extends('layouts.admin')

@section('header', 'VR Scene Contents')

@section('content')
<div class="space-y-8">
    <div class="flex flex-col gap-5 xl:flex-row xl:items-end xl:justify-between">
        <div>
            <p class="text-[10px] font-black uppercase tracking-[0.4em] text-primary/70 italic">VR Content Management</p>
            <h1 class="mt-2 text-3xl font-black text-white tracking-tight">VR Scene Contents</h1>
            <p class="mt-2 text-sm text-text-secondary max-w-3xl">Manage dynamic educational copy for WebXR scenes, including the GMP Standard Room 12 CPOB aspects panel.</p>
        </div>

        <a href="{{ route('admin.vr-scene-contents.create') }}" class="inline-flex items-center justify-center rounded-2xl bg-primary px-6 py-3 text-xs font-black uppercase tracking-[0.2em] text-background hover:scale-[1.02] transition-all shadow-cyan-glow">
            New Content
        </a>
    </div>

    <form method="GET" class="grid gap-4 rounded-3xl border border-divider bg-surface p-5 lg:grid-cols-6">
        <div class="lg:col-span-2">
            <label class="mb-2 block text-[9px] font-black uppercase tracking-[0.3em] text-text-tertiary">Search</label>
            <input type="text" name="search" value="{{ $filters['search'] ?? '' }}" placeholder="scene, key, title" class="w-full rounded-2xl border border-divider bg-background px-4 py-3 text-sm font-bold text-white outline-none focus:border-primary">
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
            <label class="mb-2 block text-[9px] font-black uppercase tracking-[0.3em] text-text-tertiary">Locale</label>
            <select name="locale" class="w-full rounded-2xl border border-divider bg-background px-4 py-3 text-sm font-bold text-white outline-none focus:border-primary">
                <option value="">All locales</option>
                @foreach ($locales as $locale)
                    <option value="{{ $locale }}" @selected(($filters['locale'] ?? '') === $locale)>{{ $locale }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="mb-2 block text-[9px] font-black uppercase tracking-[0.3em] text-text-tertiary">Status</label>
            <select name="status" class="w-full rounded-2xl border border-divider bg-background px-4 py-3 text-sm font-bold text-white outline-none focus:border-primary">
                <option value="">All status</option>
                @foreach (\App\Models\VrSceneContent::STATUSES as $status)
                    <option value="{{ $status }}" @selected(($filters['status'] ?? '') === $status)>{{ strtoupper($status) }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="mb-2 block text-[9px] font-black uppercase tracking-[0.3em] text-text-tertiary">Active</label>
            <select name="is_active" class="w-full rounded-2xl border border-divider bg-background px-4 py-3 text-sm font-bold text-white outline-none focus:border-primary">
                <option value="">All</option>
                <option value="1" @selected(($filters['is_active'] ?? '') === '1')>Active</option>
                <option value="0" @selected(($filters['is_active'] ?? '') === '0')>Inactive</option>
            </select>
        </div>

        <div class="flex items-end gap-3 lg:col-span-6">
            <button type="submit" class="rounded-2xl bg-primary px-5 py-3 text-xs font-black uppercase tracking-[0.2em] text-background">Filter</button>
            <a href="{{ route('admin.vr-scene-contents.index') }}" class="rounded-2xl border border-divider px-5 py-3 text-xs font-black uppercase tracking-[0.2em] text-text-secondary hover:text-white">Reset</a>
        </div>
    </form>

    <div class="overflow-hidden rounded-3xl border border-divider bg-surface shadow-premium">
        <div class="overflow-x-auto">
            <table class="w-full min-w-[1000px] text-left">
                <thead class="border-b border-divider bg-surface-light/30 text-[10px] font-black uppercase tracking-[0.3em] text-text-tertiary">
                    <tr>
                        <th class="px-6 py-5">Scene</th>
                        <th class="px-6 py-5">Key</th>
                        <th class="px-6 py-5">Title</th>
                        <th class="px-6 py-5">Locale</th>
                        <th class="px-6 py-5">Status</th>
                        <th class="px-6 py-5">Active</th>
                        <th class="px-6 py-5">Version</th>
                        <th class="px-6 py-5 text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-divider">
                    @forelse ($contents as $content)
                        <tr class="hover:bg-surface-light/20 transition-colors">
                            <td class="px-6 py-5 text-sm font-black text-white">{{ $content->scene_slug }}</td>
                            <td class="px-6 py-5 text-sm font-bold text-primary">{{ $content->content_key }}</td>
                            <td class="px-6 py-5">
                                <div class="text-sm font-bold text-white">{{ $content->title }}</div>
                                <div class="mt-1 text-xs text-text-tertiary">{{ $content->content_type }}</div>
                            </td>
                            <td class="px-6 py-5 text-sm font-bold uppercase text-text-secondary">{{ $content->locale }}</td>
                            <td class="px-6 py-5 text-xs font-black uppercase tracking-widest text-text-secondary">{{ $content->status }}</td>
                            <td class="px-6 py-5">
                                <span class="rounded-full px-3 py-1 text-[10px] font-black uppercase tracking-widest {{ $content->is_active ? 'bg-emerald-500/10 text-emerald-300' : 'bg-red-500/10 text-red-300' }}">
                                    {{ $content->is_active ? 'Yes' : 'No' }}
                                </span>
                            </td>
                            <td class="px-6 py-5 text-sm font-bold text-text-secondary">v{{ $content->version }}</td>
                            <td class="px-6 py-5 text-right">
                                <a href="{{ route('admin.vr-scene-contents.edit', $content) }}" class="rounded-xl border border-primary/30 px-4 py-2 text-[10px] font-black uppercase tracking-widest text-primary hover:bg-primary hover:text-background">Edit</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-6 py-12 text-center text-sm font-bold text-text-tertiary">No VR scene content records found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{ $contents->links() }}
</div>
@endsection
