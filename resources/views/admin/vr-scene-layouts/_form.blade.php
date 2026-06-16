@php
    $layoutJson = old('layout_json', json_encode($layout->layout_json ?? [], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
    $metadataJson = old('metadata_json', json_encode($layout->metadata_json ?? [], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
    $warningsJson = json_encode($layout->validation_warnings_json ?? [], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    $validation = session('layout_validation');
    $componentTypes = config('vr_scene.component_types', []);
    $positionPresets = config('vr_scene.position_presets', []);
    $componentDefaults = config('vr_scene.component_defaults', []);
    $publicApiUrl = $layout->scene_slug ? url('/api/v1/vr/scenes/'.$layout->scene_slug.'/layout') : null;
    $statusClass = match ($layout->status) {
        \App\Models\VrSceneLayout::STATUS_PUBLISHED => 'border-emerald-400/40 bg-emerald-500/10 text-emerald-300',
        \App\Models\VrSceneLayout::STATUS_DRAFT => 'border-amber-400/40 bg-amber-500/10 text-amber-300',
        \App\Models\VrSceneLayout::STATUS_ARCHIVED => 'border-slate-400/30 bg-slate-500/10 text-slate-300',
        default => 'border-divider bg-background text-text-secondary',
    };
    $snippetTypes = ['learning_panel', 'hotspot_marker', 'wall_sign', 'floor_arrow', 'status_board'];
    $snippets = array_intersect_key($componentDefaults, array_flip($snippetTypes));
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

@include('admin.vr-scene-layouts._layout_preview', ['previewLayoutJson' => $layoutJson, 'positionEditor' => true])

<div class="rounded-2xl border border-primary/20 bg-primary/5 p-5">
    <div class="mb-5 flex flex-wrap items-start justify-between gap-4">
        <div>
            <h3 class="text-sm font-black uppercase tracking-[0.2em] text-white">Component Builder</h3>
            <p class="mt-2 max-w-3xl text-xs leading-5 text-text-secondary">Generate a safe component snippet without writing JSON from scratch. Generated snippet must be inserted into <code>layout_json.components</code>. Validate Layout before publishing. Use Duplicate as Draft before editing a published layout.</p>
        </div>
        <span class="rounded-full border border-amber-400/30 bg-amber-500/10 px-3 py-1 text-[10px] font-black uppercase tracking-[0.18em] text-amber-200">MVP Helper</span>
    </div>

    <div class="grid gap-4 lg:grid-cols-4">
        <div>
            <label class="mb-2 block text-[9px] font-black uppercase tracking-[0.3em] text-text-tertiary">Component Type</label>
            <select id="builder-component-type" class="w-full rounded-xl border border-divider bg-background px-3 py-2 text-xs font-bold text-white outline-none focus:border-primary">
                @foreach ($componentTypes as $type)
                    <option value="{{ $type }}">{{ $type }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="mb-2 block text-[9px] font-black uppercase tracking-[0.3em] text-text-tertiary">ID / Instance Key</label>
            <input id="builder-component-id" type="text" value="component-draft" class="w-full rounded-xl border border-divider bg-background px-3 py-2 text-xs font-bold text-white outline-none focus:border-primary">
        </div>
        <div>
            <label class="mb-2 block text-[9px] font-black uppercase tracking-[0.3em] text-text-tertiary">Title</label>
            <input id="builder-component-title" type="text" value="Titik Inspeksi" class="w-full rounded-xl border border-divider bg-background px-3 py-2 text-xs font-bold text-white outline-none focus:border-primary">
        </div>
        <div>
            <label class="mb-2 block text-[9px] font-black uppercase tracking-[0.3em] text-text-tertiary">Variant</label>
            <input id="builder-component-variant" type="text" value="cyan" class="w-full rounded-xl border border-divider bg-background px-3 py-2 text-xs font-bold text-white outline-none focus:border-primary">
        </div>
        <div class="lg:col-span-2">
            <label class="mb-2 block text-[9px] font-black uppercase tracking-[0.3em] text-text-tertiary">Body</label>
            <textarea id="builder-component-body" rows="3" class="w-full rounded-xl border border-divider bg-background px-3 py-2 text-xs leading-5 text-white outline-none focus:border-primary">Amati elemen ruang bersih ini dan hubungkan dengan prinsip CPOB.</textarea>
        </div>
        <div>
            <label class="mb-2 block text-[9px] font-black uppercase tracking-[0.3em] text-text-tertiary">Position Preset</label>
            <select id="builder-position-preset" class="w-full rounded-xl border border-divider bg-background px-3 py-2 text-xs font-bold text-white outline-none focus:border-primary">
                @foreach ($positionPresets as $key => $preset)
                    <option value="{{ $key }}">{{ $preset['label'] ?? $key }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="mb-2 block text-[9px] font-black uppercase tracking-[0.3em] text-text-tertiary">Active</label>
            <select id="builder-component-active" class="w-full rounded-xl border border-divider bg-background px-3 py-2 text-xs font-bold text-white outline-none focus:border-primary">
                <option value="true">true</option>
                <option value="false">false</option>
            </select>
        </div>
        <div>
            <label class="mb-2 block text-[9px] font-black uppercase tracking-[0.3em] text-text-tertiary">Custom Position [x, y, z]</label>
            <input id="builder-position" type="text" value="[0, 1.7, -3.8]" class="font-mono w-full rounded-xl border border-divider bg-background px-3 py-2 text-xs text-white outline-none focus:border-primary">
        </div>
        <div>
            <label class="mb-2 block text-[9px] font-black uppercase tracking-[0.3em] text-text-tertiary">Custom Rotation [x, y, z]</label>
            <input id="builder-rotation" type="text" value="[0, 0, 0]" class="font-mono w-full rounded-xl border border-divider bg-background px-3 py-2 text-xs text-white outline-none focus:border-primary">
        </div>
        <div>
            <label class="mb-2 block text-[9px] font-black uppercase tracking-[0.3em] text-text-tertiary">Custom Scale [x, y, z]</label>
            <input id="builder-scale" type="text" value="[1, 1, 1]" class="font-mono w-full rounded-xl border border-divider bg-background px-3 py-2 text-xs text-white outline-none focus:border-primary">
        </div>
        <div class="flex items-end">
            <button type="button" id="builder-generate" class="w-full rounded-xl bg-primary px-4 py-2 text-[10px] font-black uppercase tracking-[0.18em] text-background hover:bg-cyan-200">Generate Snippet</button>
        </div>
        <div class="lg:col-span-4">
            <div class="mb-2 flex flex-wrap items-center justify-between gap-3">
                <label class="block text-[9px] font-black uppercase tracking-[0.3em] text-text-tertiary">Generated Component JSON</label>
                <div class="flex flex-wrap gap-2">
                    <button type="button" id="builder-copy" class="rounded-xl border border-primary/30 px-4 py-2 text-[10px] font-black uppercase tracking-[0.18em] text-primary hover:bg-primary hover:text-background">Copy Snippet</button>
                    <button type="button" id="builder-insert" class="rounded-xl border border-emerald-400/30 px-4 py-2 text-[10px] font-black uppercase tracking-[0.18em] text-emerald-300 hover:bg-emerald-500 hover:text-white">Insert into layout_json components array</button>
                </div>
            </div>
            <textarea id="builder-output" rows="12" readonly class="font-mono w-full rounded-2xl border border-divider bg-background px-4 py-3 text-xs leading-6 text-white outline-none"></textarea>
            <p id="builder-message" class="mt-2 text-xs font-bold text-text-tertiary"></p>
        </div>
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
        <h3 class="text-sm font-black uppercase tracking-[0.2em] text-white">Position Presets</h3>
        <div class="mt-4 max-h-80 space-y-3 overflow-auto pr-1">
            @foreach ($positionPresets as $key => $preset)
                <div class="rounded-xl border border-divider bg-surface/70 p-3">
                    <p class="text-xs font-black text-primary">{{ $preset['label'] ?? $key }}</p>
                    <p class="mt-1 font-mono text-[10px] text-text-secondary">{{ $key }}</p>
                    <p class="mt-2 font-mono text-[10px] leading-5 text-text-tertiary">position: {{ json_encode($preset['position'] ?? []) }}</p>
                    <p class="font-mono text-[10px] leading-5 text-text-tertiary">rotation: {{ json_encode($preset['rotation'] ?? []) }}</p>
                    <p class="font-mono text-[10px] leading-5 text-text-tertiary">scale: {{ json_encode($preset['scale'] ?? []) }}</p>
                </div>
            @endforeach
        </div>
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

<script type="application/json" id="vr-component-defaults-json">{!! json_encode($componentDefaults, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) !!}</script>
<script type="application/json" id="vr-position-presets-json">{!! json_encode($positionPresets, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) !!}</script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const defaultsEl = document.getElementById('vr-component-defaults-json');
        const presetsEl = document.getElementById('vr-position-presets-json');
        const defaults = defaultsEl ? JSON.parse(defaultsEl.textContent || '{}') : {};
        const presets = presetsEl ? JSON.parse(presetsEl.textContent || '{}') : {};

        const typeInput = document.getElementById('builder-component-type');
        const idInput = document.getElementById('builder-component-id');
        const titleInput = document.getElementById('builder-component-title');
        const bodyInput = document.getElementById('builder-component-body');
        const variantInput = document.getElementById('builder-component-variant');
        const presetInput = document.getElementById('builder-position-preset');
        const activeInput = document.getElementById('builder-component-active');
        const positionInput = document.getElementById('builder-position');
        const rotationInput = document.getElementById('builder-rotation');
        const scaleInput = document.getElementById('builder-scale');
        const outputInput = document.getElementById('builder-output');
        const messageEl = document.getElementById('builder-message');
        const layoutInput = document.querySelector('textarea[name="layout_json"]');

        const clone = function (value) {
            return JSON.parse(JSON.stringify(value || {}));
        };

        const showMessage = function (message, isError = false) {
            if (!messageEl) {
                return;
            }

            messageEl.textContent = message;
            messageEl.className = isError
                ? 'mt-2 text-xs font-bold text-red-300'
                : 'mt-2 text-xs font-bold text-emerald-300';
        };

        const formatVector = function (value) {
            return JSON.stringify(value || [0, 0, 0]);
        };

        const parseVector = function (raw, label) {
            let value;
            try {
                value = JSON.parse(raw);
            } catch (error) {
                value = raw.split(',').map(function (part) {
                    return Number(part.trim());
                });
            }

            if (!Array.isArray(value) || value.length !== 3 || value.some(function (number) {
                return typeof number !== 'number' || Number.isNaN(number);
            })) {
                throw new Error(label + ' must be an array of 3 numbers.');
            }

            return value;
        };

        const applyPreset = function () {
            const preset = presets[presetInput?.value] || {};
            if (positionInput && preset.position) {
                positionInput.value = formatVector(preset.position);
            }
            if (rotationInput && preset.rotation) {
                rotationInput.value = formatVector(preset.rotation);
            }
            if (scaleInput && preset.scale) {
                scaleInput.value = formatVector(preset.scale);
            }
        };

        const applyDefaults = function () {
            const defaultComponent = defaults[typeInput?.value] || {};
            if (idInput) {
                idInput.value = defaultComponent.id || (typeInput.value + '-draft');
            }
            if (titleInput) {
                titleInput.value = defaultComponent.title || '';
            }
            if (bodyInput) {
                bodyInput.value = defaultComponent.body || '';
            }
            if (variantInput) {
                variantInput.value = defaultComponent.variant || '';
            }
            if (activeInput) {
                activeInput.value = defaultComponent.active === false ? 'false' : 'true';
            }

            const transform = defaultComponent.transform || {};
            if (positionInput) {
                positionInput.value = formatVector(transform.position || [0, 1.7, -3.8]);
            }
            if (rotationInput) {
                rotationInput.value = formatVector(transform.rotation || [0, 0, 0]);
            }
            if (scaleInput) {
                scaleInput.value = formatVector(transform.scale || [1, 1, 1]);
            }
        };

        const generateSnippet = function () {
            const type = typeInput?.value || 'hotspot_marker';
            const snippet = clone(defaults[type] || {
                id: type + '-draft',
                type: type,
                title: '',
                body: '',
                variant: '',
                active: true,
                transform: {},
            });

            snippet.id = idInput?.value || snippet.id || (type + '-draft');
            snippet.type = type;
            snippet.title = titleInput?.value || snippet.title || '';
            snippet.body = bodyInput?.value || snippet.body || '';
            snippet.variant = variantInput?.value || snippet.variant || '';
            snippet.active = activeInput?.value !== 'false';
            snippet.transform = {
                position: parseVector(positionInput?.value || '[0, 1.7, -3.8]', 'position'),
                rotation: parseVector(rotationInput?.value || '[0, 0, 0]', 'rotation'),
                scale: parseVector(scaleInput?.value || '[1, 1, 1]', 'scale'),
            };

            if (!snippet.body) {
                delete snippet.body;
            }
            if (!snippet.variant) {
                delete snippet.variant;
            }

            outputInput.value = JSON.stringify(snippet, null, 2);
            showMessage('Snippet generated. Insert it into layout_json.components, then validate before publishing.');

            return snippet;
        };

        document.getElementById('builder-generate')?.addEventListener('click', function () {
            try {
                generateSnippet();
            } catch (error) {
                showMessage(error.message, true);
            }
        });

        document.getElementById('builder-copy')?.addEventListener('click', function () {
            try {
                if (!outputInput.value) {
                    generateSnippet();
                }

                if (navigator.clipboard) {
                    navigator.clipboard.writeText(outputInput.value);
                    showMessage('Snippet copied to clipboard.');
                    return;
                }

                outputInput.select();
                document.execCommand('copy');
                showMessage('Snippet copied to clipboard.');
            } catch (error) {
                showMessage(error.message, true);
            }
        });

        document.getElementById('builder-insert')?.addEventListener('click', function () {
            try {
                const snippet = outputInput.value ? JSON.parse(outputInput.value) : generateSnippet();
                const layout = JSON.parse(layoutInput?.value || '{}');
                if (!Array.isArray(layout.components)) {
                    layout.components = [];
                }
                layout.components.push(snippet);
                layoutInput.value = JSON.stringify(layout, null, 2);
                showMessage('Snippet inserted into layout_json.components. Run Validate Layout before publishing.');
            } catch (error) {
                showMessage('Insert failed: ' + error.message, true);
            }
        });

        typeInput?.addEventListener('change', function () {
            applyDefaults();
            try {
                generateSnippet();
            } catch (error) {
                showMessage(error.message, true);
            }
        });
        presetInput?.addEventListener('change', function () {
            applyPreset();
            try {
                generateSnippet();
            } catch (error) {
                showMessage(error.message, true);
            }
        });

        applyDefaults();
        if (presetInput?.value) {
            applyPreset();
        }
        try {
            generateSnippet();
        } catch (error) {
            showMessage(error.message, true);
        }
    });
</script>
