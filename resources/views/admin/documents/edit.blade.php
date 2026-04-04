@extends('layouts.admin')

@section('header', 'Edit Educational Document')

@section('content')
<div class="max-w-4xl relative">
    <form action="{{ route('admin.documents.update', $document) }}" method="POST" enctype="multipart/form-data" class="space-y-12 pb-20">
        @csrf
        @method('PUT')

        <!-- 1. Main Information -->
        <div class="bg-slate-900 border border-slate-800 rounded-[2.5rem] p-12 backdrop-blur-xl relative overflow-hidden group">
            <div class="absolute top-0 right-0 w-64 h-64 bg-indigo-500/5 rounded-full blur-3xl -mr-32 -mt-32"></div>
            
            <div class="relative z-10">
                <div class="flex items-center gap-3 mb-10">
                    <div class="w-1.5 h-1.5 rounded-full bg-indigo-500"></div>
                    <h3 class="text-[10px] font-black text-slate-500 uppercase tracking-[0.3em] italic">1. Main Information</h3>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-10">
                    <div class="space-y-3 md:col-span-2">
                        <label for="title" class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] px-1">Document Title</label>
                        <input type="text" id="title" name="title" value="{{ old('title', $document->title) }}" required 
                            class="w-full px-6 py-4 bg-slate-800/50 border border-slate-700 rounded-2xl text-white font-bold focus:border-indigo-500 focus:bg-slate-800 outline-none transition-all"
                            placeholder="e.g., SOP Penanganan Produk Farmasi">
                    </div>

                    <div class="space-y-3 md:col-span-2">
                        <label for="short_summary" class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] px-1">Lobby Card Summary (Short)</label>
                        <input type="text" id="short_summary" name="short_summary" value="{{ old('short_summary', $document->short_summary) }}"
                            class="w-full px-6 py-4 bg-slate-800/50 border border-slate-700 rounded-2xl text-white font-bold focus:border-indigo-500 focus:bg-slate-800 outline-none transition-all"
                            placeholder="Ringkasan singkat 1 kalimat untuk tampilan di daftar dokumen.">
                    </div>

                    <div class="space-y-3">
                        <label for="training_module_id" class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] px-1">Linked Training Module</label>
                        <select id="training_module_id" name="training_module_id" required class="w-full px-6 py-4 bg-slate-800/50 border border-slate-700 rounded-2xl text-white font-bold focus:border-indigo-500 focus:bg-slate-800 outline-none transition-all appearance-none cursor-pointer">
                            @foreach($modules as $module)
                            <option value="{{ $module->id }}" {{ $document->training_module_id == $module->id ? 'selected' : '' }}>{{ $module->title }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="space-y-3">
                        <label for="category" class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] px-1">Resource Category</label>
                        <input type="text" id="category" name="category" value="{{ old('category', $document->category) }}" required 
                            class="w-full px-6 py-4 bg-slate-800/50 border border-slate-700 rounded-2xl text-white font-bold focus:border-indigo-500 focus:bg-slate-800 outline-none transition-all">
                    </div>

                    <div class="space-y-3">
                        <label for="related_topic" class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] px-1">Educational Topic</label>
                        <input type="text" id="related_topic" name="related_topic" value="{{ old('related_topic', $document->related_topic) }}"
                            class="w-full px-6 py-4 bg-slate-800/50 border border-slate-700 rounded-2xl text-white font-bold focus:border-indigo-500 focus:bg-slate-800 outline-none transition-all">
                    </div>

                    <div class="space-y-3">
                        <label for="level" class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] px-1">Learning Level</label>
                        <select id="level" name="level" class="w-full px-6 py-4 bg-slate-800/50 border border-slate-700 rounded-2xl text-white font-bold focus:border-indigo-500 focus:bg-slate-800 outline-none transition-all appearance-none cursor-pointer">
                            <option value="Beginner" {{ $document->level == 'Beginner' ? 'selected' : '' }}>Beginner</option>
                            <option value="Intermediate" {{ $document->level == 'Intermediate' ? 'selected' : '' }}>Intermediate</option>
                            <option value="Advanced" {{ $document->level == 'Advanced' ? 'selected' : '' }}>Advanced</option>
                        </select>
                    </div>

                    <div class="space-y-3">
                        <label for="pages_count" class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] px-1">Pages Count</label>
                        <input type="number" id="pages_count" name="pages_count" value="{{ old('pages_count', $document->pages_count) }}"
                            class="w-full px-6 py-4 bg-slate-800/50 border border-slate-700 rounded-2xl text-white font-bold focus:border-indigo-500 focus:bg-slate-800 outline-none transition-all">
                    </div>
                </div>
            </div>
        </div>

        <!-- 2. Learning Detail -->
        <div class="bg-slate-900 border border-slate-800 rounded-[2.5rem] p-12 backdrop-blur-xl relative overflow-hidden group">
            <div class="relative z-10">
                <div class="flex items-center gap-3 mb-10">
                    <div class="w-1.5 h-1.5 rounded-full bg-indigo-500 shadow-indigo-glow"></div>
                    <h3 class="text-[10px] font-black text-slate-500 uppercase tracking-[0.3em] italic">2. Learning Detail</h3>
                </div>

                <div class="space-y-8">
                    <div class="space-y-3">
                        <label for="description" class="text-[10px] font-black text-indigo-400 uppercase tracking-[0.2em] px-1">Summary / Ikhtisar Materi</label>
                        <textarea id="description" name="description" rows="4" required
                            class="w-full px-6 py-4 bg-slate-800/50 border border-slate-700 rounded-3xl text-white text-xs font-bold focus:border-indigo-500 outline-none transition-all">{{ old('description', $document->description) }}</textarea>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-10">
                        <div class="space-y-3">
                            <label for="prerequisites" class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] px-1">Prerequisite / Recommendation</label>
                            <textarea id="prerequisites" name="prerequisites" rows="3"
                                class="w-full px-6 py-4 bg-slate-800/50 border border-slate-700 rounded-3xl text-white text-xs font-bold focus:border-indigo-500 outline-none transition-all">{{ old('prerequisites', $document->prerequisites) }}</textarea>
                        </div>

                        <div class="space-y-3">
                            <label for="related_materials" class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] px-1">Related Materials</label>
                            <textarea id="related_materials" name="related_materials" rows="3"
                                class="w-full px-6 py-4 bg-slate-800/50 border border-slate-700 rounded-3xl text-white text-xs font-bold focus:border-indigo-500 outline-none transition-all placeholder:text-slate-600" 
                                placeholder="Dokumen atau materi pendukung lainnya...">{{ old('related_materials', $document->related_materials) }}</textarea>
                        </div>

                        <div class="space-y-3">
                            <label for="ai_context" class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] px-1">AI Analysis Context (Keywords)</label>
                            <textarea id="ai_context" name="ai_context" rows="3"
                                class="w-full px-6 py-4 bg-slate-800/50 border border-slate-700 rounded-3xl text-white text-xs font-bold focus:border-indigo-500 outline-none transition-all placeholder:text-slate-600" 
                                placeholder="Topik atau kata kunci spesifik untuk membantu AI memahami konteks dokumen ini...">{{ old('ai_context', $document->ai_context) }}</textarea>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- 3. Source & Cover -->
        <div class="bg-slate-900 border border-slate-800 rounded-[2.5rem] p-12 backdrop-blur-xl relative overflow-hidden group">
            <div class="relative z-10">
                <div class="flex items-center gap-3 mb-10">
                    <div class="w-1.5 h-1.5 rounded-full bg-amber-500 shadow-amber-glow"></div>
                    <h3 class="text-[10px] font-black text-slate-500 uppercase tracking-[0.3em] italic">3. Document Source</h3>
                </div>

                <div class="space-y-10">
                    <div class="flex flex-col md:flex-row md:items-center gap-8 p-6 bg-slate-800/30 border border-slate-700 rounded-2xl">
                        <div class="flex-1">
                            <span class="text-[10px] font-black text-slate-400 font-bold uppercase tracking-widest italic">Source Management</span>
                            <p class="text-[9px] text-slate-500 italic mt-1 leading-relaxed">Transition between Cloud Links and Local Uploads as required. Cloud Links are preferred for educational stability.</p>
                        </div>
                        <div class="flex bg-slate-900 p-1.5 rounded-xl border border-slate-700 w-fit">
                            <label class="relative cursor-pointer">
                                <input type="radio" name="source_type" value="external" class="sr-only peer" {{ $document->source_type == 'external' ? 'checked' : '' }} onchange="toggleSource('external')">
                                <div class="px-6 py-2 rounded-lg text-[10px] font-black uppercase tracking-widest transition-all peer-checked:bg-white peer-checked:text-slate-900 text-slate-500 hover:text-white">Cloud Link</div>
                            </label>
                            <label class="relative cursor-pointer ml-1">
                                <input type="radio" name="source_type" value="upload" class="sr-only peer" {{ $document->source_type == 'upload' ? 'checked' : '' }} onchange="toggleSource('upload')">
                                <div class="px-6 py-2 rounded-lg text-[10px] font-black uppercase tracking-widest transition-all peer-checked:bg-white peer-checked:text-slate-900 text-slate-500 hover:text-white">Local Upload</div>
                            </label>
                        </div>
                    </div>

                    <div id="external_source_ui" class="space-y-3 {{ $document->source_type == 'upload' ? 'hidden' : '' }}">
                        <label for="external_url" class="text-[10px] font-black text-indigo-400 uppercase tracking-[0.2em] px-1">External Document URL</label>
                        <input type="url" id="external_url" name="external_url" value="{{ old('external_url', $document->source_type == 'external' ? $document->file_url : '') }}"
                            class="w-full px-6 py-4 bg-slate-800/50 border border-slate-700 rounded-2xl text-white font-bold focus:border-indigo-500 outline-none transition-all"
                            placeholder="https://drive.google.com/file/d/...">
                        @if($document->source_type == 'external' && $document->file_url)
                            <p class="text-[9px] text-emerald-500/80 px-1 italic">Linked to: <a href="{{ $document->file_url }}" target="_blank" class="underline hover:text-emerald-400">Current External Asset &nearr;</a></p>
                        @endif
                    </div>

                    <div id="upload_source_ui" class="space-y-3 {{ $document->source_type == 'external' ? 'hidden' : '' }}">
                        <label for="document_file" class="text-[10px] font-black text-indigo-400 uppercase tracking-[0.2em] px-1">Replace PDF File (Optional)</label>
                        <div class="relative">
                            <input type="file" id="document_file" name="document_file"
                                class="w-full px-6 py-4 bg-slate-800/50 border border-slate-700 rounded-2xl text-white font-bold focus:border-indigo-500 outline-none transition-all file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-[10px] file:font-black file:uppercase file:bg-indigo-500/10 file:text-indigo-400 hover:file:bg-indigo-500/20 cursor-pointer">
                        </div>
                        @if($document->source_type == 'upload' && $document->file_url)
                            <p class="text-[9px] text-slate-500 italic px-1 mt-2">Current filename: <span class="text-slate-300 font-bold underline italic">{{ basename($document->file_url) }}</span></p>
                        @endif
                    </div>

                    <div class="pt-8 border-t border-slate-800 flex gap-10">
                        <div class="space-y-3 flex-1 max-w-lg">
                            <label for="thumbnail" class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] px-1">Replace Cover Thumbnail (Optional)</label>
                            <input type="file" id="thumbnail" name="thumbnail"
                                class="w-full px-6 py-4 bg-slate-800/50 border border-slate-700 rounded-2xl text-white font-bold focus:border-indigo-500 outline-none transition-all file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-[10px] file:font-black file:uppercase file:bg-slate-700 file:text-slate-300 hover:file:bg-slate-600 cursor-pointer">
                            <p class="text-[9px] text-slate-600 px-1 italic">Update the portrait learning card visual if needed.</p>
                        </div>

                        @if($document->thumbnail_url)
                        <div class="w-32 h-40 rounded-2xl bg-slate-800 border-2 border-slate-700 overflow-hidden relative shadow-lg group/thumb">
                            <img src="{{ $document->thumbnail_url }}" alt="Thumb" class="w-full h-full object-cover transition-transform group-hover/thumb:scale-110">
                            <div class="absolute inset-0 bg-slate-900/40 backdrop-blur-[2px] flex items-center justify-center opacity-0 group-hover/thumb:opacity-100 transition-opacity">
                                <span class="text-[8px] font-black uppercase text-white tracking-widest">Active Cover</span>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- 4. Publication -->
        <div class="bg-slate-900 border border-slate-800 rounded-[2.5rem] p-12 backdrop-blur-xl relative overflow-hidden group">
            <div class="relative z-10">
                <div class="flex items-center gap-3 mb-10">
                    <div class="w-1.5 h-1.5 rounded-full bg-emerald-500 shadow-emerald-glow"></div>
                    <h3 class="text-[10px] font-black text-slate-500 uppercase tracking-[0.3em] italic">4. Publication Control</h3>
                </div>

                <div class="flex flex-col md:flex-row md:items-center justify-between gap-10">
                    <div class="space-y-1">
                        <h4 class="text-sm font-bold text-white uppercase tracking-tight">Visibility Policy</h4>
                        <p class="text-[10px] text-slate-500 font-medium leading-relaxed italic">Changes will reflect immediately in the mobile app library after saving.</p>
                    </div>

                    <div class="flex items-center gap-12">
                        <div class="flex items-center gap-6 p-4 bg-slate-800/30 border border-slate-700 rounded-2xl">
                            <div class="flex flex-col text-right">
                                <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest italic leading-none">Status</span>
                                <span class="text-[9px] {{ $document->is_active ? 'text-emerald-500' : 'text-slate-500' }} font-black uppercase mt-1">{{ $document->is_active ? 'Live Preview' : 'Staged/Draft' }}</span>
                            </div>
                            <input type="hidden" name="is_active" value="0">
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" name="is_active" value="1" {{ $document->is_active ? 'checked' : '' }} class="sr-only peer">
                                <div class="w-14 h-7 bg-slate-700 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[4px] after:left-[4px] after:bg-slate-400 after:rounded-full after:h-5 after:w-6 after:transition-all peer-checked:bg-emerald-500 after:shadow-md peer-checked:after:bg-white"></div>
                            </label>
                        </div>

                        <!-- Actions Bundle -->
                        <div class="flex items-center gap-4">
                            <button type="button" onclick="window.history.back()" class="px-6 py-4 text-[10px] font-black text-slate-500 uppercase tracking-[0.2em] hover:text-white transition-colors">Cancel</button>
                            <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white px-10 py-5 rounded-2xl text-[10px] font-black uppercase tracking-[0.3em] transition-all shadow-indigo-glow hover:scale-[1.03]">
                                Update Resource
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
    function toggleSource(type) {
        const externalUI = document.getElementById('external_source_ui');
        const uploadUI = document.getElementById('upload_source_ui');
        
        if (type === 'external') {
            externalUI.classList.remove('hidden');
            uploadUI.classList.add('hidden');
        } else {
            externalUI.classList.add('hidden');
            uploadUI.classList.remove('hidden');
        }
    }
</script>
@endsection
