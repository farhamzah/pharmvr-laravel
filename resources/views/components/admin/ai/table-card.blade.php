@props(['title', 'helper' => null, 'action' => null])

<div class="bg-surface rounded-4xl border border-divider shadow-premium overflow-hidden">
    <div class="p-8 border-b border-divider flex items-center justify-between bg-surface-light/30">
        <div>
            <h3 class="font-black text-white text-xl tracking-tight uppercase font-display italic">{{ $title }}</h3>
            @if($helper)
                <p class="text-xs text-text-tertiary font-bold uppercase tracking-widest mt-1 opacity-60">{{ $helper }}</p>
            @endif
        </div>
        @if($action)
            <div>
                {{ $action }}
            </div>
        @endif
    </div>
    <div class="overflow-x-auto">
        {{ $slot }}
    </div>
</div>
