<div x-data="{ 
    content: @js(old('content', $news->content ?? '')),
    title: @js(old('title', $news->title ?? '')),
    status: @js(old('status', (isset($news) && $news->is_active) ? 'published' : (old('status') ?: 'draft'))),
    isDirty: false,
    isSaving: false,
    get wordCount() {
        return this.content.trim() ? this.content.trim().split(/\s+/).length : 0;
    },
    get charCount() {
        return this.content.length;
    },
    publish() {
        const form = this.$el.closest('form');
        if (!form.reportValidity()) return;
        this.status = 'published';
        this.isSaving = true;
        this.isDirty = false;
        this.$nextTick(() => { form.submit(); });
    },
    saveAsDraft() {
        const form = this.$el.closest('form');
        if (!form.reportValidity()) return;
        this.status = 'draft';
        this.isSaving = true;
        this.isDirty = false;
        this.$nextTick(() => { form.submit(); });
    },
    preview() {
        @if(isset($news) && $news->slug)
            if (this.isDirty) {
                if (confirm('Anda memiliki perubahan yang belum disimpan. Pratinjau mungkin tidak menampilkan data terbaru dari draf terakhir. Simpan draf sekarang?')) {
                    this.saveAsDraft();
                    return;
                }
            }
            window.open('{{ route('admin.news.show', $news->slug) }}', '_blank');
        @else
            alert('Wajib simpan sebagai draf (Save Draft) terlebih dahulu sebelum melihat pratinjau untuk artikel baru.');
        @endif
    },
    discard() {
        if (confirm('Apakah Anda yakin ingin membuang semua perubahan yang belum disimpan?')) {
            this.isDirty = false;
            window.location.href = '{{ route('admin.news.index') }}';
        }
    }
}" 
@input="isDirty = true"
class="grid grid-cols-1 lg:grid-cols-12 gap-10 items-start max-w-[1400px] mx-auto">
    @if ($errors->any())
        <div class="lg:col-span-12 mb-2 p-6 bg-red-500/10 border border-red-500/20 rounded-3xl">
            <div class="flex items-center gap-3 text-red-500 mb-2">
                <svg class="w-5 h-5 font-black" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                <span class="text-[11px] font-black uppercase tracking-[0.2em]">Form Validation Errors</span>
            </div>
            <ul class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-x-8 gap-y-1 ml-1">
                @foreach ($errors->all() as $error)
                    <li class="text-[10px] font-bold text-red-400 uppercase tracking-widest list-none flex items-center gap-2">
                        <span class="w-1 h-1 rounded-full bg-red-400"></span>
                        {{ $error }}
                    </li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- Main Content Column: 8/12 -->
    <div class="lg:col-span-8 space-y-8">
        <x-admin.card padding="p-0" class="overflow-visible !bg-transparent !border-none !shadow-none">
            <div class="space-y-6">
                <!-- Article Title -->
                <div class="bg-surface rounded-3xl border border-divider p-10 shadow-premium focus-within:border-primary/40 transition-all">
                    <x-admin.input-group label="Article Title" name="title" required helper="Gunakan judul yang ringkas dan menarik.">
                        <input type="text" id="title" name="title" x-model="title" required 
                            class="w-full px-0 py-2 bg-transparent border-none text-2xl font-bold text-white focus:ring-0 outline-none transition-all placeholder-white/5 tracking-tight font-sans" 
                            placeholder="Tulis judul artikel di sini...">
                    </x-admin.input-group>
                </div>

                <!-- Article Content -->
                <div class="bg-surface rounded-3xl border border-divider p-12 shadow-premium flex flex-col focus-within:border-primary/40 transition-all" style="min-height: 700px;">
                    <div class="flex justify-between items-center mb-10">
                        <div class="space-y-2">
                            <h3 class="text-xs font-black text-primary uppercase tracking-[0.5em] italic font-display">Intelligence Construction</h3>
                            <p class="text-[11px] font-bold text-text-tertiary/40 uppercase tracking-widest font-sans">Full-field editorial immersion active.</p>
                        </div>
                        <div class="flex gap-6 items-center">
                            <div class="px-5 py-3 rounded-2xl bg-divider/10 border border-divider/50 flex items-center gap-4">
                                <span class="text-[10px] font-black text-text-tertiary/60 uppercase tracking-widest font-sans">Metrics:</span>
                                <div class="flex items-center gap-3">
                                    <span class="text-[11px] font-black text-primary" x-text="'Words: ' + wordCount">Words: 0</span>
                                    <div class="w-1 h-3 bg-divider/30 rounded-full"></div>
                                    <span class="text-[11px] font-black text-white" x-text="'Chars: ' + charCount">Chars: 0</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="flex-grow flex flex-col w-full">
                        <textarea id="content" name="content" x-model="content" required 
                            class="flex-grow w-full px-0 py-6 bg-transparent border-none text-[24px] text-white focus:ring-0 outline-none transition-all placeholder-text-tertiary/10 leading-[1.8] font-medium font-sans resize-none" 
                            style="min-height: 450px;"
                            placeholder="Tuliskan gagasan, pembaruan, atau pengumuman Anda di sini..."></textarea>
                    </div>
                    
                    <div class="mt-8 pt-6 border-t border-divider/50 flex justify-between items-center">
                        <div class="flex items-center gap-3">
                            <span class="w-2 h-2 rounded-full" :class="isDirty ? 'bg-yellow-500 animate-pulse' : 'bg-primary'"></span>
                            <span class="text-[10px] font-bold uppercase tracking-[0.2em] font-sans" :class="isDirty ? 'text-yellow-500' : 'text-primary/60'" x-text="isDirty ? 'Changes Detected' : 'Content Synced'"></span>
                        </div>
                        <span class="text-[9px] font-medium text-text-tertiary/20 uppercase tracking-[0.3em] font-sans italic" x-text="isDirty ? 'Unsaved draft...' : 'Last version saved to local buffer'"></span>
                    </div>
                </div>
            </div>
        </x-admin.card>
    </div>

    <!-- Sidebar Column: 4/12 -->
    <div class="lg:col-span-4 space-y-6 lg:sticky lg:top-8">
        <!-- Publishing Controls -->
        <x-admin.card title="Publishing Settings" helper="Manage visibility and schedule." padding="p-6">
            <div class="space-y-6">
                <x-admin.input-group label="Visibility Status" name="status">
                    <div class="relative">
                        <select id="status" name="status" x-model="status" class="w-full bg-surface-light border border-divider rounded-2xl px-6 py-4 text-[11px] font-black uppercase tracking-widest text-white outline-none focus:border-primary transition-all cursor-pointer shadow-inner appearance-none">
                            <option value="draft">Save as Draft</option>
                            <option value="published">Publish Immediately</option>
                        </select>
                        <div class="absolute inset-y-0 right-6 flex items-center pointer-events-none text-text-tertiary/40">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M19 9l-7 7-7-7"></path></svg>
                        </div>
                    </div>
                </x-admin.input-group>

                <x-admin.input-group label="Classification" name="category" helper="Select the appropriate category.">
                    <input type="text" id="category" name="category" value="{{ old('category', $news->category ?? 'General') }}" 
                        class="w-full px-6 py-4 bg-surface-light border border-divider rounded-2xl text-[11px] font-bold uppercase tracking-widest text-white focus:border-primary outline-none transition-all shadow-inner" 
                        placeholder="e.g. SYSTEM UPDATE">
                </x-admin.input-group>

                <x-admin.input-group label="Release Date" name="published_at" helper="Schedule article appearance.">
                    <div class="relative group">
                        <input type="text" id="published_at" name="published_at" value="{{ old('published_at', isset($news) && $news->published_at ? $news->published_at->format('Y-m-d H:i') : now()->format('Y-m-d H:i')) }}" 
                            class="w-full px-6 py-4 bg-surface-light border border-divider rounded-2xl text-[11px] font-bold uppercase tracking-widest text-white outline-none focus:border-primary transition-all cursor-pointer shadow-inner">
                        <div class="absolute inset-y-0 right-0 flex items-center px-6 pointer-events-none text-primary/40 group-hover:text-primary transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2-2v12a2 2 0 002 2z"></path></svg>
                        </div>
                    </div>
                </x-admin.input-group>
            </div>
        </x-admin.card>

        <!-- Image Control -->
        <x-admin.card title="Headline Image" helper="Requirements: 800x450px (16:9)." padding="p-6">
            <x-admin.image-upload name="image" :value="$news->image_url ?? null" :required="!isset($news)" />
        </x-admin.card>

        <!-- Editorial Actions -->
        <div class="bg-surface-light/30 border border-divider rounded-4xl p-6 backdrop-blur-md shadow-premium space-y-3">
            <div class="mb-4">
                <h3 class="text-[10px] font-black text-primary uppercase tracking-[0.4em] italic mb-1 font-display">Action Center</h3>
                <div class="h-0.5 w-8 bg-primary/40 rounded-full"></div>
            </div>

            <x-admin.button type="button" variant="primary" class="w-full py-5 !text-[11px] group !rounded-2xl shadow-cyan-500/20" @click="publish()" x-bind:disabled="isSaving">
                <span x-text="isSaving ? 'Processing...' : ({{ isset($news) ? "'Update Article'" : "'Publish Article'" }})"></span>
                <svg x-show="!isSaving" class="w-5 h-5 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                <svg x-show="isSaving" class="w-5 h-5 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
            </x-admin.button>
            
            <div class="grid grid-cols-2 gap-3">
                <x-admin.button type="button" variant="secondary" class="py-4 !px-4 !text-[10px] opacity-100 hover:bg-surface transition-all !rounded-xl" @click="saveAsDraft()" x-bind:disabled="isSaving">
                    <span x-text="isSaving ? '...' : 'Save Draft'"></span>
                </x-admin.button>
                <x-admin.button type="button" variant="outline" class="py-4 !px-4 !text-[10px] !rounded-xl" @click="preview()">
                    Preview
                </x-admin.button>
            </div>

            <div class="pt-3 mt-3 border-t border-divider/50">
                <x-admin.button type="button" variant="danger" class="w-full py-4 !text-[10px] !bg-transparent border-none hover:!bg-red-500/10 !text-red-500/60 font-medium hover:!text-red-500" @click="discard()">
                    Discard Changes
                </x-admin.button>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        if(typeof flatpickr !== 'undefined') {
            flatpickr("#published_at", {
                enableTime: true,
                dateFormat: "Y-m-d H:i",
                theme: "dark",
                disableMobile: "true"
            });
        }
    });

    window.addEventListener('beforeunload', function (e) {
        const alpineData = document.querySelector('[x-data]').__x.$data;
        if (alpineData && alpineData.isDirty && !alpineData.isSaving) {
            e.preventDefault();
            e.returnValue = '';
        }
    });
</script>


