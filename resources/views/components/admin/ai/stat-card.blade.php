@props(['title', 'value', 'icon', 'color' => 'primary', 'trend' => null, 'trendUp' => true])

@php
    $colorClasses = match($color) {
        'primary' => 'bg-primary/10 text-primary border-primary/20',
        'emerald' => 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20',
        'blue' => 'bg-blue-500/10 text-blue-400 border-blue-500/20',
        'amber' => 'bg-amber-500/10 text-amber-400 border-amber-500/20',
        'red' => 'bg-red-500/10 text-red-400 border-red-500/20',
        default => 'bg-primary/10 text-primary border-primary/20',
    };
    
    $glowClasses = match($color) {
        'primary' => 'shadow-cyan-glow',
        'emerald' => 'shadow-emerald-glow',
        'blue' => 'shadow-blue-glow',
        'amber' => 'shadow-amber-glow',
        'red' => 'shadow-red-glow',
        default => 'shadow-cyan-glow',
    };
@endphp

<div {{ $attributes->merge(['class' => 'bg-surface p-8 rounded-4xl border border-divider shadow-premium hover:border-'.$color.'/30 transition-all group relative overflow-hidden']) }}>
    <!-- Decorative background glow -->
    <div class="absolute -right-10 -top-10 w-32 h-32 {{ str_replace('/10', '/5', $colorClasses) }} rounded-full blur-3xl opacity-0 group-hover:opacity-100 transition-opacity"></div>
    
    <div class="flex items-center justify-between mb-6 relative z-10">
        <div class="w-14 h-14 {{ $colorClasses }} rounded-2xl flex items-center justify-center border transition-transform group-hover:scale-110">
            {!! $icon !!}
        </div>
        <div class="text-right">
            <span class="text-[10px] font-black uppercase tracking-[0.3em] text-text-tertiary">{{ $title }}</span>
            <p class="text-3xl font-black text-white leading-none mt-2 font-display italic tracking-tight">{{ $value }}</p>
        </div>
    </div>
    
    @if($trend)
    <div class="flex items-center gap-2 text-[10px] font-black {{ $trendUp ? 'text-emerald-400 bg-emerald-500/5 border-emerald-500/10' : 'text-red-400 bg-red-500/5 border-red-500/10' }} border px-3 py-1.5 rounded-full w-fit uppercase tracking-widest relative z-10">
        @if($trendUp)
            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path></svg>
        @else
            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M13 17h8m0 0v-8m0 8l-8-8-4 4-6-6"></path></svg>
        @endif
        {{ $trend }}
    </div>
    @else
    <p class="text-[9px] font-black text-text-tertiary/40 uppercase tracking-[0.4em] italic leading-none relative z-10">Diagnostic Integrity Nominal</p>
    @endif
</div>
