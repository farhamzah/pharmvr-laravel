@php
    $itemsJson = old('items_json', json_encode($content->items_json ?? [], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
    $metadataJson = old('metadata_json', json_encode($content->metadata_json ?? [], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
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
        <input type="text" name="scene_slug" value="{{ old('scene_slug', $content->scene_slug) }}" required class="w-full rounded-2xl border border-divider bg-background px-4 py-3 text-sm font-bold text-white outline-none focus:border-primary">
    </div>

    <div>
        <label class="mb-2 block text-[9px] font-black uppercase tracking-[0.3em] text-text-tertiary">Content Key</label>
        <input type="text" name="content_key" value="{{ old('content_key', $content->content_key) }}" required class="w-full rounded-2xl border border-divider bg-background px-4 py-3 text-sm font-bold text-white outline-none focus:border-primary">
    </div>

    <div>
        <label class="mb-2 block text-[9px] font-black uppercase tracking-[0.3em] text-text-tertiary">Content Type</label>
        <select name="content_type" required class="w-full rounded-2xl border border-divider bg-background px-4 py-3 text-sm font-bold text-white outline-none focus:border-primary">
            @foreach (['grid_panel', 'text_panel', 'checklist', 'hotspot_copy'] as $type)
                <option value="{{ $type }}" @selected(old('content_type', $content->content_type) === $type)>{{ $type }}</option>
            @endforeach
        </select>
    </div>

    <div>
        <label class="mb-2 block text-[9px] font-black uppercase tracking-[0.3em] text-text-tertiary">Locale</label>
        <input type="text" name="locale" value="{{ old('locale', $content->locale) }}" required class="w-full rounded-2xl border border-divider bg-background px-4 py-3 text-sm font-bold text-white outline-none focus:border-primary">
    </div>

    <div class="lg:col-span-2">
        <label class="mb-2 block text-[9px] font-black uppercase tracking-[0.3em] text-text-tertiary">Title</label>
        <input type="text" name="title" value="{{ old('title', $content->title) }}" required class="w-full rounded-2xl border border-divider bg-background px-4 py-3 text-sm font-bold text-white outline-none focus:border-primary">
    </div>

    <div class="lg:col-span-2">
        <label class="mb-2 block text-[9px] font-black uppercase tracking-[0.3em] text-text-tertiary">Subtitle</label>
        <input type="text" name="subtitle" value="{{ old('subtitle', $content->subtitle) }}" class="w-full rounded-2xl border border-divider bg-background px-4 py-3 text-sm font-bold text-white outline-none focus:border-primary">
    </div>

    <div class="lg:col-span-2">
        <label class="mb-2 block text-[9px] font-black uppercase tracking-[0.3em] text-text-tertiary">Body</label>
        <textarea name="body" rows="4" class="w-full rounded-2xl border border-divider bg-background px-4 py-3 text-sm font-bold text-white outline-none focus:border-primary">{{ old('body', $content->body) }}</textarea>
    </div>

    <div class="lg:col-span-2">
        <label class="mb-2 block text-[9px] font-black uppercase tracking-[0.3em] text-text-tertiary">Items JSON</label>
        <textarea name="items_json" rows="18" spellcheck="false" class="font-mono w-full rounded-2xl border border-divider bg-background px-4 py-3 text-xs text-white outline-none focus:border-primary">{{ $itemsJson }}</textarea>
        <p class="mt-2 text-xs text-text-tertiary">Use a JSON array for ordered panel/hotspot items.</p>
    </div>

    <div class="lg:col-span-2">
        <label class="mb-2 block text-[9px] font-black uppercase tracking-[0.3em] text-text-tertiary">Metadata JSON</label>
        <textarea name="metadata_json" rows="8" spellcheck="false" class="font-mono w-full rounded-2xl border border-divider bg-background px-4 py-3 text-xs text-white outline-none focus:border-primary">{{ $metadataJson }}</textarea>
        <p class="mt-2 text-xs text-text-tertiary">Use a JSON object or array for optional CMS metadata.</p>
    </div>

    <div>
        <label class="mb-2 block text-[9px] font-black uppercase tracking-[0.3em] text-text-tertiary">Sort Order</label>
        <input type="number" min="0" name="sort_order" value="{{ old('sort_order', $content->sort_order ?? 0) }}" class="w-full rounded-2xl border border-divider bg-background px-4 py-3 text-sm font-bold text-white outline-none focus:border-primary">
    </div>

    <div>
        <label class="mb-2 block text-[9px] font-black uppercase tracking-[0.3em] text-text-tertiary">Version</label>
        <input type="number" min="1" name="version" value="{{ old('version', $content->version ?? 1) }}" required class="w-full rounded-2xl border border-divider bg-background px-4 py-3 text-sm font-bold text-white outline-none focus:border-primary">
    </div>

    <div>
        <label class="mb-2 block text-[9px] font-black uppercase tracking-[0.3em] text-text-tertiary">Status</label>
        <select name="status" required class="w-full rounded-2xl border border-divider bg-background px-4 py-3 text-sm font-bold text-white outline-none focus:border-primary">
            @foreach (\App\Models\VrSceneContent::STATUSES as $status)
                <option value="{{ $status }}" @selected(old('status', $content->status) === $status)>{{ strtoupper($status) }}</option>
            @endforeach
        </select>
    </div>

    <div class="flex items-center rounded-2xl border border-divider bg-background px-4 py-3">
        <input type="hidden" name="is_active" value="0">
        <label class="flex cursor-pointer items-center gap-3 text-sm font-bold text-white">
            <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $content->is_active ?? true)) class="h-5 w-5 rounded border-divider bg-background text-primary focus:ring-primary">
            Active and available to published API
        </label>
    </div>
</div>

<div class="flex flex-wrap items-center gap-3 pt-8">
    <button type="submit" class="rounded-2xl bg-primary px-6 py-3 text-xs font-black uppercase tracking-[0.2em] text-background shadow-cyan-glow">Save Content</button>
    <a href="{{ route('admin.vr-scene-contents.index') }}" class="rounded-2xl border border-divider px-6 py-3 text-xs font-black uppercase tracking-[0.2em] text-text-secondary hover:text-white">Back</a>
</div>
