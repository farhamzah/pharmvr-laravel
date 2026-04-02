@props(['color' => 'primary', 'size' => 'md'])

@php
    $colorClasses = match($color) {
        'primary' => 'hover:bg-primary hover:text-background border-primary/20 bg-primary/5 text-primary shadow-cyan-glow/10 hover:shadow-cyan-glow',
        'red' => 'hover:bg-red-500 hover:text-white border-red-500/20 bg-red-500/5 text-red-400 shadow-red-500/10 hover:shadow-red-500',
        'emerald' => 'hover:bg-emerald-500 hover:text-white border-emerald-500/20 bg-emerald-500/5 text-emerald-400 shadow-emerald-500/10 hover:shadow-emerald-500',
        'surface' => 'hover:bg-surface-light hover:text-white border-divider bg-surface-light/50 text-text-tertiary',
        default => 'hover:bg-primary hover:text-background border-primary/20 bg-primary/5 text-primary',
    };
    
    $sizeClasses = match($size) {
        'sm' => 'px-3 py-1.5 text-[8px]',
        'md' => 'px-5 py-2.5 text-[10px]',
        'lg' => 'px-8 py-4 text-xs',
        default => 'px-5 py-2.5 text-[10px]',
    };
@endphp

<button {{ $attributes->merge(['class' => 'font-black uppercase tracking-[0.2em] border rounded-xl transition-all duration-300 ' . $colorClasses . ' ' . $sizeClasses]) }}>
    {{ $slot }}
</button>
