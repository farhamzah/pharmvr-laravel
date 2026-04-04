@extends('layouts.admin')

@section('header', 'Initialize Knowledge Source')

@section('content')
<div class="max-w-5xl mx-auto px-6 py-10 antialiased font-sans text-zinc-400" 
     x-data="{ 
        sourceType: '{{ old('source_type', 'pdf') }}', 
        fileName: '',
        isSaving: false,
        submitSource() {
            const form = $el.querySelector('form');
            if (form.reportValidity()) {
                this.isSaving = true;
                form.submit();
            }
        },
        saveDraft() {
            const form = $el.querySelector('form');
            const draftInput = document.createElement('input');
            draftInput.type = 'hidden';
            draftInput.name = 'draft';
            draftInput.value = '1';
            form.appendChild(draftInput);
            this.isSaving = true;
            form.submit();
        }
     }">
    
    {{-- Simple Page Header: Focused & Clear --}}
    <div class="mb-12 border-b border-white/5 pb-8">
        <a href="{{ route('admin.ai.sources.index') }}" class="inline-flex items-center gap-2 text-xs font-bold text-zinc-500 hover:text-primary transition-all mb-4 group">
            <svg class="w-4 h-4 group-hover:-translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"></path></svg>
            Back to Matrix
        </a>
        <h1 class="text-3xl font-bold text-white tracking-tight mb-2">Upload Knowledge Source</h1>
        <p class="text-zinc-500">Configure how the AI should ingest and process your documentation.</p>
    </div>

    <form action="{{ route('admin.ai.sources.store') }}" method="POST" enctype="multipart/form-data" class="space-y-8 pb-20">
        @csrf
        
        {{-- Section 1: Basic Identity --}}
        <div class="bg-zinc-900/50 border border-white/5 rounded-2xl p-8 shadow-xl">
            <div class="flex items-center gap-3 mb-8 border-b border-white/5 pb-4">
                <div class="w-8 h-8 rounded-lg bg-primary/10 flex items-center justify-center text-primary font-bold text-sm italic">1</div>
                <h2 class="text-lg font-bold text-white tracking-tight uppercase tracking-wider">Source Identity</h2>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <div class="md:col-span-2 space-y-2">
                    <label class="text-xs font-bold text-zinc-500 uppercase tracking-widest pl-1">Source Title</label>
                    <input type="text" name="title" value="{{ old('title') }}" required placeholder="e.g. CPOB_PROTOCOL_v4_2025" 
                        class="bg-zinc-950 border-white/10 border rounded-xl px-4 py-3 text-sm font-medium text-white focus:border-primary/60 focus:ring-1 focus:ring-primary/20 transition-all w-full placeholder:text-zinc-800">
                    @error('title') <p class="text-red-500 text-[10px] font-medium uppercase tracking-widest pl-2 mt-1">{{ $message }}</p> @enderror
                </div>
                
                <div class="space-y-2">
                    <label class="text-xs font-bold text-zinc-500 uppercase tracking-widest pl-1">Module Integration</label>
                    <select name="module_id" class="bg-zinc-950 border-white/10 border rounded-xl px-4 py-3 text-sm font-medium text-white focus:border-primary/60 focus:ring-1 focus:ring-primary/20 transition-all w-full cursor-pointer appearance-none">
                        <option value="">(Default) General System</option>
                        @foreach($modules as $module)
                            <option value="{{ $module->id }}" {{ old('module_id') == $module->id ? 'selected' : '' }}>{{ $module->title }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="space-y-2">
                    <label class="text-xs font-bold text-zinc-500 uppercase tracking-widest pl-1">Topic / Tags</label>
                    <input type="text" name="topic" value="{{ old('topic') }}" placeholder="QC, Audit, SOP" 
                        class="bg-zinc-950 border-white/10 border rounded-xl px-4 py-3 text-sm font-medium text-white focus:border-primary/60 focus:ring-1 focus:ring-primary/20 transition-all w-full placeholder:text-zinc-800">
                </div>
            </div>

            <div class="space-y-2">
                <label class="text-xs font-bold text-zinc-500 uppercase tracking-widest pl-1">Description / Abstract</label>
                <textarea name="description" rows="3" placeholder="Explain the context of this source..." 
                    class="bg-zinc-950 border-white/10 border rounded-xl px-4 py-3 text-sm font-medium text-white focus:border-primary/60 focus:ring-1 focus:ring-primary/20 transition-all w-full placeholder:text-zinc-800">{{ old('description') }}</textarea>
            </div>
        </div>

        {{-- Section 2: Format & Payload --}}
        <div class="bg-zinc-900/50 border border-white/5 rounded-2xl p-8 shadow-xl">
             <div class="flex items-center gap-3 mb-8 border-b border-white/5 pb-4">
                <div class="w-8 h-8 rounded-lg bg-amber-500/10 flex items-center justify-center text-amber-500 font-bold text-sm italic">2</div>
                <h2 class="text-lg font-bold text-white tracking-tight uppercase tracking-wider">Payload Ingestion</h2>
            </div>

            <div class="space-y-8">
                <div class="space-y-3">
                    <label class="text-xs font-bold text-zinc-500 uppercase tracking-widest pl-1">Select Input Format</label>
                    <div class="grid grid-cols-2 md:grid-cols-6 gap-2">
                        @foreach(['pdf' => 'PDF', 'docx' => 'DOCX', 'txt' => 'Txt', 'md' => 'MD', 'manual' => 'Manual', 'web' => 'URL'] as $val => $label)
                            <label class="relative cursor-pointer group">
                                <input type="radio" name="source_type" value="{{ $val }}" x-model="sourceType" class="sr-only peer">
                                <div class="bg-zinc-950 border border-white/10 peer-checked:border-primary peer-checked:bg-primary/5 py-3 rounded-lg text-center transition-all">
                                     <span class="text-xs font-bold text-zinc-500 peer-checked:text-primary transition-colors">{{ $label }}</span>
                                </div>
                            </label>
                        @endforeach
                    </div>
                </div>

                <div class="bg-zinc-950/40 border border-white/10 border-dashed rounded-xl p-6">
                    {{-- Upload --}}
                    <div x-show="['pdf', 'docx', 'txt', 'md'].includes(sourceType)" x-transition.opacity class="text-center group py-6">
                        <div class="relative border-2 border-white/5 border-dashed rounded-xl p-8 hover:border-primary transition-all bg-black/10">
                            <input type="file" name="file" class="absolute inset-0 opacity-0 cursor-pointer z-10" 
                                @change="fileName = $event.target.files[0].name">
                            <div class="relative z-0 pointer-events-none">
                                <svg class="w-10 h-10 text-primary/40 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path></svg>
                                <p class="text-sm font-bold text-white mb-1" x-text="fileName || 'Click to upload payload'"></p>
                                <p class="text-[10px] text-zinc-600 font-medium uppercase tracking-widest italic">Capacity: UNLIMITED_STORAGE</p>
                            </div>
                        </div>
                        @error('file') <p class="text-red-500 text-[10px] font-medium uppercase tracking-widest pl-2 mt-4">{{ $message }}</p> @enderror
                    </div>

                    {{-- Manual --}}
                    <div x-show="sourceType === 'manual'" x-transition.opacity>
                        <textarea name="content" rows="10" placeholder="Paste or type raw context here..." 
                            class="bg-zinc-950 border border-white/10 rounded-xl px-4 py-4 text-sm font-medium text-white focus:border-primary w-full">{{ old('content') }}</textarea>
                    </div>

                    {{-- URL --}}
                    <div x-show="sourceType === 'web'" x-transition.opacity>
                        <input type="url" name="url" value="{{ old('url') }}" placeholder="https://example.com/source" 
                            class="bg-zinc-950 border border-white/10 rounded-xl px-4 py-3 text-sm font-bold text-white w-full">
                    </div>
                </div>
            </div>
        </div>

        {{-- Section 3: Trust & Lifecycle (Side by Side Grid in one card) --}}
        <div class="bg-zinc-900/50 border border-white/5 rounded-2xl p-8 shadow-xl">
             <div class="flex items-center gap-3 mb-8 border-b border-white/5 pb-4">
                <div class="w-8 h-8 rounded-lg bg-blue-500/10 flex items-center justify-center text-blue-500 font-bold text-sm italic">3</div>
                <h2 class="text-lg font-bold text-white tracking-tight uppercase tracking-wider">System Configuration</h2>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-10">
                <div class="space-y-4">
                    <label class="text-xs font-bold text-zinc-500 uppercase tracking-widest pl-1">Trust Validation Level</label>
                    <div class="space-y-2">
                        @foreach([
                            'verified' => ['label' => 'Verified Root', 'color' => 'emerald'],
                            'internal' => ['label' => 'Internal Trusted', 'color' => 'blue'],
                            'general' => ['label' => 'General Context', 'color' => 'zinc']
                        ] as $val => $info)
                            <label class="flex items-center gap-3 p-3 bg-zinc-950 border border-white/10 rounded-xl cursor-pointer hover:bg-zinc-900 transition-colors">
                                <input type="radio" name="trust_level" value="{{ $val }}" {{ old('trust_level', 'internal') === $val ? 'checked' : '' }} class="w-4 h-4 text-primary focus:ring-0">
                                <span class="text-xs font-bold text-{{ $info['color'] }}-400">{{ $info['label'] }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>

                <div class="space-y-6">
                    <div class="space-y-4">
                         <label class="text-xs font-bold text-zinc-500 uppercase tracking-widest pl-1">Network Lifecycle</label>
                         <div class="p-4 bg-zinc-950 border border-white/10 rounded-xl flex items-center gap-4">
                             <input type="checkbox" name="is_active" id="is_active_check" {{ old('is_active', true) ? 'checked' : '' }} value="1" class="w-5 h-5 rounded bg-zinc-900 border-white/20 text-primary focus:ring-0">
                             <label for="is_active_check" class="flex flex-col gap-0.5 cursor-pointer">
                                 <span class="text-xs font-bold text-white">Auto-Activate after Sync</span>
                                 <span class="text-[10px] text-zinc-500">Enable extraction for AI retrieval immediately.</span>
                             </label>
                         </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4 pt-4 border-t border-white/5">
                        <div class="space-y-1">
                             <span class="text-[10px] font-bold text-zinc-500 uppercase">Pub. Year</span>
                             <input type="number" name="publication_year" value="{{ old('publication_year', date('Y')) }}" class="bg-zinc-950 border-white/10 border rounded-lg h-10 w-full px-3 text-xs text-white">
                        </div>
                        <div class="space-y-1">
                             <span class="text-[10px] font-bold text-zinc-500 uppercase">Language</span>
                             <input type="text" name="language" value="{{ old('language', 'id') }}" class="bg-zinc-950 border-white/10 border rounded-lg h-10 w-full px-3 text-xs text-white">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Final Actions: Large, Clean, Primary Highlighted --}}
        <div class="flex items-center justify-between gap-6 pt-10 border-t border-white/5">
            <a href="{{ route('admin.ai.sources.index') }}" class="text-xs font-bold text-zinc-500 hover:text-red-400 transition-colors flex items-center gap-2 group">
                 <svg class="w-5 h-5 group-hover:rotate-90 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"></path></svg>
                 Cancel Upload
            </a>
            
            <div class="flex items-center gap-4">
                <button type="button" @click="saveDraft()" :disabled="isSaving" class="px-8 py-3 bg-zinc-800 hover:bg-zinc-700 text-zinc-300 font-bold text-xs rounded-xl transition-all disabled:opacity-50">
                    <span x-text="isSaving ? 'Saving...' : 'Save Draft'"></span>
                </button>
                <button type="button" @click="submitSource()" :disabled="isSaving" class="bg-primary hover:bg-primary-hover text-zinc-950 px-12 py-3 rounded-xl font-bold text-xs transition-all shadow-lg active:scale-95 flex items-center gap-2 disabled:opacity-50">
                    <svg x-show="!isSaving" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"></path></svg>
                    <svg x-show="isSaving" class="w-4 h-4 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                    <span x-text="isSaving ? 'Processing...' : 'Upload & Process Node'"></span>
                </button>
            </div>
        </div>
    </form>
</div>
@endsection
