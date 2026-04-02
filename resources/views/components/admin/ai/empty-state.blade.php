@props(['message' => 'No active telemetry data found.'])

<div {{ $attributes->merge(['class' => 'px-10 py-20 text-center']) }}>
    <div class="w-20 h-20 bg-surface-light border border-divider rounded-3xl mx-auto mb-6 flex items-center justify-center text-text-tertiary/20">
        <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.75 17L9 21h6l-.75-4M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
        </svg>
    </div>
    <p class="text-text-tertiary font-bold uppercase tracking-[0.4em] opacity-30 italic">{{ $message }}</p>
</div>
