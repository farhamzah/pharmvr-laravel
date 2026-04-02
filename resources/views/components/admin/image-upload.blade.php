@props(['name', 'label' => 'Headline Image', 'value' => null, 'required' => false])

@php
    $previewUrl = $value ? (Str::startsWith($value, 'http') ? $value : asset($value)) : '';
@endphp

<div x-data="{ 
    preview: '{{ $previewUrl }}',
    hasInitial: {{ $value ? 'true' : 'false' }},
    remove() {
        this.preview = '';
        this.$refs.fileInput.value = '';
    },
    handleFile(e) {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = (e) => this.preview = e.target.result;
            reader.readAsDataURL(file);
        }
    }
}" class="space-y-4">
    <label class="text-[11px] font-black text-text-tertiary uppercase tracking-[0.3em] ml-1 opacity-60 italic flex justify-between">
        <span>{{ $label }}</span>
        @if($required && !$value) <span class="text-primary text-[9px]">Required</span> @endif
    </label>

    <div class="relative w-full aspect-video rounded-3xl bg-surface-light border-2 border-dashed border-divider hover:border-primary/50 transition-all overflow-hidden flex flex-col items-center justify-center group shadow-inner">
        <!-- Preview Image -->
        <template x-if="preview">
            <div class="absolute inset-0 w-full h-full">
                <img :src="preview" 
                    class="w-full h-full object-cover opacity-80 group-hover:opacity-40 transition-opacity duration-500">
                <div class="absolute inset-0 bg-background/20 group-hover:bg-background/40 transition-colors"></div>
            </div>
        </template>

        <!-- Empty State / Overlay -->
        <div class="relative z-10 flex flex-col items-center gap-4 group-hover:scale-105 transition-transform duration-500 pointer-events-none">
            <div class="w-14 h-14 rounded-2xl bg-primary/10 flex items-center justify-center text-primary group-hover:bg-primary/20 transition-colors backdrop-blur-sm border border-primary/20 shadow-lg">
                <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
            </div>
            <div class="flex flex-col items-center gap-1">
                <span class="text-[10px] font-black text-white uppercase tracking-widest drop-shadow-md text-center px-4" x-text="preview ? 'Replace Headline Image' : 'Upload Headline Image'"></span>
                <span x-show="!preview" class="text-[8px] font-bold text-text-tertiary uppercase tracking-widest opacity-60 italic">Drag and drop or search files</span>
            </div>
        </div>

        <input type="file" x-ref="fileInput" name="{{ $name }}" @change="handleFile" accept="image/*" {{ $required && !$value ? 'required' : '' }} 
            class="absolute inset-0 opacity-0 cursor-pointer z-20">
        
        <!-- Remove Button -->
        <div x-show="preview" class="absolute top-4 right-4 z-30">
            <button type="button" @click.stop="remove()" class="p-2 rounded-xl bg-red-500/10 border border-red-500/20 text-red-500 hover:bg-red-500 hover:text-white transition-all shadow-lg backdrop-blur-md">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        </div>
    </div>

    <div class="px-2 space-y-3 pt-2">
        <div class="flex justify-between items-center text-[9px] font-black uppercase tracking-widest leading-none">
            <span class="text-text-tertiary opacity-40 italic">Max file size</span>
            <span class="text-primary italic">2 Megabytes</span>
        </div>
        <div class="flex justify-between items-center text-[9px] font-black uppercase tracking-widest leading-none">
            <span class="text-text-tertiary opacity-40 italic">Requirements</span>
            <span class="text-white italic">16:9 Aspect / 800x450px+</span>
        </div>
    </div>
    
    @error($name) <p class="text-[10px] font-bold text-red-500 mt-2 uppercase tracking-widest text-center">{{ $message }}</p> @enderror
</div>

