@extends('layouts.admin')

@section('header', 'Modify Knowledge Protocol')

@section('content')
<div class="max-w-6xl mx-auto" x-data="{ sourceType: '{{ old('source_type', $source->source_type->value) }}', fileName: '' }">
    <div class="mb-10 flex items-center justify-between">
        <div class="flex flex-col gap-2">
            <a href="{{ route('admin.ai.sources.show', $source) }}" class="flex items-center gap-2 text-[10px] font-black text-text-tertiary hover:text-primary transition-all uppercase tracking-[0.2em] mb-2 group">
                <svg class="w-4 h-4 group-hover:-translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"></path></svg>
                Back to Node Detail
            </a>
            <h1 class="text-3xl font-black text-white italic tracking-tight uppercase font-display">Modify Neural Node</h1>
            <p class="text-xs text-text-tertiary font-bold uppercase tracking-widest opacity-80">
                Update metadata, trust policy, or physical payload for this knowledge cluster.
            </p>
        </div>
    </div>

    <form action="{{ route('admin.ai.sources.update', $source) }}" method="POST" enctype="multipart/form-data" class="space-y-10 pb-20">
        @csrf
        @method('PUT')
        
        {{-- Section 1: Basic Information --}}
        <x-admin.card title="01 — Basic Information" :helper="'RECONFIGURING: ' . strtoupper($source->title)">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <x-admin.input-group label="Source Title" name="title" required :value="old('title', $source->title)" />
                <x-admin.input-group label="Topic / Tags" name="topic" required :value="old('topic', $source->topic)" />
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mt-8">
                <div class="space-y-3">
                    <label class="text-[10px] font-black text-text-tertiary uppercase tracking-[0.2em] pl-1">Module Integration</label>
                    <select name="module_id" class="w-full bg-background border-divider border rounded-2xl px-6 py-4 text-sm font-black text-white focus:border-primary focus:ring-0 transition-all uppercase tracking-widest cursor-pointer appearance-none">
                        <option value="">CORE SYSTEM (GENERAL INTELLIGENCE)</option>
                        @foreach($modules as $module)
                            <option value="{{ $module->id }}" {{ old('module_id', $source->module_id) == $module->id ? 'selected' : '' }}>{{ strtoupper($module->title) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="space-y-3">
                    <label class="text-[10px] font-black text-text-tertiary uppercase tracking-[0.2em] pl-1">Knowledge Category</label>
                    <select name="category" class="w-full bg-background border-divider border rounded-2xl px-6 py-4 text-sm font-black text-white focus:border-primary focus:ring-0 transition-all uppercase tracking-widest cursor-pointer appearance-none">
                        @foreach([
                            'guideline' => 'REGULATORY_GUIDELINE',
                            'manual' => 'SOP_MANUAL',
                            'research' => 'RESEARCH_INTEL',
                            'textbook' => 'ACADEMIC_REFERENCE',
                            'internal' => 'INTERNAL_POLICY'
                        ] as $val => $label)
                            <option value="{{ $val }}" {{ old('category', $source->category) == $val ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="mt-8">
                <x-admin.input-group label="Description / Abstract" type="textarea" name="description" rows="3" :value="old('description', $source->description)" />
            </div>
        </x-admin.card>

        {{-- Section 2: Reference Information --}}
        <x-admin.card title="02 — Reference Details" helper="Update attribution and publication metadata.">
            <div class="grid grid-cols-1 lg:grid-cols-4 gap-8">
                <div class="lg:col-span-2">
                    <x-admin.input-group label="Author / Organization" name="author" :value="old('author', $source->author)" />
                </div>
                <x-admin.input-group label="Publisher" name="publisher" :value="old('publisher', $source->publisher)" />
                <x-admin.input-group label="Pub. Year" name="publication_year" type="number" :value="old('publication_year', $source->publication_year)" />
            </div>
            <div class="mt-8 grid grid-cols-1 md:grid-cols-2 gap-8">
                <x-admin.input-group label="Language Code" name="language" :value="old('language', $source->language)" />
                <div class="p-6 bg-surface-light/30 border border-divider rounded-2xl flex items-center justify-center">
                    <p class="text-[9px] text-text-tertiary italic font-bold uppercase tracking-widest opacity-60 text-center leading-relaxed">External identifiers ensure accurate citation logic during AI grounding.</p>
                </div>
            </div>
        </x-admin.card>

        {{-- Section 3: Physical Ingestion --}}
        <x-admin.card title="03 — Physical Ingestion" helper="Modify ingestion parameters or replace the physical payload.">
            <div class="space-y-10">
                <div class="space-y-4">
                    <label class="text-[10px] font-black text-white uppercase tracking-[0.2em] pl-1">Primary Source Format</label>
                    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-3">
                        @foreach([
                            'pdf' => 'PDF', 
                            'docx' => 'DOCX', 
                            'txt' => 'TXT', 
                            'md' => 'MARKDOWN',
                            'manual' => 'MANUAL TEXT', 
                            'web' => 'WEB URL'
                        ] as $val => $label)
                            <label class="relative group cursor-pointer">
                                <input type="radio" name="source_type" value="{{ $val }}" x-model="sourceType" class="sr-only peer">
                                <div class="bg-background border-2 border-divider peer-checked:border-primary peer-checked:bg-primary/5 p-4 rounded-xl text-center group-hover:border-primary/50 transition-all h-full flex flex-col items-center justify-center gap-2">
                                     <span class="text-[10px] font-black text-text-tertiary peer-checked:text-primary uppercase tracking-widest">{{ $label }}</span>
                                </div>
                            </label>
                        @endforeach
                    </div>
                </div>

                <div class="bg-background/40 border border-divider border-dashed rounded-3xl p-8">
                    {{-- File Upload Area --}}
                    <div x-show="['pdf', 'docx', 'txt', 'md'].includes(sourceType)" x-transition.opacity class="space-y-6">
                        <div class="relative group border-2 border-divider border-dashed rounded-3xl p-12 text-center hover:border-primary transition-all duration-500 overflow-hidden">
                            <input type="file" name="file" class="absolute inset-0 opacity-0 cursor-pointer z-10" 
                                   @change="fileName = $event.target.files[0].name">
                            <div class="relative z-0">
                                <div class="w-16 h-16 bg-primary/10 text-primary border border-primary/20 rounded-2xl flex items-center justify-center mx-auto mb-6 group-hover:scale-110 transition-transform duration-500">
                                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path></svg>
                                </div>
                                <p class="text-[11px] font-black text-white uppercase tracking-[0.3em] mb-2" x-text="fileName || 'REPLACE NEURAL PAYLOAD'"></p>
                                @if($source->file_path)
                                    <div class="mt-4 px-4 py-2 bg-primary/10 border border-primary/20 rounded-lg inline-flex items-center gap-2">
                                        <div class="w-1.5 h-1.5 rounded-full bg-primary animate-pulse"></div>
                                        <span class="text-[9px] text-primary font-black uppercase tracking-widest truncate max-w-xs">Current: {{ basename($source->file_path) }}</span>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    {{-- Manual Text --}}
                    <div x-show="sourceType === 'manual'" x-transition.opacity class="space-y-4">
                        <x-admin.input-group label="Manual Knowledge Content" type="textarea" name="content" rows="12" :value="old('content', $source->content)" />
                    </div>

                    {{-- Web Reference --}}
                    <div x-show="sourceType === 'web'" x-transition.opacity class="space-y-4">
                        <x-admin.input-group label="System Hub URL" name="url" :value="old('url', $source->url)" />
                    </div>
                </div>

                <div class="space-y-6 pt-6 border-t border-divider/50">
                    <label class="text-[10px] font-black text-white uppercase tracking-[0.2em] pl-1">Trust Integrity Level</label>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        @foreach([
                            'VERIFIED' => ['label' => 'VERIFIED_ROOT', 'color' => 'emerald', 'desc' => 'Official GMP/BPOM regulations.'], 
                            'INTERNAL' => ['label' => 'TRUSTED_INTERNAL', 'color' => 'blue', 'desc' => 'Company-standard SOPs and guides.'], 
                            'GENERAL' => ['label' => 'GENERAL_INTEL', 'color' => 'amber', 'desc' => 'Industry references and context.']
                        ] as $val => $info)
                            <label class="relative group cursor-pointer">
                                <input type="radio" name="trust_level" value="{{ $val }}" class="sr-only peer" {{ old('trust_level', $source->trust_level->value) === $val ? 'checked' : '' }}>
                                <div class="bg-background border-2 border-divider peer-checked:border-{{ $info['color'] }}-500/50 peer-checked:bg-{{ $info['color'] }}-500/5 p-6 rounded-2xl group-hover:border-{{ $info['color'] }}-500/30 transition-all flex flex-col gap-2">
                                    <span class="text-[10px] font-black text-text-tertiary peer-checked:text-{{ $info['color'] }}-400 uppercase tracking-widest">{{ $info['label'] }}</span>
                                    <span class="text-[8px] text-text-tertiary opacity-40 uppercase tracking-widest leading-loose">{{ $info['desc'] }}</span>
                                </div>
                            </label>
                        @endforeach
                    </div>
                </div>
            </div>
        </x-admin.card>

        {{-- Section 4: Network Activation --}}
        <x-admin.card title="04 — Network Activation" helper="Configure node visibility across the neural retrieval network.">
            <div class="space-y-6">
                <div class="flex items-start gap-6 p-8 bg-surface-light/30 rounded-3xl border border-divider group hover:border-primary/30 transition-all border-dashed">
                    <div class="pt-1">
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" name="is_active" id="is_active" {{ old('is_active', $source->is_active) ? 'checked' : '' }} value="1" class="sr-only peer">
                            <div class="w-12 h-6 bg-background border border-divider rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-text-tertiary peer-checked:after:bg-primary after:border-divider after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary/20 peer-checked:border-primary"></div>
                        </label>
                    </div>
                    <label for="is_active" class="flex flex-col gap-1 cursor-pointer">
                        <span class="text-[11px] font-black text-white uppercase tracking-widest">Active Broadcasting State</span>
                        <span class="text-[9px] text-text-tertiary font-bold uppercase tracking-widest opacity-60 leading-relaxed">If disabled, this source remains in the matrix but will not be used to answer student queries via the AI Assistant.</span>
                    </label>
                </div>
            </div>

            <x-slot name="footer">
                <div class="flex items-center justify-end gap-6">
                    <a href="{{ route('admin.ai.sources.show', $source) }}" class="text-[10px] font-black text-text-tertiary hover:text-white uppercase tracking-[0.2em] transition-colors flex items-center gap-2 group">
                         <svg class="w-5 h-5 group-hover:rotate-90 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"></path></svg>
                         Discard Changes
                    </a>
                    <x-admin.ai.action-button type="submit" size="lg" class="px-12 py-4 shadow-cyan-glow">Update Knowledge Protocol</x-admin.ai.action-button>
                </div>
            </x-slot>
        </x-admin.card>
    </form>
</div>
@endsection
