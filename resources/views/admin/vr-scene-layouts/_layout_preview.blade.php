@php
    $previewSource = $previewLayout ?? null;
    $previewError = null;
    $positionEditorEnabled = (bool) ($positionEditor ?? false);

    if (isset($previewLayoutJson)) {
        $decodedPreview = json_decode($previewLayoutJson, true);
        if (json_last_error() === JSON_ERROR_NONE && is_array($decodedPreview)) {
            $previewSource = $decodedPreview;
        } else {
            $previewError = 'Preview unavailable: invalid layout JSON.';
        }
    }

    if (!is_array($previewSource)) {
        $previewSource = [];
    }

    $defaultBounds = config('vr_scene.default_room_bounds', [
        'x' => [-4, 4],
        'z' => [-4, 4],
    ]);
    $rawBounds = $previewSource['roomBounds'] ?? [];
    $readBound = function (array $bounds, string $axis, int $index, string $flatKey, string $nestedKey) use ($defaultBounds) {
        $value = data_get($bounds, "{$axis}.{$index}")
            ?? data_get($bounds, $flatKey)
            ?? data_get($bounds, $nestedKey)
            ?? data_get($defaultBounds, "{$axis}.{$index}");

        return is_numeric($value) ? (float) $value : (float) data_get($defaultBounds, "{$axis}.{$index}", 0);
    };

    $xMin = $readBound($rawBounds, 'x', 0, 'minX', 'min.x');
    $xMax = $readBound($rawBounds, 'x', 1, 'maxX', 'max.x');
    $zMin = $readBound($rawBounds, 'z', 0, 'minZ', 'min.z');
    $zMax = $readBound($rawBounds, 'z', 1, 'maxZ', 'max.z');

    if ($xMax <= $xMin) {
        [$xMin, $xMax] = $defaultBounds['x'];
    }
    if ($zMax <= $zMin) {
        [$zMin, $zMax] = $defaultBounds['z'];
    }

    $width = 640;
    $height = 420;
    $padding = 42;
    $plotWidth = $width - ($padding * 2);
    $plotHeight = $height - ($padding * 2);
    $xRange = max(0.001, $xMax - $xMin);
    $zRange = max(0.001, $zMax - $zMin);

    $typeColors = [
        'hotspot_marker' => '#22d3ee',
        'learning_panel' => '#a78bfa',
        'cms_panel' => '#38bdf8',
        'checklist_panel' => '#34d399',
        'equipment_island' => '#fbbf24',
        'wall_sign' => '#f472b6',
        'floor_arrow' => '#60a5fa',
        'status_board' => '#4ade80',
        'pressure_gauge' => '#fb7185',
        'hvac_diffuser' => '#93c5fd',
        'return_grille' => '#94a3b8',
        'other' => '#cbd5e1',
    ];

    $components = collect($previewSource['components'] ?? [])
        ->filter(fn ($component) => is_array($component))
        ->values()
        ->map(function (array $component) use ($xMin, $xMax, $zMin, $zMax, $padding, $plotWidth, $plotHeight, $xRange, $zRange, $typeColors) {
            $position = data_get($component, 'transform.position');
            $rotation = data_get($component, 'transform.rotation');
            $hasPosition = is_array($position) && count($position) === 3 && collect($position)->every(fn ($value) => is_numeric($value));
            $positionVector = $hasPosition ? array_values($position) : null;
            $x = $hasPosition ? (float) $positionVector[0] : null;
            $y = $hasPosition ? (float) $positionVector[1] : null;
            $z = $hasPosition ? (float) $positionVector[2] : null;
            $outsideBounds = $hasPosition && ($x < $xMin || $x > $xMax || $z < $zMin || $z > $zMax);
            $svgX = $hasPosition ? $padding + (($x - $xMin) / $xRange) * $plotWidth : null;
            $svgY = $hasPosition ? $padding + (($zMax - $z) / $zRange) * $plotHeight : null;
            $type = (string) ($component['type'] ?? 'other');
            $label = (string) ($component['id'] ?? $component['title'] ?? 'component');
            $shortLabel = mb_strlen($label) > 22 ? mb_substr($label, 0, 19).'...' : $label;

            return [
                'id' => (string) ($component['id'] ?? '-'),
                'type' => $type,
                'title' => (string) ($component['title'] ?? '-'),
                'position' => $positionVector,
                'rotation' => $rotation,
                'active' => ($component['active'] ?? true) !== false && ($component['isActive'] ?? true) !== false,
                'hasPosition' => $hasPosition,
                'outsideBounds' => $outsideBounds,
                'svgX' => $svgX,
                'svgY' => $svgY,
                'color' => $typeColors[$type] ?? $typeColors['other'],
                'label' => $shortLabel,
                'yaw' => is_array($rotation) && isset($rotation[1]) && is_numeric($rotation[1]) ? (float) $rotation[1] : null,
            ];
        });

    $warnings = [];
    foreach ($components as $component) {
        if (!$component['hasPosition']) {
            $warnings[] = "Component [{$component['id']}] has missing or invalid transform.position.";
        } elseif ($component['outsideBounds']) {
            $warnings[] = "Component [{$component['id']}] is outside room bounds.";
        }
    }
