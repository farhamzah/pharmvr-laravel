@props(['variant' => 'primary', 'type' => 'button'])

@php
    $baseClasses = 'px-8 py-4 rounded-2xl text-[11px] font-black uppercase tracking-[0.2em] transition-all duration-300 flex items-center justify-center gap-3 active:scale-95 disabled:opacity-50 disabled:cursor-not-allowed';
    $variants = [
        'primary' => 'bg-primary hover:bg-primary-dark text-background shadow-cyan-glow',
        'secondary' => 'bg-surface-light hover:bg-divider text-white border border-divider',
        'danger' => 'bg-red-500/10 hover:bg-red-500 text-red-400 hover:text-white border border-red-500/20',
        'outline' => 'bg-transparent border border-divider hover:border-primary text-text-tertiary hover:text-primary'
    ];
    $classes = $baseClasses . ' ' . ($variants[$variant] ?? $variants['primary']);
@endphp

@if($attributes->has('href'))
    <a {{ $attributes->merge(['class' => $classes]) }}>
        {{ $slot }}
    </a>
@else
    <button type="{{ $type }}" {{ $attributes->merge(['class' => $classes]) }}>
        {{ $slot }}
    </button>
@endif
