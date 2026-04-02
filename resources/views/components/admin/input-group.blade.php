@props(['label' => null, 'name' => null, 'helper' => null, 'placeholder' => null, 'required' => false])

<div class="space-y-2.5">
    @if($label)
        <label for="{{ $name }}" class="text-[10px] font-bold text-text-tertiary/50 uppercase tracking-[0.2em] ml-0.5 flex justify-between items-center group-focus-within:text-primary/70 transition-colors cursor-pointer font-sans">
            <span>{{ $label }}</span>
            @if($required)
                <span class="text-primary/30 text-[9px] tracking-widest uppercase font-bold">Wajib</span>
            @endif
        </label>
    @endif

    <div class="relative group">
        {{ $slot }}
        
        @if($errors->has($name))
            <div class="absolute inset-y-0 right-0 flex items-center px-4 pointer-events-none text-red-500/60">
                <svg class="w-4 h-4 animate-pulse" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path></svg>
            </div>
        @endif
    </div>

    @if($helper || $errors->has($name))
        <div class="ml-0.5">
            @if($errors->has($name))
                <p class="text-[9px] font-bold text-red-500/80 uppercase tracking-widest font-sans">{{ $errors->first($name) }}</p>
            @elseif($helper)
                <p class="text-[10px] font-medium text-text-tertiary/30 leading-relaxed font-sans">{{ $helper }}</p>
            @endif
        </div>
    @endif
</div>

