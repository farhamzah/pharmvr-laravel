@php
    $layoutJson = old('layout_json', json_encode($layout->layout_json ?? [], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
    $metadataJson = old('metadata_json', json_encode($layout->metadata_json ?? [], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
    $warningsJson = json_encode($layout->validation_warnings_json ?? [], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
@endphp

@if ($errors->any())
    <div class="rounded-2xl border border-red-500/20 bg-red-500/10 p-5 text-sm font-bold text-red-300">
        <p class="mb-3 text-[10px] font-black uppercase tracking-[0.3em]">Validation failed</p>
        <ul class="list-disc space-y-1 pl-5">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="grid gap-6 lg:grid-cols-2">
    <div>
        <label class="mb-2 block text-[9px] font-black uppercase tracking-[0.3em] text-text-tertiary">Scene Slug</label>
        <input type="text" name="scene_slug" value="{{ old('scene_slug', $layout->scene_slug) }}" required class="w-full rounded-2xl border border-divider bg-background px-4 py-3 text-sm font-bold text-white outline-none focus:border-primary">
    </div>

    <div>
        <label class="mb-2 block text-[9px] font-black uppercase tracking-[0.3em] text-text-tertiary">Template Key</label>
        <input type="text" name="template_key" value="{{ old('template_key', $layout->template_key) }}" class="w-full rounded-2xl border border-divider bg-background px-4 py-3 text-sm font-bold text-white outline-none focus:border-primary">
    </div>

    <div class="lg:col-span-2">
        <label class="mb-2 block text-[9px] font-black uppercase tracking-[0.3em] text-text-tertiary">Title</label>
        <input type="text" name="title" value="{{ old('title', $layout->title) }}" class="w-full rounded-2xl border border-divider bg-background px-4 py-3 text-sm font-bold text-white outline-none focus:border-primary">
    </div>

    <div>
        <label class="mb-2 block text-[9px] font-black uppercase tracking-[0.3em] text-text-tertiary">Version</label>
        <input type="number" min="1" name="version" value="{{ old('version', $layout->version ?? 1) }}" required class="w-full rounded-2xl border border-divider bg-background px-4 py-3 text-sm font-bold text-white outline-none focus:border-primary">
    </div>

    <div>
        <label class="mb-2 block text-[9px] font-black uppercase tracking-[0.3em] text-text-tertiary">Status</label>
        <select name="status" required class="w-full rounded-2xl border border-divider bg-background px-4 py-3 text-sm font-bold text-white outline-none focus:border-primary">
            @foreach (\App\Models\VrSceneLayout::STATUSES as $status)
                <option value="{{ $status }}" @selected(old('status', $layout->status) === $status)>{{ strtoupper($status) }}</option>
            @endforeach
        </select>
    </div>

    <div class="lg:col-span-2">
        <label class="mb-2 block text-[9px] font-black uppercase tracking-[0.3em] text-text-tertiary">Layout JSON</label>
        <textarea name="layout_json" rows="26" spellcheck="false" required class="font-mono w-full rounded-2xl border border-divider bg-background px-4 py-3 text-xs leading-6 text-white outline-none focus:border-primary">{{ $layoutJson }}</textarea>
        <p class="mt-2 text-xs text-text-tertiary">Plain JSON only. Component IDs must be unique and component types must match the backend VR scene registry.</p>
    </div>

    <div class="lg:col-span-2">
        <label class="mb-2 block text-[9px] font-black uppercase tracking-[0.3em] text-text-tertiary">Metadata JSON</label>
        <textarea name="metadata_json" rows="8" spellcheck="false" class="font-mono w-full rounded-2xl border border-divider bg-background px-4 py-3 text-xs leading-6 text-white outline-none focus:border-primary">{{ $metadataJson }}</textarea>
    </div>

    <div class="lg:col-span-2">
        <label class="mb-2 block text-[9px] font-black uppercase tracking-[0.3em] text-text-tertiary">Validation Warnings</label>
        <textarea rows="6" readonly class="font-mono w-full rounded-2xl border border-divider bg-background/70 px-4 py-3 text-xs leading-6 text-text-secondary outline-none">{{ $warningsJson }}</textarea>
    </div>
</div>

<div class="flex flex-wrap items-center gap-3 pt-8">
    <button type="submit" class="rounded-2xl bg-primary px-6 py-3 text-xs font-black uppercase tracking-[0.2em] text-background shadow-cyan-glow">Save Layout</button>
    <a href="{{ route('admin.vr-scene-layouts.index') }}" class="rounded-2xl border border-divider px-6 py-3 text-xs font-black uppercase tracking-[0.2em] text-text-secondary hover:text-white">Back</a>
    @if ($layout->exists)
        <a href="{{ route('admin.vr-scene-layouts.show', $layout) }}" class="rounded-2xl border border-primary/30 px-6 py-3 text-xs font-black uppercase tracking-[0.2em] text-primary hover:bg-primary hover:text-background">Show</a>
        <a href="{{ url('/api/v1/vr/scenes/'.$layout->scene_slug.'/layout') }}" target="_blank" class="rounded-2xl border border-emerald-400/30 px-6 py-3 text-xs font-black uppercase tracking-[0.2em] text-emerald-300 hover:bg-emerald-500 hover:text-white">View Public API</a>
    @endif
</div>
