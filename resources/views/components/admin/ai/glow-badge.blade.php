@props(['color' => 'primary'])

@php
    $style = match($color) {
        'primary' => 'bg-primary/10 text-primary border-primary/20',
        'emerald' => 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20',
        'blue' => 'bg-blue-500/10 text-blue-400 border-blue-500/20',
        'amber' => 'bg-amber-500/10 text-amber-400 border-amber-500/20',
        'red' => 'bg-red-500/10 text-red-400 border-red-500/20',
        'surface' => 'bg-surface-light text-text-tertiary border-divider',
        default => 'bg-primary/10 text-primary border-primary/20',
    };
@endphp

<span {{ $attributes->merge(['class' => 'px-4 py-1.5 rounded-full text-[9px] font-black uppercase tracking-[0.2em] border ' . $style]) }}>
    {{ $slot }}
</span>
