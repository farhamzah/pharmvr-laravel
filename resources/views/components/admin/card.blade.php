@props(['title' => null, 'helper' => null, 'footer' => null, 'padding' => 'p-8'])

<div {{ $attributes->merge(['class' => 'bg-surface rounded-4xl border border-divider shadow-premium overflow-hidden']) }}>
    @if($title || $helper)
        <div class="px-8 py-6 border-b border-divider bg-surface-light/20">
            @if($title)
                <h3 class="font-black text-white text-sm tracking-[0.2em] uppercase italic font-display">{{ $title }}</h3>
            @endif
            @if($helper)
                <p class="mt-1 text-[10px] font-bold text-text-tertiary uppercase tracking-widest opacity-60">{{ $helper }}</p>
            @endif
        </div>
    @endif

    <div class="{{ $padding }}">
        {{ $slot }}
    </div>

    @if($footer)
        <div class="px-8 py-6 border-t border-divider bg-surface-light/10">
            {{ $footer }}
        </div>
    @endif
</div>
