@php
    $layoutJson = old('layout_json', json_encode($layout->layout_json ?? [], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
    $metadataJson = old('metadata_json', json_encode($layout->metadata_json ?? [], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
    $warningsJson = json_encode($layout->validation_warnings_json ?? [], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    $validation = session('layout_validation');
    $componentTypes = config('vr_scene.component_types', []);
    $publicApiUrl = $layout->scene_slug ? url('/api/v1/vr/scenes/'.$layout->scene_slug.'/layout') : null;
    $statusClass = match ($layout->status) {
        \App\Models\VrSceneLayout::STATUS_PUBLISHED => 'border-emerald-400/40 bg-emerald-500/10 text-emerald-300',
        \App\Models\VrSceneLayout::STATUS_DRAFT => 'border-amber-400/40 bg-amber-500/10 text-amber-300',
        \App\Models\VrSceneLayout::STATUS_ARCHIVED => 'border-slate-400/30 bg-slate-500/10 text-slate-300',
        default => 'border-divider bg-background text-text-secondary',
    };
    $snippets = [
        'learning_panel' => [
            'id' => 'opening-briefing',
            'type' => 'learning_panel',
            'title' => 'Tujuan Utama',
            'transform' => ['position' => [0, 1.8, -3.5], 'rotation' => [0, 0, 0], 'scale' => [1, 1, 1]],
        ],
        'hotspot_marker' => [
            'id' => 'floor-surface',
            'type' => 'hotspot_marker',
            'title' => 'Permukaan Lantai',
            'body' => 'Lantai halus dan mudah dibersihkan mendukung higiene ruang produksi.',
            'transform' => ['position' => [-1.2, 0.12, 1.4], 'rotation' => [0, 0, 0], 'scale' => [1, 1, 1]],
            'interaction' => ['eventName' => 'gmp_standard_room.floor_inspected', 'evidenceEventType' => 'hotspot_inspected'],
        ],
        'wall_sign' => [
            'id' => 'room-status-sign',
            'type' => 'wall_sign',
            'title' => 'STATUS RUANG',
            'transform' => ['position' => [0, 2.05, -3.7], 'rotation' => [0, 0, 0], 'scale' => [1, 1, 1]],
        ],
        'floor_arrow' => [
            'id' => 'personnel-flow-arrow',
            'type' => 'floor_arrow',
            'title' => 'Alur Personel',
            'transform' => ['position' => [-2.2, 0.08, 1.8], 'rotation' => [-1.57, 0, 0], 'scale' => [1, 1, 1]],
        ],
        'status_board' => [
            'id' => 'room-status-board',
            'type' => 'status_board',
            'title' => 'Status Kesiapan Ruang',
            'transform' => ['position' => [-3.4, 1.5, 2.6], 'rotation' => [0, 3.14, 0], 'scale' => [1, 1, 1]],
        ],
    ];
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

@if ($validation)
    <div class="rounded-2xl border {{ $validation['valid'] ? 'border-emerald-400/30 bg-emerald-500/10 text-emerald-200' : 'border-red-500/30 bg-red-500/10 text-red-200' }} p-5 text-sm">
        <p class="text-[10px] font-black uppercase tracking-[0.3em]">{{ $validation['valid'] ? 'Layout valid' : 'Layout invalid' }}</p>
        @if (!empty($validation['errors']))
            <div class="mt-3">
                <p class="font-black text-white">Errors</p>
                <ul class="mt-2 list-disc space-y-1 pl-5">
                    @foreach ($validation['errors'] as $field => $messages)
                        @foreach ((array) $messages as $message)
                            <li><span class="font-mono text-xs">{{ $field }}</span>: {{ $message }}</li>
                        @endforeach
                    @endforeach
                </ul>
            </div>
        @endif
        @if (!empty($validation['warnings']))
            <div class="mt-3">
                <p class="font-black text-white">Warnings</p>
                <ul class="mt-2 list-disc space-y-1 pl-5">
                    @foreach ($validation['warnings'] as $warning)
                        <li>{{ $warning }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
    </div>
@endif

@if ($layout->exists)
    <div class="grid gap-4 md:grid-cols-3">
        <div class="rounded-2xl border border-divider bg-background/70 p-4">
            <p class="text-[9px] font-black uppercase tracking-[0.3em] text-text-tertiary">Status</p>
            <p class="mt-2 inline-flex rounded-full border px-3 py-1 text-xs font-black uppercase tracking-[0.18em] {{ $statusClass }}">{{ $layout->status }}</p>
        </div>
        <div class="rounded-2xl border border-divider bg-background/70 p-4">
            <p class="text-[9px] font-black uppercase tracking-[0.3em] text-text-tertiary">Published</p>
            <p class="mt-2 text-sm font-bold text-white">{{ $layout->published_at?->format('Y-m-d H:i') ?? '-' }}</p>
            <p class="mt-1 text-xs text-text-tertiary">By: {{ $layout->publisher?->name ?? '-' }}</p>
        </div>
        <div class="rounded-2xl border border-divider bg-background/70 p-4">
            <p class="text-[9px] font-black uppercase tracking-[0.3em] text-text-tertiary">Public API</p>
            @if ($publicApiUrl)
                <a href="{{ $publicApiUrl }}" target="_blank" class="mt-2 block break-all text-xs font-bold text-primary hover:text-white">{{ $publicApiUrl }}</a>
            @else
                <p class="mt-2 text-xs text-text-tertiary">Save scene_slug first.</p>
            @endif
        </div>
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
        <div class="mb-2 flex flex-wrap items-center justify-between gap-3">
            <label class="block text-[9px] font-black uppercase tracking-[0.3em] text-text-tertiary">Layout JSON</label>
            <div class="flex flex-wrap gap-2">
                <button type="submit" name="editor_action" value="validate" class="rounded-xl border border-emerald-400/30 px-4 py-2 text-[10px] font-black uppercase tracking-[0.18em] text-emerald-300 hover:bg-emerald-500 hover:text-white">Validate Layout</button>
                <button type="submit" name="editor_action" value="format" class="rounded-xl border border-primary/30 px-4 py-2 text-[10px] font-black uppercase tracking-[0.18em] text-primary hover:bg-primary hover:text-background">Format JSON</button>
            </div>
        </div>
        <textarea name="layout_json" rows="26" spellcheck="false" required class="font-mono w-full rounded-2xl border border-divider bg-background px-4 py-3 text-xs leading-6 text-white outline-none focus:border-primary">{{ $layoutJson }}</textarea>
        <p class="mt-2 text-xs text-text-tertiary">Plain JSON only. Component IDs must be unique and component types must match the backend VR scene registry.</p>
    </div>

    <div class="lg:col-span-2">
        <div class="mb-2 flex flex-wrap items-center justify-between gap-3">
            <label class="block text-[9px] font-black uppercase tracking-[0.3em] text-text-tertiary">Metadata JSON</label>
            <button type="submit" name="editor_action" value="format" class="rounded-xl border border-primary/30 px-4 py-2 text-[10px] font-black uppercase tracking-[0.18em] text-primary hover:bg-primary hover:text-background">Format JSON</button>
        </div>
        <textarea name="metadata_json" rows="8" spellcheck="false" class="font-mono w-full rounded-2xl border border-divider bg-background px-4 py-3 text-xs leading-6 text-white outline-none focus:border-primary">{{ $metadataJson }}</textarea>
    </div>

    <div class="lg:col-span-2">
        <label class="mb-2 block text-[9px] font-black uppercase tracking-[0.3em] text-text-tertiary">Validation Warnings</label>
        <textarea rows="6" readonly class="font-mono w-full rounded-2xl border border-divider bg-background/70 px-4 py-3 text-xs leading-6 text-text-secondary outline-none">{{ $warningsJson }}</textarea>
    </div>
</div>

<div class="grid gap-6 lg:grid-cols-3">
    <div class="rounded-2xl border border-divider bg-background/70 p-5">
        <h3 class="text-sm font-black uppercase tracking-[0.2em] text-white">Component Types</h3>
        <div class="mt-4 flex flex-wrap gap-2">
            @foreach ($componentTypes as $type)
                <code class="rounded-lg border border-primary/20 bg-primary/5 px-2 py-1 text-[11px] font-bold text-primary">{{ $type }}</code>
            @endforeach
        </div>
    </div>

    <div class="rounded-2xl border border-divider bg-background/70 p-5">
        <h3 class="text-sm font-black uppercase tracking-[0.2em] text-white">Transform Guidance</h3>
        <ul class="mt-4 list-disc space-y-2 pl-5 text-xs leading-5 text-text-secondary">
            <li><code>position: [x, y, z]</code></li>
            <li><code>rotation: [x, y, z]</code></li>
            <li><code>scale: [x, y, z]</code></li>
            <li>Wall panels usually read well at <code>y</code> around <code>1.4-2.2</code>.</li>
            <li>Avoid panels outside <code>roomBounds</code> and avoid <code>scale</code> value <code>0</code>.</li>
            <li>Use rotation so panels face the room center.</li>
        </ul>
    </div>

    <div class="rounded-2xl border border-divider bg-background/70 p-5">
        <h3 class="text-sm font-black uppercase tracking-[0.2em] text-white">JSON Snippets</h3>
        <div class="mt-4 space-y-3">
            @foreach ($snippets as $type => $snippet)
                <details class="rounded-xl border border-divider bg-surface/70 p-3">
                    <summary class="cursor-pointer text-xs font-black text-primary">{{ $type }}</summary>
                    <pre class="mt-3 max-h-56 overflow-auto rounded-lg bg-background p-3 text-[10px] leading-5 text-text-secondary">{{ json_encode($snippet, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) }}</pre>
                </details>
            @endforeach
        </div>
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