@endphp

<div class="rounded-3xl border border-divider bg-surface p-6 shadow-premium">
    <div class="mb-5 flex flex-col gap-3 lg:flex-row lg:items-start lg:justify-between">
        <div>
            <h2 class="text-lg font-black text-white">2D Layout Preview</h2>
            <p class="mt-2 text-xs leading-5 text-text-secondary">Top-down map from <code>layout_json</code>. X/Z are mapped to room floor coordinates; Y height appears in the component list.</p>
        </div>
        <div class="rounded-2xl border border-primary/20 bg-primary/5 px-4 py-3 text-[10px] font-black uppercase tracking-[0.18em] text-primary">
            Bounds X {{ $xMin }}..{{ $xMax }} / Z {{ $zMin }}..{{ $zMax }}
        </div>
    </div>

    @if ($previewError)
        <div class="rounded-2xl border border-red-500/30 bg-red-500/10 p-4 text-sm font-bold text-red-200">{{ $previewError }}</div>
    @else
        <div class="grid gap-6 xl:grid-cols-[minmax(0,2fr)_minmax(320px,1fr)]">
            <div class="overflow-auto rounded-2xl border border-divider bg-background p-4">
                <svg viewBox="0 0 {{ $width }} {{ $height }}" role="img" aria-label="2D top-down VR scene layout preview" class="min-h-[360px] w-full">
                    <rect x="{{ $padding }}" y="{{ $padding }}" width="{{ $plotWidth }}" height="{{ $plotHeight }}" rx="12" fill="#0f172a" stroke="#334155" stroke-width="2" />
                    @for ($i = 1; $i <= 3; $i++)
                        @php
                            $gridX = $padding + ($plotWidth / 4) * $i;
                            $gridY = $padding + ($plotHeight / 4) * $i;
                        @endphp
                        <line x1="{{ $gridX }}" y1="{{ $padding }}" x2="{{ $gridX }}" y2="{{ $height - $padding }}" stroke="#1e293b" stroke-width="1" />
                        <line x1="{{ $padding }}" y1="{{ $gridY }}" x2="{{ $width - $padding }}" y2="{{ $gridY }}" stroke="#1e293b" stroke-width="1" />
                    @endfor
                    <line x1="{{ $padding + (($plotWidth * (0 - $xMin)) / $xRange) }}" y1="{{ $padding }}" x2="{{ $padding + (($plotWidth * (0 - $xMin)) / $xRange) }}" y2="{{ $height - $padding }}" stroke="#22d3ee" stroke-opacity="0.35" stroke-dasharray="6 6" />
                    <line x1="{{ $padding }}" y1="{{ $padding + (($plotHeight * ($zMax - 0)) / $zRange) }}" x2="{{ $width - $padding }}" y2="{{ $padding + (($plotHeight * ($zMax - 0)) / $zRange) }}" stroke="#22d3ee" stroke-opacity="0.35" stroke-dasharray="6 6" />
                    <text x="{{ $width / 2 }}" y="24" fill="#94a3b8" font-size="12" font-weight="700" text-anchor="middle">North wall / -Z</text>
                    <text x="{{ $width / 2 }}" y="{{ $height - 12 }}" fill="#94a3b8" font-size="12" font-weight="700" text-anchor="middle">South wall / +Z</text>
                    <text x="12" y="{{ $height / 2 }}" fill="#94a3b8" font-size="12" font-weight="700" transform="rotate(-90 12 {{ $height / 2 }})" text-anchor="middle">West / -X</text>
                    <text x="{{ $width - 12 }}" y="{{ $height / 2 }}" fill="#94a3b8" font-size="12" font-weight="700" transform="rotate(90 {{ $width - 12 }} {{ $height / 2 }})" text-anchor="middle">East / +X</text>

                    @foreach ($components as $component)
                        @if ($component['hasPosition'])
                            <g opacity="{{ $component['active'] ? '1' : '0.35' }}" @if ($positionEditorEnabled) data-layout-marker data-component-id="{{ $component['id'] }}" style="cursor: pointer;" @endif>
                                <circle cx="{{ $component['svgX'] }}" cy="{{ $component['svgY'] }}" r="{{ $component['outsideBounds'] ? 9 : 7 }}" fill="{{ $component['color'] }}" stroke="{{ $component['outsideBounds'] ? '#f97316' : '#e2e8f0' }}" stroke-width="{{ $component['outsideBounds'] ? 4 : 2 }}" data-default-stroke="{{ $component['outsideBounds'] ? '#f97316' : '#e2e8f0' }}" data-default-stroke-width="{{ $component['outsideBounds'] ? 4 : 2 }}">
                                    <title>{{ $component['id'] }} / {{ $component['type'] }} / y={{ is_array($component['position']) ? $component['position'][1] : '-' }}</title>
                                </circle>
                                @if ($component['yaw'] !== null)
                                    @php
                                        $arrowX = $component['svgX'] + sin($component['yaw']) * 18;
                                        $arrowY = $component['svgY'] - cos($component['yaw']) * 18;
                                    @endphp
                                    <line x1="{{ $component['svgX'] }}" y1="{{ $component['svgY'] }}" x2="{{ $arrowX }}" y2="{{ $arrowY }}" stroke="{{ $component['color'] }}" stroke-width="2" stroke-linecap="round" />
                                @endif
                                <text x="{{ $component['svgX'] + 10 }}" y="{{ $component['svgY'] - 10 }}" fill="#e5e7eb" font-size="11" font-weight="700">{{ $component['label'] }}</text>
                            </g>
                        @endif
                    @endforeach
                </svg>
            </div>

            <div class="space-y-4">
                @if ($positionEditorEnabled)
                    <div id="layout-position-editor" class="rounded-2xl border border-primary/30 bg-primary/5 p-4" data-x-min="{{ $xMin }}" data-x-max="{{ $xMax }}" data-z-min="{{ $zMin }}" data-z-max="{{ $zMax }}">
                        <div class="mb-4">
                            <p class="text-[10px] font-black uppercase tracking-[0.25em] text-primary">2D Position Editor</p>
                            <p class="mt-2 text-xs leading-5 text-text-secondary">Select a marker, edit X/Z floor position and optional Y height, then apply to <code>layout_json</code>. Save and Validate Layout are still required.</p>
                        </div>

                        <div class="space-y-3 rounded-xl border border-divider bg-background/70 p-3 text-xs">
                            <p class="font-black text-white" id="layout-editor-selected-id">No component selected</p>
                            <p class="text-text-secondary">Type: <span id="layout-editor-selected-type">-</span></p>
                            <p class="text-text-secondary">Title: <span id="layout-editor-selected-title">-</span></p>
                            <p class="text-text-secondary">Rotation: <span id="layout-editor-selected-rotation" class="font-mono">-</span></p>
                            <p class="text-text-secondary">Active: <span id="layout-editor-selected-active">-</span></p>
                        </div>

                        <div class="mt-4 grid grid-cols-3 gap-3">
                            <div>
                                <label class="mb-2 block text-[9px] font-black uppercase tracking-[0.25em] text-text-tertiary">X</label>
                                <input id="layout-editor-x" type="number" step="0.01" class="w-full rounded-xl border border-divider bg-background px-3 py-2 text-xs font-bold text-white outline-none focus:border-primary">
                            </div>
                            <div>
                                <label class="mb-2 block text-[9px] font-black uppercase tracking-[0.25em] text-text-tertiary">Y Height</label>
                                <input id="layout-editor-y" type="number" step="0.01" class="w-full rounded-xl border border-divider bg-background px-3 py-2 text-xs font-bold text-white outline-none focus:border-primary">
                            </div>
                            <div>
                                <label class="mb-2 block text-[9px] font-black uppercase tracking-[0.25em] text-text-tertiary">Z</label>
                                <input id="layout-editor-z" type="number" step="0.01" class="w-full rounded-xl border border-divider bg-background px-3 py-2 text-xs font-bold text-white outline-none focus:border-primary">
                            </div>
                        </div>

                        <div class="mt-4 flex flex-wrap gap-2">
                            <button type="button" id="layout-editor-apply" class="rounded-xl bg-primary px-4 py-2 text-[10px] font-black uppercase tracking-[0.18em] text-background hover:bg-cyan-200">Apply Position to JSON</button>
                            <button type="button" id="layout-editor-reset" class="rounded-xl border border-divider px-4 py-2 text-[10px] font-black uppercase tracking-[0.18em] text-text-secondary hover:text-white">Reset from JSON</button>
                        </div>

                        <p id="layout-editor-message" class="mt-3 text-xs font-bold text-text-tertiary">Click a marker or Select button to begin.</p>
                    </div>
                @endif

                @if (!empty($warnings))
                    <div class="rounded-2xl border border-amber-400/30 bg-amber-500/10 p-4">
                        <p class="text-[10px] font-black uppercase tracking-[0.25em] text-amber-200">Preview Warnings</p>
                        <ul class="mt-3 list-disc space-y-1 pl-5 text-xs leading-5 text-amber-100">
                            @foreach ($warnings as $warning)
                                <li>{{ $warning }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div class="rounded-2xl border border-divider bg-background/70 p-4">
                    <p class="text-[10px] font-black uppercase tracking-[0.25em] text-text-tertiary">Legend</p>
                    <div class="mt-3 flex flex-wrap gap-2">
                        @foreach ($typeColors as $type => $color)
                            @if ($type !== 'other')
                                <span class="inline-flex items-center gap-2 rounded-lg border border-divider px-2 py-1 text-[10px] font-bold text-text-secondary">
                                    <span class="h-2.5 w-2.5 rounded-full" style="background: {{ $color }}"></span>{{ $type }}
                                </span>
                            @endif
                        @endforeach
                    </div>
                </div>

                <div class="max-h-[420px] overflow-auto rounded-2xl border border-divider bg-background/70">
                    <table class="min-w-full divide-y divide-divider text-left text-xs">
                        <thead class="bg-surface/80 text-[9px] font-black uppercase tracking-[0.18em] text-text-tertiary">
                            <tr>
                                <th class="px-3 py-3">Component</th>
                                <th class="px-3 py-3">Position</th>
                                <th class="px-3 py-3">State</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-divider">
                            @forelse ($components as $component)
                                <tr class="{{ $component['outsideBounds'] ? 'bg-amber-500/10' : '' }}">
                                    <td class="px-3 py-3 align-top">
                                        <p class="font-black text-white">{{ $component['id'] }}</p>
                                        <p class="mt-1 text-text-tertiary">{{ $component['type'] }}</p>
                                        <p class="mt-1 text-text-secondary">{{ $component['title'] }}</p>
                                    </td>
                                    <td class="px-3 py-3 align-top font-mono text-[10px] leading-5 text-text-secondary">
                                        @if ($component['hasPosition'])
                                            <p>{{ json_encode($component['position']) }}</p>
                                            <p>rotation: {{ json_encode($component['rotation'] ?? []) }}</p>
                                            <p>y: {{ $component['position'][1] }}</p>
                                        @else
                                            <span class="text-amber-200">Missing position</span>
                                        @endif
                                    </td>
                                    <td class="px-3 py-3 align-top">
                                        <span class="rounded-full border px-2 py-1 text-[10px] font-black uppercase tracking-[0.16em] {{ $component['active'] ? 'border-emerald-400/30 text-emerald-300' : 'border-slate-400/30 text-slate-300' }}">{{ $component['active'] ? 'active' : 'inactive' }}</span>
                                        @if ($positionEditorEnabled)
                                            <button type="button" data-layout-select data-component-id="{{ $component['id'] }}" class="mt-3 block rounded-lg border border-primary/30 px-3 py-1 text-[10px] font-black uppercase tracking-[0.16em] text-primary hover:bg-primary hover:text-background">Select</button>
                                        @endif
                                        @if ($component['outsideBounds'])
                                            <p class="mt-2 text-[10px] font-bold text-amber-200">Outside bounds</p>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="px-3 py-6 text-center text-text-secondary">No components found in layout_json.components.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif
</div>

@if ($positionEditorEnabled && !$previewError)
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const editor = document.getElementById('layout-position-editor');
            const layoutInput = document.querySelector('textarea[name="layout_json"]');
            const selectedIdEl = document.getElementById('layout-editor-selected-id');
            const selectedTypeEl = document.getElementById('layout-editor-selected-type');
            const selectedTitleEl = document.getElementById('layout-editor-selected-title');
            const selectedRotationEl = document.getElementById('layout-editor-selected-rotation');
            const selectedActiveEl = document.getElementById('layout-editor-selected-active');
            const xInput = document.getElementById('layout-editor-x');
            const yInput = document.getElementById('layout-editor-y');
            const zInput = document.getElementById('layout-editor-z');
            const messageEl = document.getElementById('layout-editor-message');
            const markers = Array.from(document.querySelectorAll('[data-layout-marker]'));
            const selectors = Array.from(document.querySelectorAll('[data-layout-select]'));
            let selectedComponentId = null;

            if (!editor || !layoutInput) {
                return;
            }

            const bounds = {
                xMin: Number(editor.dataset.xMin),
                xMax: Number(editor.dataset.xMax),
                zMin: Number(editor.dataset.zMin),
                zMax: Number(editor.dataset.zMax),
            };

            const setMessage = function (message, isError = false) {
                messageEl.textContent = message;
                messageEl.className = isError
                    ? 'mt-3 text-xs font-bold text-red-300'
                    : 'mt-3 text-xs font-bold text-emerald-300';
            };

            const parseLayout = function () {
                try {
                    const layout = JSON.parse(layoutInput.value || '{}');
                    if (!layout || typeof layout !== 'object') {
                        throw new Error('layout_json must be a JSON object.');
                    }
                    if (!Array.isArray(layout.components)) {
                        layout.components = [];
                    }
                    return layout;
                } catch (error) {
                    setMessage('Cannot edit position: invalid JSON. Fix layout_json first.', true);
                    return null;
                }
            };

            const findComponent = function (layout, id) {
                return layout.components.find(function (component) {
                    return component && component.id === id;
                }) || null;
            };

            const normalizePosition = function (component) {
                const position = component?.transform?.position;
                if (!Array.isArray(position) || position.length !== 3) {
                    return null;
                }
                const vector = position.map(function (value) {
                    return Number(value);
                });
                return vector.some(Number.isNaN) ? null : vector;
            };

            const highlightSelectedMarker = function () {
                markers.forEach(function (marker) {
                    const circle = marker.querySelector('circle');
                    if (!circle) {
                        return;
                    }
                    if (marker.dataset.componentId === selectedComponentId) {
                        circle.setAttribute('stroke', '#facc15');
                        circle.setAttribute('stroke-width', '5');
                    } else if (circle.getAttribute('stroke') === '#facc15') {
                        circle.setAttribute('stroke', circle.dataset.defaultStroke || '#e2e8f0');
                        circle.setAttribute('stroke-width', circle.dataset.defaultStrokeWidth || '2');
                    }
                });
            };

            const selectComponent = function (id) {
                const layout = parseLayout();
                if (!layout) {
                    return;
                }
                const component = findComponent(layout, id);
                const position = normalizePosition(component);
                if (!component || !position) {
                    setMessage('Selected component has no editable transform.position.', true);
                    return;
                }

                selectedComponentId = id;
                selectedIdEl.textContent = component.id || '-';
                selectedTypeEl.textContent = component.type || '-';
                selectedTitleEl.textContent = component.title || '-';
                selectedRotationEl.textContent = JSON.stringify(component.transform?.rotation || []);
                selectedActiveEl.textContent = (component.active ?? component.isActive ?? true) === false ? 'inactive' : 'active';
                xInput.value = position[0];
                yInput.value = position[1];
                zInput.value = position[2];
                highlightSelectedMarker();
                setMessage('Component selected. Edit X/Y/Z and apply to layout_json.');
            };

            const readNumber = function (input, label) {
                const value = Number(input.value);
                if (Number.isNaN(value)) {
                    throw new Error(label + ' must be a number.');
                }
                return value;
            };

            document.getElementById('layout-editor-apply')?.addEventListener('click', function () {
                if (!selectedComponentId) {
                    setMessage('Select a component before applying a position.', true);
                    return;
                }

                const layout = parseLayout();
                if (!layout) {
                    return;
                }

                const component = findComponent(layout, selectedComponentId);
                if (!component) {
                    setMessage('Selected component no longer exists in layout_json.', true);
                    return;
                }

                try {
                    const x = readNumber(xInput, 'X');
                    const y = readNumber(yInput, 'Y');
                    const z = readNumber(zInput, 'Z');
                    component.transform = component.transform || {};
                    component.transform.position = [x, y, z];
                    layoutInput.value = JSON.stringify(layout, null, 2);

                    const outsideBounds = x < bounds.xMin || x > bounds.xMax || z < bounds.zMin || z > bounds.zMax;
                    setMessage(outsideBounds
                        ? 'Position applied to layout_json, but component is outside room bounds. Validate before publishing.'
                        : 'Position applied to layout_json. Save and Validate Layout are still required.', outsideBounds);
                } catch (error) {
                    setMessage(error.message, true);
                }
            });

            document.getElementById('layout-editor-reset')?.addEventListener('click', function () {
                if (!selectedComponentId) {
                    setMessage('Select a component before resetting from JSON.', true);
                    return;
                }
                selectComponent(selectedComponentId);
            });

            markers.forEach(function (marker) {
                marker.addEventListener('click', function () {
                    selectComponent(marker.dataset.componentId);
                });
            });
            selectors.forEach(function (button) {
                button.addEventListener('click', function () {
                    selectComponent(button.dataset.componentId);
                });
            });
        });
    </script>
@endif
